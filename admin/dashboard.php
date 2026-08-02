<?php
// admin/dashboard.php — Admin Dashboard Overview
$admin_title = "Overview Dashboard";
require_once __DIR__ . '/includes/admin-header.php';

$total_dest = $pdo->query("SELECT COUNT(*) FROM destinations")->fetchColumn();
$total_hotels = $pdo->query("SELECT COUNT(*) FROM hotels")->fetchColumn();
$total_bookings = $pdo->query("SELECT COUNT(*) FROM bookings")->fetchColumn();
$pending_reviews = $pdo->query("SELECT COUNT(*) FROM reviews WHERE status = 'Pending'")->fetchColumn();

$b_stmt = $pdo->query("SELECT b.*, u.full_name AS tourist_name, u.phone AS tourist_phone, h.name AS hotel_name, h.image AS hotel_image, d.title AS destination_name FROM bookings b JOIN users u ON b.user_id = u.id JOIN hotels h ON b.hotel_id = h.id JOIN destinations d ON h.destination_id = d.id ORDER BY b.id DESC LIMIT 5");
$recent_bookings = $b_stmt ? $b_stmt->fetchAll() : [];
?>

<div class="row g-3 mb-4">
    <div class="col-6 col-xl-3 col-md-6">
        <div class="metric-card">
            <div class="d-flex justify-content-between align-items-center gap-2">
                <div class="min-w-0">
                    <span class="text-muted small fw-bold text-uppercase">Destinations</span>
                    <h2 class="fw-bold text-dark mb-0"><?= (int)$total_dest ?></h2>
                </div>
                <div class="metric-icon bg-success text-white"><i class="fa-solid fa-map-location-dot"></i></div>
            </div>
        </div>
    </div>
    <div class="col-6 col-xl-3 col-md-6">
        <div class="metric-card" style="border-left-color:#E5A93C;">
            <div class="d-flex justify-content-between align-items-center gap-2">
                <div class="min-w-0">
                    <span class="text-muted small fw-bold text-uppercase">Hotels</span>
                    <h2 class="fw-bold text-dark mb-0"><?= (int)$total_hotels ?></h2>
                </div>
                <div class="metric-icon bg-warning text-dark"><i class="fa-solid fa-hotel"></i></div>
            </div>
        </div>
    </div>
    <div class="col-6 col-xl-3 col-md-6">
        <div class="metric-card" style="border-left-color:#17A2B8;">
            <div class="d-flex justify-content-between align-items-center gap-2">
                <div class="min-w-0">
                    <span class="text-muted small fw-bold text-uppercase">Bookings</span>
                    <h2 class="fw-bold text-dark mb-0"><?= (int)$total_bookings ?></h2>
                </div>
                <div class="metric-icon bg-info text-white"><i class="fa-solid fa-calendar-check"></i></div>
            </div>
        </div>
    </div>
    <div class="col-6 col-xl-3 col-md-6">
        <div class="metric-card" style="border-left-color:#DC3545;">
            <div class="d-flex justify-content-between align-items-center gap-2">
                <div class="min-w-0">
                    <span class="text-muted small fw-bold text-uppercase">Pending Reviews</span>
                    <h2 class="fw-bold text-dark mb-0"><?= (int)$pending_reviews ?></h2>
                </div>
                <div class="metric-icon bg-danger text-white"><i class="fa-solid fa-comments"></i></div>
            </div>
        </div>
    </div>
</div>

<div class="card border-0 shadow-sm mb-0">
    <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2">
        <h5 class="fw-bold text-dark mb-0"><i class="fa-solid fa-clock-rotate-left text-success me-2"></i>Recent Reservations</h5>
        <div class="admin-toolbar-actions">
            <a href="manage-destinations.php?action=add" class="btn btn-sm btn-digitour-primary"><i class="fa-solid fa-plus me-1"></i> Destination</a>
            <a href="manage-hotels.php?action=add" class="btn btn-sm btn-digitour-gold"><i class="fa-solid fa-plus me-1"></i> Hotel</a>
        </div>
    </div>

    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Hotel</th>
                    <th>Tourist</th>
                    <th>Attraction</th>
                    <th>Dates</th>
                    <th>Total</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($recent_bookings)): ?>
                    <tr>
                        <td colspan="8" class="text-center py-4 text-muted">No hotel reservations found.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($recent_bookings as $rb): ?>
                        <tr>
                            <td><strong>#<?= (int)$rb['id'] ?></strong></td>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <img src="<?= get_hotel_main_image($rb['hotel_name'], $rb['hotel_image']) ?>" class="admin-thumb" alt="">
                                    <strong class="text-dark"><?= sanitize($rb['hotel_name']) ?></strong>
                                </div>
                            </td>
                            <td>
                                <strong class="d-block text-dark"><?= sanitize($rb['tourist_name']) ?></strong>
                                <small class="text-muted"><?= sanitize($rb['tourist_phone']) ?></small>
                            </td>
                            <td><small class="text-success"><i class="fa-solid fa-location-dot me-1"></i><?= sanitize($rb['destination_name']) ?></small></td>
                            <td>
                                <small class="d-block"><?= date('M d, Y', strtotime($rb['check_in_date'])) ?> – <?= date('M d, Y', strtotime($rb['check_out_date'])) ?></small>
                                <small class="text-muted"><?= (int)$rb['guests_count'] ?> guest(s)</small>
                            </td>
                            <td><strong class="text-success">$<?= number_format((float)$rb['total_price'], 2) ?></strong></td>
                            <td><?= status_badge($rb['status']) ?></td>
                            <td>
                                <?php if ($rb['status'] === 'Pending'): ?>
                                    <div class="admin-actions">
                                        <a href="manage-bookings.php?action=confirm&id=<?= (int)$rb['id'] ?>" class="btn btn-sm btn-success"><i class="fa-solid fa-check"></i></a>
                                        <a href="manage-bookings.php?action=cancel&id=<?= (int)$rb['id'] ?>" class="btn btn-sm btn-outline-danger"><i class="fa-solid fa-xmark"></i></a>
                                    </div>
                                <?php else: ?>
                                    <span class="text-muted small">Processed</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <div class="card-footer text-end">
        <a href="manage-bookings.php" class="btn btn-sm btn-outline-dark fw-bold">View all reservations <i class="fa-solid fa-arrow-right ms-1"></i></a>
    </div>
</div>

<?php require_once __DIR__ . '/includes/admin-footer.php'; ?>
