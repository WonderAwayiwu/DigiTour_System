<?php
// admin/manage-bookings.php — Reservation management
$admin_title = "Customer Bookings";
require_once __DIR__ . '/includes/admin-header.php';

$msg = '';
$action = isset($_GET['action']) ? sanitize($_GET['action']) : '';
$b_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$filter_status = isset($_GET['status']) ? sanitize($_GET['status']) : 'All';

if ($b_id > 0) {
    if ($action === 'confirm') {
        $upd = $pdo->prepare("UPDATE bookings SET status = 'Confirmed' WHERE id = ?");
        if ($upd->execute([$b_id])) {
            $msg = "Booking #$b_id has been CONFIRMED successfully!";
        }
    } elseif ($action === 'cancel') {
        $upd = $pdo->prepare("UPDATE bookings SET status = 'Cancelled' WHERE id = ?");
        if ($upd->execute([$b_id])) {
            $msg = "Booking #$b_id has been CANCELLED.";
        }
    }
}

$sql = "SELECT b.*, u.full_name AS tourist_name, u.email AS tourist_email, u.phone AS tourist_phone, h.name AS hotel_name, h.image AS hotel_image, h.price_per_night, d.title AS destination_name, d.region FROM bookings b JOIN users u ON b.user_id = u.id JOIN hotels h ON b.hotel_id = h.id JOIN destinations d ON h.destination_id = d.id";

if ($filter_status !== 'All' && in_array($filter_status, ['Pending', 'Confirmed', 'Cancelled'])) {
    $sql .= " WHERE b.status = " . $pdo->quote($filter_status);
}

$sql .= " ORDER BY b.id DESC";
$bookings = $pdo->query($sql)->fetchAll();
?>

<?php if (!empty($msg)): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="fa-solid fa-circle-check me-2"></i> <?= $msg ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<div class="admin-toolbar">
    <h5><i class="fa-solid fa-filter text-success me-2"></i>Filter Reservations</h5>
    <div class="btn-group btn-group-sm flex-wrap">
        <a href="manage-bookings.php?status=All" class="btn btn-<?= ($filter_status === 'All') ? 'success' : 'outline-secondary' ?>">All</a>
        <a href="manage-bookings.php?status=Pending" class="btn btn-<?= ($filter_status === 'Pending') ? 'warning text-dark' : 'outline-secondary' ?>">Pending</a>
        <a href="manage-bookings.php?status=Confirmed" class="btn btn-<?= ($filter_status === 'Confirmed') ? 'success' : 'outline-secondary' ?>">Confirmed</a>
        <a href="manage-bookings.php?status=Cancelled" class="btn btn-<?= ($filter_status === 'Cancelled') ? 'danger' : 'outline-secondary' ?>">Cancelled</a>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2">
        <h5 class="fw-bold text-dark mb-0"><i class="fa-solid fa-calendar-check text-success me-2"></i>Reservations (<span id="bookingCount"><?= count($bookings) ?></span>)</h5>
        <div class="admin-toolbar-actions">
            <div class="input-group input-group-sm admin-search">
                <span class="input-group-text bg-light"><i class="fa-solid fa-magnifying-glass text-secondary"></i></span>
                <input type="text" id="bookingSearchInput" class="form-control" placeholder="Search tourist, hotel…" onkeyup="filterBookingsTable()">
            </div>
            <a href="export-csv.php?type=bookings" class="btn btn-sm btn-outline-success"><i class="fa-solid fa-file-csv me-1"></i> CSV</a>
        </div>
    </div>

    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Hotel</th>
                    <th>Tourist</th>
                    <th>Destination</th>
                    <th>Dates</th>
                    <th>Total</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody id="bookingTableBody">
                <?php if (empty($bookings)): ?>
                    <tr>
                        <td colspan="8" class="text-center py-4 text-muted">No reservations found for this filter.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($bookings as $b): ?>
                        <tr>
                            <td><strong>#<?= (int)$b['id'] ?></strong></td>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <img src="<?= get_hotel_main_image($b['hotel_name'], $b['hotel_image']) ?>" class="admin-thumb" alt="">
                                    <strong class="text-dark"><?= sanitize($b['hotel_name']) ?></strong>
                                </div>
                            </td>
                            <td>
                                <strong class="d-block text-dark"><?= sanitize($b['tourist_name']) ?></strong>
                                <small class="text-muted d-block text-break"><?= sanitize($b['tourist_email']) ?></small>
                                <small class="text-muted"><?= sanitize($b['tourist_phone']) ?></small>
                            </td>
                            <td>
                                <small class="text-success"><i class="fa-solid fa-location-dot me-1"></i><?= sanitize($b['destination_name']) ?></small>
                            </td>
                            <td>
                                <small class="d-block fw-semibold"><?= date('M d, Y', strtotime($b['check_in_date'])) ?> – <?= date('M d, Y', strtotime($b['check_out_date'])) ?></small>
                                <small class="text-muted"><?= calculate_nights($b['check_in_date'], $b['check_out_date']) ?> night(s) · <?= (int)$b['guests_count'] ?> guest(s)</small>
                            </td>
                            <td><strong class="text-success">$<?= number_format((float)$b['total_price'], 2) ?></strong></td>
                            <td><?= status_badge($b['status']) ?></td>
                            <td>
                                <div class="admin-actions">
                                    <?php if ($b['status'] !== 'Confirmed'): ?>
                                        <a href="manage-bookings.php?action=confirm&id=<?= (int)$b['id'] ?>&status=<?= urlencode($filter_status) ?>" class="btn btn-sm btn-success" title="Confirm" onclick="return confirm('Confirm this booking?');"><i class="fa-solid fa-check"></i></a>
                                    <?php endif; ?>
                                    <?php if ($b['status'] !== 'Cancelled'): ?>
                                        <a href="manage-bookings.php?action=cancel&id=<?= (int)$b['id'] ?>&status=<?= urlencode($filter_status) ?>" class="btn btn-sm btn-outline-danger" title="Cancel" onclick="return confirm('Cancel this booking?');"><i class="fa-solid fa-ban"></i></a>
                                    <?php endif; ?>
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
function filterBookingsTable() {
    let input = document.getElementById('bookingSearchInput').value.toLowerCase().trim();
    let rows = document.querySelectorAll('#bookingTableBody tr');
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
    let countSpan = document.getElementById('bookingCount');
    if (countSpan) countSpan.innerText = visibleCount;
}
</script>

<?php require_once __DIR__ . '/includes/admin-footer.php'; ?>
