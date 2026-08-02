<?php
// admin/manage-reviews.php — Review moderation
$admin_title = "Moderate Reviews";
require_once __DIR__ . '/includes/admin-header.php';

$msg = '';
$action = isset($_GET['action']) ? sanitize($_GET['action']) : '';
$r_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$filter = isset($_GET['status']) ? sanitize($_GET['status']) : 'All';

if ($r_id > 0) {
    if ($action === 'approve') {
        $upd = $pdo->prepare("UPDATE reviews SET status = 'Approved' WHERE id = ?");
        if ($upd->execute([$r_id])) {
            $msg = "Review #$r_id has been APPROVED and is now live.";
        }
    } elseif ($action === 'delete') {
        $del = $pdo->prepare("DELETE FROM reviews WHERE id = ?");
        if ($del->execute([$r_id])) {
            $msg = "Review #$r_id has been deleted.";
        }
    }
}

$sql = "SELECT r.*, u.full_name AS reviewer_name, u.email AS reviewer_email, d.title AS destination_name FROM reviews r JOIN users u ON r.user_id = u.id LEFT JOIN destinations d ON r.destination_id = d.id";

if ($filter !== 'All' && in_array($filter, ['Pending', 'Approved'])) {
    $sql .= " WHERE r.status = " . $pdo->quote($filter);
}

$sql .= " ORDER BY r.id DESC";
$reviews = $pdo->query($sql)->fetchAll();
?>

<?php if (!empty($msg)): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="fa-solid fa-circle-check me-2"></i> <?= $msg ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<div class="admin-toolbar">
    <h5><i class="fa-solid fa-comments text-success me-2"></i>Filter Feedback</h5>
    <div class="btn-group btn-group-sm flex-wrap">
        <a href="manage-reviews.php?status=All" class="btn btn-<?= ($filter === 'All') ? 'success' : 'outline-secondary' ?>">All</a>
        <a href="manage-reviews.php?status=Pending" class="btn btn-<?= ($filter === 'Pending') ? 'warning text-dark' : 'outline-secondary' ?>">Pending</a>
        <a href="manage-reviews.php?status=Approved" class="btn btn-<?= ($filter === 'Approved') ? 'success' : 'outline-secondary' ?>">Approved</a>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2">
        <h5 class="fw-bold text-dark mb-0"><i class="fa-solid fa-star text-warning me-2"></i>Reviews (<span id="reviewCount"><?= count($reviews) ?></span>)</h5>
        <div class="admin-toolbar-actions">
            <div class="input-group input-group-sm admin-search">
                <span class="input-group-text bg-light"><i class="fa-solid fa-magnifying-glass text-secondary"></i></span>
                <input type="text" id="reviewSearchInput" class="form-control" placeholder="Search reviewer, comment…" onkeyup="filterReviewsTable()">
            </div>
        </div>
    </div>

    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead>
                <tr>
                    <th>Reviewer</th>
                    <th>Destination</th>
                    <th>Rating</th>
                    <th>Comment</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody id="reviewTableBody">
                <?php if (empty($reviews)): ?>
                    <tr>
                        <td colspan="6" class="text-center py-4 text-muted">No reviews found for this filter.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($reviews as $r): ?>
                        <tr>
                            <td>
                                <strong class="d-block text-dark"><?= sanitize($r['reviewer_name']) ?></strong>
                                <small class="text-muted text-break"><?= sanitize($r['reviewer_email']) ?></small>
                            </td>
                            <td>
                                <span class="badge bg-light text-dark border">
                                    <i class="fa-solid fa-location-dot text-success me-1"></i><?= sanitize($r['destination_name']) ?>
                                </span>
                            </td>
                            <td><?= render_stars($r['rating']) ?></td>
                            <td>
                                <p class="text-dark small mb-0 admin-comment"><?= sanitize($r['comment']) ?></p>
                                <small class="text-muted"><?= date('M d, Y H:i', strtotime($r['created_at'])) ?></small>
                            </td>
                            <td><?= status_badge($r['status']) ?></td>
                            <td>
                                <div class="admin-actions">
                                    <?php if ($r['status'] === 'Pending'): ?>
                                        <a href="manage-reviews.php?action=approve&id=<?= (int)$r['id'] ?>&status=<?= urlencode($filter) ?>" class="btn btn-sm btn-success" title="Approve"><i class="fa-solid fa-check"></i></a>
                                    <?php endif; ?>
                                    <a href="manage-reviews.php?action=delete&id=<?= (int)$r['id'] ?>&status=<?= urlencode($filter) ?>" class="btn btn-sm btn-outline-danger" title="Delete" onclick="return confirm('Delete this review?');"><i class="fa-solid fa-trash"></i></a>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
function filterReviewsTable() {
    let input = document.getElementById('reviewSearchInput').value.toLowerCase().trim();
    let rows = document.querySelectorAll('#reviewTableBody tr');
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
    let countSpan = document.getElementById('reviewCount');
    if (countSpan) countSpan.innerText = visibleCount;
}
</script>

<?php require_once __DIR__ . '/includes/admin-footer.php'; ?>
