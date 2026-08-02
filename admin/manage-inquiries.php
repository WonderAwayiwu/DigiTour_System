<?php
// admin/manage-inquiries.php — Tourist Inquiry Management (read / reply / resolve)
$admin_title = "Manage Inquiries";
require_once __DIR__ . '/includes/admin-header.php';

dt_ensure_inquiry_schema($pdo);

$msg = '';
$error = '';
$action = isset($_GET['action']) ? sanitize($_GET['action']) : '';
$inq_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$filter = isset($_GET['status']) ? sanitize($_GET['status']) : 'All';
$view_id = isset($_GET['view']) ? (int)$_GET['view'] : 0;

// Quick status actions via GET
if ($inq_id > 0 && in_array($action, ['resolve', 'reopen', 'delete'], true)) {
    if ($action === 'resolve') {
        $upd = $pdo->prepare("UPDATE inquiries SET status = 'Resolved' WHERE id = ?");
        if ($upd->execute([$inq_id])) {
            $msg = "Inquiry #$inq_id marked as Resolved.";
        }
    } elseif ($action === 'reopen') {
        $upd = $pdo->prepare("UPDATE inquiries SET status = 'New' WHERE id = ?");
        if ($upd->execute([$inq_id])) {
            $msg = "Inquiry #$inq_id reopened as New.";
        }
    } elseif ($action === 'delete') {
        $del = $pdo->prepare("DELETE FROM inquiries WHERE id = ?");
        if ($del->execute([$inq_id])) {
            $msg = "Inquiry #$inq_id deleted.";
            if ($view_id === $inq_id) {
                $view_id = 0;
            }
        }
    }
}

// Save admin reply
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_reply'])) {
    $reply_id = (int)($_POST['inquiry_id'] ?? 0);
    $admin_reply = trim($_POST['admin_reply'] ?? '');
    $mark_resolved = isset($_POST['mark_resolved']);

    if ($reply_id <= 0) {
        $error = "Invalid inquiry selected.";
    } elseif ($admin_reply === '') {
        $error = "Please write a reply before saving.";
        $view_id = $reply_id;
    } else {
        $new_status = $mark_resolved ? 'Resolved' : 'Replied';
        $upd = $pdo->prepare("UPDATE inquiries SET admin_reply = ?, replied_at = NOW(), status = ? WHERE id = ?");
        if ($upd->execute([$admin_reply, $new_status, $reply_id])) {
            $msg = "Reply saved for inquiry #$reply_id. Status set to $new_status.";
            $view_id = $reply_id;
        } else {
            $error = "Failed to save reply. Please try again.";
            $view_id = $reply_id;
        }
    }
}

// Counts for filter badges
$count_all = (int)$pdo->query("SELECT COUNT(*) FROM inquiries")->fetchColumn();
$count_new = (int)$pdo->query("SELECT COUNT(*) FROM inquiries WHERE status = 'New'")->fetchColumn();
$count_replied = (int)$pdo->query("SELECT COUNT(*) FROM inquiries WHERE status = 'Replied'")->fetchColumn();
$count_resolved = (int)$pdo->query("SELECT COUNT(*) FROM inquiries WHERE status = 'Resolved'")->fetchColumn();

// List query
$sql = "SELECT i.*, u.full_name AS account_name, u.phone AS account_phone
        FROM inquiries i
        LEFT JOIN users u ON i.user_id = u.id";
if ($filter !== 'All' && in_array($filter, ['New', 'Replied', 'Resolved'], true)) {
    $sql .= " WHERE i.status = " . $pdo->quote($filter);
}
$sql .= " ORDER BY
            CASE i.status WHEN 'New' THEN 0 WHEN 'Replied' THEN 1 ELSE 2 END,
            i.created_at DESC";
$inquiries = $pdo->query($sql)->fetchAll();

