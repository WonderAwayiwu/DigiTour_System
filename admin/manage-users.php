<?php
// admin/manage-users.php — User account management
$admin_title = "Registered Users";
require_once __DIR__ . '/includes/admin-header.php';

$msg = '';
$action = isset($_GET['action']) ? sanitize($_GET['action']) : '';
$u_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($action === 'delete' && $u_id > 0) {
    if ($u_id === (int)$_SESSION['user_id']) {
        $msg = "Error: You cannot delete your own logged-in admin account.";
    } else {
        $del = $pdo->prepare("DELETE FROM users WHERE id = ?");
        if ($del->execute([$u_id])) {
            $msg = "User account deleted successfully.";
        }
    }
}

$all_users = $pdo->query("SELECT u.*, COUNT(b.id) AS booking_count FROM users u LEFT JOIN bookings b ON u.id = b.user_id GROUP BY u.id ORDER BY u.id ASC")->fetchAll();
?>

<?php if (!empty($msg)): ?>
    <div class="alert alert-info alert-dismissible fade show" role="alert">
        <i class="fa-solid fa-info-circle me-2"></i> <?= $msg ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<div class="card border-0 shadow-sm">
    <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2">
        <h5 class="fw-bold text-dark mb-0"><i class="fa-solid fa-users text-success me-2"></i>Accounts (<span id="userCount"><?= count($all_users) ?></span>)</h5>
        <div class="admin-toolbar-actions">
            <div class="input-group input-group-sm admin-search">
                <span class="input-group-text bg-light"><i class="fa-solid fa-magnifying-glass text-secondary"></i></span>
                <input type="text" id="userSearchInput" class="form-control" placeholder="Search name, email, role…" onkeyup="filterUsersTable()">
            </div>
            <a href="export-csv.php?type=users" class="btn btn-sm btn-outline-success"><i class="fa-solid fa-file-csv me-1"></i> CSV</a>
        </div>
    </div>

    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>Role</th>
                    <th>Bookings</th>
                    <th>Joined</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody id="userTableBody">
                <?php foreach ($all_users as $u): ?>
                    <tr>
                        <td><strong>#<?= (int)$u['id'] ?></strong></td>
                        <td><strong class="text-dark"><?= sanitize($u['full_name']) ?></strong></td>
                        <td><span class="text-break"><?= sanitize($u['email']) ?></span></td>
                        <td><?= sanitize($u['phone']) ?></td>
                        <td>
                            <?php if ($u['role'] === 'admin'): ?>
                                <span class="badge bg-danger"><i class="fa-solid fa-shield-halved me-1"></i>Admin</span>
                            <?php else: ?>
                                <span class="badge bg-success-subtle text-success border border-success-subtle">Tourist</span>
                            <?php endif; ?>
                        </td>
                        <td><span class="badge bg-light text-dark border"><?= (int)$u['booking_count'] ?></span></td>
                        <td><small><?= date('M d, Y', strtotime($u['created_at'])) ?></small></td>
                        <td>
                            <?php if ($u['id'] != $_SESSION['user_id']): ?>
                                <a href="manage-users.php?action=delete&id=<?= (int)$u['id'] ?>" class="btn btn-sm btn-outline-danger" title="Delete" onclick="return confirm('Delete this user?');">
                                    <i class="fa-solid fa-user-xmark"></i>
                                </a>
                            <?php else: ?>
                                <span class="text-muted small">You</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
function filterUsersTable() {
    let input = document.getElementById('userSearchInput').value.toLowerCase().trim();
    let rows = document.querySelectorAll('#userTableBody tr');
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
    let countSpan = document.getElementById('userCount');
    if (countSpan) countSpan.innerText = visibleCount;
}
</script>

<?php require_once __DIR__ . '/includes/admin-footer.php'; ?>