// Detail view
$view_inquiry = null;
if ($view_id > 0) {
    $v_stmt = $pdo->prepare("SELECT i.*, u.full_name AS account_name, u.phone AS account_phone
                             FROM inquiries i
                             LEFT JOIN users u ON i.user_id = u.id
                             WHERE i.id = ?");
    $v_stmt->execute([$view_id]);
    $view_inquiry = $v_stmt->fetch();
}
?>

<?php if (!empty($msg)): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="fa-solid fa-circle-check me-2"></i> <?= sanitize($msg) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<?php if (!empty($error)): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="fa-solid fa-triangle-exclamation me-2"></i> <?= sanitize($error) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<div class="admin-toolbar">
    <h5><i class="fa-solid fa-envelope-open-text text-success me-2"></i>Filter Inquiries</h5>
    <div class="btn-group btn-group-sm flex-wrap">
        <a href="manage-inquiries.php?status=All" class="btn btn-<?= ($filter === 'All') ? 'success' : 'outline-secondary' ?>">All (<?= $count_all ?>)</a>
        <a href="manage-inquiries.php?status=New" class="btn btn-<?= ($filter === 'New') ? 'warning text-dark' : 'outline-secondary' ?>">New (<?= $count_new ?>)</a>
        <a href="manage-inquiries.php?status=Replied" class="btn btn-<?= ($filter === 'Replied') ? 'success' : 'outline-secondary' ?>">Replied (<?= $count_replied ?>)</a>
        <a href="manage-inquiries.php?status=Resolved" class="btn btn-<?= ($filter === 'Resolved') ? 'primary' : 'outline-secondary' ?>">Resolved (<?= $count_resolved ?>)</a>
    </div>
</div>

<div class="row g-3 g-lg-4">
    <!-- Inquiry list -->
    <div class="col-lg-<?= $view_inquiry ? '5' : '12' ?> min-w-0">
        <div class="card border-0 shadow-sm">
            <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2">
                <h5 class="fw-bold text-dark mb-0">
                    <i class="fa-solid fa-inbox text-warning me-2"></i>
                    Inquiries (<span id="inquiryCount"><?= count($inquiries) ?></span>)
                </h5>
                <div class="admin-toolbar-actions">
                    <div class="input-group input-group-sm admin-search">
                        <span class="input-group-text bg-light"><i class="fa-solid fa-magnifying-glass text-secondary"></i></span>
                        <input type="text" id="inquirySearchInput" class="form-control" placeholder="Search name, email, subject…" onkeyup="filterInquiriesTable()">
                    </div>
                    <a href="export-csv.php?type=inquiries" class="btn btn-sm btn-outline-success"><i class="fa-solid fa-file-csv me-1"></i> CSV</a>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>From</th>
                            <th>Subject</th>
                            <th>Status</th>
                            <th>Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="inquiryTableBody">
                        <?php if (empty($inquiries)): ?>
                            <tr>
                                <td colspan="6" class="text-center py-4 text-muted">No inquiries found for this filter.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($inquiries as $inq): ?>
                                <tr class="<?= ($view_id === (int)$inq['id']) ? 'table-warning' : '' ?>">
                                    <td><strong>#<?= (int)$inq['id'] ?></strong></td>
                                    <td>
                                        <strong class="d-block text-dark"><?= sanitize($inq['name']) ?></strong>
                                        <small class="text-muted text-break"><?= sanitize($inq['email']) ?></small>
                                    </td>
                                    <td>
                                        <span class="d-block text-dark"><?= sanitize($inq['subject']) ?></span>
                                        <small class="text-secondary"><?= sanitize(mb_strimwidth($inq['message'], 0, 60, '…')) ?></small>
                                    </td>
                                    <td><?= status_badge($inq['status']) ?></td>
                                    <td><small><?= date('M d, Y', strtotime($inq['created_at'])) ?></small></td>
                                    <td>
                                        <div class="admin-actions">
                                            <a href="manage-inquiries.php?status=<?= urlencode($filter) ?>&view=<?= (int)$inq['id'] ?>" class="btn btn-sm btn-outline-primary" title="Read & Reply"><i class="fa-solid fa-eye"></i></a>
                                            <?php if ($inq['status'] !== 'Resolved'): ?>
                                                <a href="manage-inquiries.php?action=resolve&id=<?= (int)$inq['id'] ?>&status=<?= urlencode($filter) ?>&view=<?= (int)$inq['id'] ?>" class="btn btn-sm btn-outline-success" title="Mark Resolved" onclick="return confirm('Mark this inquiry as resolved?');"><i class="fa-solid fa-check"></i></a>
                                            <?php else: ?>
                                                <a href="manage-inquiries.php?action=reopen&id=<?= (int)$inq['id'] ?>&status=<?= urlencode($filter) ?>&view=<?= (int)$inq['id'] ?>" class="btn btn-sm btn-outline-warning" title="Reopen"><i class="fa-solid fa-rotate-left"></i></a>
                                            <?php endif; ?>
                                            <a href="manage-inquiries.php?action=delete&id=<?= (int)$inq['id'] ?>&status=<?= urlencode($filter) ?>" class="btn btn-sm btn-outline-danger" title="Delete" onclick="return confirm('Delete this inquiry permanently?');"><i class="fa-solid fa-trash"></i></a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <?php if ($view_inquiry): ?>
    <!-- Inquiry detail + reply panel -->
    <div class="col-lg-7 min-w-0">
        <div class="card border-0 shadow-sm">
            <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2">
                <h5 class="fw-bold text-dark mb-0">
                    <i class="fa-solid fa-envelope-open text-success me-2"></i>
                    Inquiry #<?= (int)$view_inquiry['id'] ?>
                </h5>
                <div class="d-flex flex-wrap gap-2 align-items-center">
                    <?= status_badge($view_inquiry['status']) ?>
                    <a href="manage-inquiries.php?status=<?= urlencode($filter) ?>" class="btn btn-sm btn-outline-secondary"><i class="fa-solid fa-xmark me-1"></i> Close</a>
                </div>
            </div>

            <div class="card-body">
                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <div class="p-3 rounded-3 border bg-light h-100">
                            <small class="text-muted text-uppercase fw-bold d-block mb-1">Sender</small>
                            <strong class="d-block text-dark"><?= sanitize($view_inquiry['name']) ?></strong>
                            <a class="text-break small" href="mailto:<?= sanitize($view_inquiry['email']) ?>"><?= sanitize($view_inquiry['email']) ?></a>
                            <?php if (!empty($view_inquiry['account_name'])): ?>
                                <div class="mt-2 small text-success">
                                    <i class="fa-solid fa-user-check me-1"></i>
                                    Registered: <?= sanitize($view_inquiry['account_name']) ?>
                                    <?php if (!empty($view_inquiry['account_phone'])): ?>
                                        · <?= sanitize($view_inquiry['account_phone']) ?>
                                    <?php endif; ?>
                                </div>
                            <?php else: ?>
                                <div class="mt-2 small text-muted"><i class="fa-solid fa-user-slash me-1"></i> Guest (not logged in)</div>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="p-3 rounded-3 border bg-light h-100">
                            <small class="text-muted text-uppercase fw-bold d-block mb-1">Meta</small>
                            <div class="small mb-1"><strong>Subject:</strong> <?= sanitize($view_inquiry['subject']) ?></div>
                            <div class="small mb-1"><strong>Received:</strong> <?= date('M d, Y · H:i', strtotime($view_inquiry['created_at'])) ?></div>
                            <?php if (!empty($view_inquiry['replied_at'])): ?>
                                <div class="small"><strong>Last reply:</strong> <?= date('M d, Y · H:i', strtotime($view_inquiry['replied_at'])) ?></div>
                            <?php endif; ?>
                            <div class="mt-2 d-flex flex-wrap gap-2">
                                <a class="btn btn-sm btn-outline-dark" href="mailto:<?= sanitize($view_inquiry['email']) ?>?subject=<?= rawurlencode('Re: ' . $view_inquiry['subject'] . ' — DigiTour Ghana') ?>&body=<?= rawurlencode("Hello " . $view_inquiry['name'] . ",\n\n") ?>">
                                    <i class="fa-solid fa-envelope me-1"></i> Email
                                </a>
                                <a class="btn btn-sm btn-outline-success" target="_blank" rel="noopener"
                                   href="<?= DT_ADMIN_WHATSAPP ?>?text=<?= rawurlencode('Hello ' . $view_inquiry['name'] . ', regarding your DigiTour inquiry: ' . $view_inquiry['subject']) ?>">
                                    <i class="fab fa-whatsapp me-1"></i> WhatsApp
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mb-4">
                    <label class="form-label fw-bold">Tourist Message</label>
                    <div class="p-3 rounded-3 border" style="background:#fffaf3; white-space:pre-wrap;"><?= sanitize($view_inquiry['message']) ?></div>
                </div>

                <?php if (!empty($view_inquiry['admin_reply'])): ?>
                    <div class="mb-4">
                        <label class="form-label fw-bold text-success"><i class="fa-solid fa-reply me-1"></i> Current Admin Reply</label>
                        <div class="p-3 rounded-3 border border-success-subtle" style="background:#f0fdf4; white-space:pre-wrap;"><?= sanitize($view_inquiry['admin_reply']) ?></div>
                    </div>
                <?php endif; ?>

                <form method="POST" action="manage-inquiries.php?status=<?= urlencode($filter) ?>&view=<?= (int)$view_inquiry['id'] ?>">
                    <input type="hidden" name="inquiry_id" value="<?= (int)$view_inquiry['id'] ?>">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Write / Update Reply <span class="text-danger">*</span></label>
                        <textarea name="admin_reply" class="form-control" rows="5" placeholder="Type your official reply to the tourist…" required><?= sanitize($view_inquiry['admin_reply'] ?? '') ?></textarea>
                        <small class="text-muted">Saved replies appear on the tourist dashboard when the sender has an account.</small>
                    </div>
                    <div class="form-check mb-3">
                        <input class="form-check-input" type="checkbox" name="mark_resolved" id="mark_resolved" value="1" <?= ($view_inquiry['status'] === 'Resolved') ? 'checked' : '' ?>>
                        <label class="form-check-label" for="mark_resolved">Also mark as Resolved after saving</label>
                    </div>
                    <div class="d-flex flex-wrap gap-2">
                        <button type="submit" name="save_reply" class="btn btn-digitour-primary">
                            <i class="fa-solid fa-paper-plane me-1"></i> Save Reply
                        </button>
                        <?php if ($view_inquiry['status'] !== 'Resolved'): ?>
                            <a href="manage-inquiries.php?action=resolve&id=<?= (int)$view_inquiry['id'] ?>&status=<?= urlencode($filter) ?>&view=<?= (int)$view_inquiry['id'] ?>" class="btn btn-outline-success" onclick="return confirm('Mark as resolved without changing the reply text?');">
                                <i class="fa-solid fa-check me-1"></i> Mark Resolved
                            </a>
                        <?php else: ?>
                            <a href="manage-inquiries.php?action=reopen&id=<?= (int)$view_inquiry['id'] ?>&status=<?= urlencode($filter) ?>&view=<?= (int)$view_inquiry['id'] ?>" class="btn btn-outline-warning">
                                <i class="fa-solid fa-rotate-left me-1"></i> Reopen
                            </a>
                        <?php endif; ?>
                        <a href="manage-inquiries.php?action=delete&id=<?= (int)$view_inquiry['id'] ?>&status=<?= urlencode($filter) ?>" class="btn btn-outline-danger ms-auto" onclick="return confirm('Delete this inquiry permanently?');">
                            <i class="fa-solid fa-trash me-1"></i> Delete
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>

<script>
function filterInquiriesTable() {
    let input = document.getElementById('inquirySearchInput').value.toLowerCase().trim();
    let rows = document.querySelectorAll('#inquiryTableBody tr');
    let visibleCount = 0;
    rows.forEach(row => {
        let text = row.innerText.toLowerCase();
        if (text.indexOf(input) > -1) {
            row.style.display = '';
            visibleCount++;
        } else {
            row.style.display = 'none';
        }
    });
    let countSpan = document.getElementById('inquiryCount');
    if (countSpan) countSpan.innerText = visibleCount;
}
</script>

<?php require_once __DIR__ . '/includes/admin-footer.php'; ?>
