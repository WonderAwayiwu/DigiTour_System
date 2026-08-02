<?php
// tourist-dashboard.php - Registered Tourist Portal (Tabler Style Layout)
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/functions.php';

require_login();

// Redirect admin to admin dashboard if they visit this page
if (is_admin()) {
    header("Location: admin/dashboard.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$msg = isset($_GET['msg']) ? sanitize($_GET['msg']) : '';
$error = '';

// Handle Booking Cancellation
if (isset($_GET['cancel_booking'])) {
    $b_id = (int)$_GET['cancel_booking'];
    $can = $pdo->prepare("UPDATE bookings SET status = 'Cancelled' WHERE id = ? AND user_id = ? AND status = 'Pending'");
    if ($can->execute([$b_id, $user_id])) {
        $msg = "Booking reservation cancelled successfully.";
    }
}

// Handle Profile Update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    $full_name = sanitize($_POST['full_name']);
    $phone = sanitize($_POST['phone']);
    $new_password = $_POST['new_password'];

    if (!empty($full_name)) {
        if (!empty($new_password)) {
            $hashed = password_hash($new_password, PASSWORD_BCRYPT);
            $upd = $pdo->prepare("UPDATE users SET full_name = ?, phone = ?, password = ? WHERE id = ?");
            $upd->execute([$full_name, $phone, $hashed, $user_id]);
        } else {
            $upd = $pdo->prepare("UPDATE users SET full_name = ?, phone = ? WHERE id = ?");
            $upd->execute([$full_name, $phone, $user_id]);
        }
        $_SESSION['full_name'] = $full_name;
        $msg = "Profile updated successfully!";
    } else {
        $error = "Full name cannot be empty.";
    }
}

// Fetch User Info
$user_stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$user_stmt->execute([$user_id]);
$current_user = $user_stmt->fetch();

// Fetch User Bookings (Relational Query)
$b_stmt = $pdo->prepare("SELECT b.*, h.name AS hotel_name, h.price_per_night, d.title AS destination_name, d.region FROM bookings b JOIN hotels h ON b.hotel_id = h.id JOIN destinations d ON h.destination_id = d.id WHERE b.user_id = ? ORDER BY b.id DESC");
$b_stmt->execute([$user_id]);
$user_bookings = $b_stmt->fetchAll();

// Fetch User Reviews
$r_stmt = $pdo->prepare("SELECT r.*, d.title AS destination_name FROM reviews r LEFT JOIN destinations d ON r.destination_id = d.id WHERE r.user_id = ? ORDER BY r.id DESC");
$r_stmt->execute([$user_id]);
$user_reviews = $r_stmt->fetchAll();

// Fetch User Inquiries + admin replies
dt_ensure_inquiry_schema($pdo);
$i_stmt = $pdo->prepare("SELECT * FROM inquiries WHERE user_id = ? ORDER BY id DESC");
$i_stmt->execute([$user_id]);
$user_inquiries = $i_stmt->fetchAll();

$page_title = "Tourist Dashboard | DigiTour Ghana";
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/navbar.php';
?>

<section class="py-5 bg-light min-vh-100">
    <div class="container">
        
        <?php if (!empty($msg)): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fa-solid fa-circle-check me-2"></i> <?= $msg ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <?php if (!empty($error)): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fa-solid fa-triangle-exclamation me-2"></i> <?= $error ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <!-- DASHBOARD HEADER CARD -->
        <div class="card border-0 shadow-sm p-3 p-md-4 rounded-4 mb-4 text-white" style="background: linear-gradient(135deg, #232F3E 0%, #E47911 120%);">
            <div class="row align-items-center g-3">
                <div class="col-md-8 min-w-0">
                    <span class="badge bg-warning text-dark px-3 py-2 rounded-pill fw-bold mb-2">Tourist</span>
                    <h2 class="h4 fw-bold mb-1 text-break">Welcome, <?= sanitize($current_user['full_name']) ?></h2>
                    <p class="text-light mb-0 small text-break"><i class="fa-solid fa-envelope me-1"></i> <?= sanitize($current_user['email']) ?> · <i class="fa-solid fa-phone me-1"></i> <?= sanitize($current_user['phone']) ?></p>
                </div>
                <div class="col-md-4 text-md-end">
                    <a href="destinations.php" class="btn btn-digitour-gold btn-sm"><i class="fa-solid fa-plus me-1"></i> Book a Trip</a>
                </div>
            </div>
        </div>

        <!-- STAT METRIC CARDS -->
        <div class="row g-3 mb-4">
            <div class="col-6 col-md-4">
                <div class="metric-card">
                    <div class="d-flex justify-content-between align-items-center gap-2">
                        <div class="min-w-0">
                            <span class="text-muted small fw-bold text-uppercase">Bookings</span>
                            <h2 class="fw-bold text-dark mb-0"><?= count($user_bookings) ?></h2>
                        </div>
                        <div class="metric-icon bg-success text-white"><i class="fa-solid fa-hotel"></i></div>
                    </div>
                </div>
            </div>

            <div class="col-6 col-md-4">
                <div class="metric-card" style="border-left-color: #E5A93C;">
                    <div class="d-flex justify-content-between align-items-center gap-2">
                        <div class="min-w-0">
                            <span class="text-muted small fw-bold text-uppercase">Pending</span>
                            <h2 class="fw-bold text-dark mb-0">
                                <?= count(array_filter($user_bookings, fn($b) => $b['status'] === 'Pending')) ?>
                            </h2>
                        </div>
                        <div class="metric-icon bg-warning text-dark"><i class="fa-solid fa-clock"></i></div>
                    </div>
                </div>
            </div>

            <div class="col-12 col-md-4">
                <div class="metric-card" style="border-left-color: #17A2B8;">
                    <div class="d-flex justify-content-between align-items-center gap-2">
                        <div class="min-w-0">
                            <span class="text-muted small fw-bold text-uppercase">Reviews</span>
                            <h2 class="fw-bold text-dark mb-0"><?= count($user_reviews) ?></h2>
                        </div>
                        <div class="metric-icon bg-info text-white"><i class="fa-solid fa-comments"></i></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-4">
            <!-- Left Column: My Bookings Table -->
            <div class="col-lg-8 min-w-0">
                <div class="card border-0 shadow-sm rounded-4 mb-4">
                    <div class="card-header bg-white py-3 d-flex flex-wrap justify-content-between align-items-center gap-2 border-bottom">
                        <h5 class="fw-bold text-dark mb-0"><i class="fa-solid fa-calendar-check text-success me-2"></i> My Bookings</h5>
                        <span class="badge bg-light text-dark border"><?= count($user_bookings) ?></span>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="bg-light">
                                <tr>
                                    <th>Hotel</th>
                                    <th>Dates</th>
                                    <th>Total</th>
                                    <th>Status</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($user_bookings)): ?>
                                    <tr>
                                        <td colspan="5" class="text-center py-4 text-muted">
                                            No reservations yet. <a href="destinations.php" class="fw-bold text-success">Browse destinations</a>
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($user_bookings as $b): ?>
                                        <tr>
                                            <td>
                                                <strong class="d-block text-dark"><?= sanitize($b['hotel_name']) ?></strong>
                                                <small class="text-muted"><i class="fa-solid fa-location-dot text-success me-1"></i><?= sanitize($b['destination_name']) ?></small>
                                            </td>
                                            <td>
                                                <small class="d-block"><?= date('M d, Y', strtotime($b['check_in_date'])) ?> – <?= date('M d, Y', strtotime($b['check_out_date'])) ?></small>
                                                <small class="text-muted"><?= (int)$b['guests_count'] ?> guest(s)</small>
                                            </td>
                                            <td>
                                                <span class="fw-bold text-success">$<?= number_format((float)$b['total_price'], 2) ?></span>
                                            </td>
                                            <td>
                                                <?= status_badge($b['status']) ?>
                                            </td>
                                            <td>
                                                <div class="admin-actions">
                                                    <?php if ($b['status'] === 'Pending'): ?>
                                                        <a href="tourist-dashboard.php?cancel_booking=<?= (int)$b['id'] ?>" class="btn btn-sm btn-outline-danger" title="Cancel" onclick="return confirm('Cancel this booking?');">
                                                            <i class="fa-solid fa-ban"></i>
                                                        </a>
                                                    <?php endif; ?>
                                                    <button type="button" class="btn btn-sm btn-outline-success" title="Receipt" onclick='showReceipt(<?= json_encode($b) ?>, <?= json_encode($current_user['full_name']) ?>)'>
                                                        <i class="fa-solid fa-receipt"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- BOOKING RECEIPT MODAL -->
                <div class="modal fade" id="receiptModal" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
                            <div class="modal-header bg-dark text-white py-3">
                                <h5 class="modal-title fw-bold"><i class="fa-solid fa-compass text-warning me-2"></i> DigiTour Booking Receipt</h5>
                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body p-4" id="receiptContent">
                                <!-- Populated dynamically by JS -->
                            </div>
                            <div class="modal-footer bg-light">
                                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Close</button>
                                <button type="button" class="btn btn-success fw-bold" onclick="window.print()"><i class="fa-solid fa-print me-1"></i> Print Receipt</button>
                            </div>
                        </div>
                    </div>
                </div>

                <script>
                function showReceipt(booking, userName) {
                    let modal = new bootstrap.Modal(document.getElementById('receiptModal'));
                    let content = document.getElementById('receiptContent');
                    content.innerHTML = `
                        <div class="text-center mb-4">
                            <h4 class="fw-bold text-dark mb-0">DigiTour Ghana</h4>
                            <small class="text-muted">Official Reservation Voucher</small>
                            <hr class="my-2">
                        </div>
                        <div class="vstack gap-2">
                            <div class="d-flex justify-content-between"><strong>Booking Reference:</strong> <span>#DT-${booking.id}</span></div>
                            <div class="d-flex justify-content-between"><strong>Guest Name:</strong> <span>${userName}</span></div>
                            <div class="d-flex justify-content-between"><strong>Hotel Name:</strong> <span>${booking.hotel_name}</span></div>
                            <div class="d-flex justify-content-between"><strong>Destination Site:</strong> <span>${booking.destination_name}</span></div>
                            <div class="d-flex justify-content-between"><strong>Check-In Date:</strong> <span>${booking.check_in_date}</span></div>
                            <div class="d-flex justify-content-between"><strong>Check-Out Date:</strong> <span>${booking.check_out_date}</span></div>
                            <div class="d-flex justify-content-between"><strong>Guests Count:</strong> <span>${booking.guests_count} Person(s)</span></div>
                            <div class="d-flex justify-content-between fs-5 fw-bold text-success border-top pt-2 mt-2">
                                <span>Total Paid/Due:</span>
                                <span>$${parseFloat(booking.total_price).toFixed(2)}</span>
                            </div>
                            <div class="d-flex justify-content-between align-items-center mt-2">
                                <strong>Reservation Status:</strong>
                                <span class="badge ${booking.status === 'Confirmed' ? 'bg-success' : 'bg-warning text-dark'}">${booking.status}</span>
                            </div>
                        </div>
                        <div class="mt-4 p-3 bg-light rounded text-center small text-muted">
                            <i class="fa-solid fa-phone me-1 text-success"></i> Customer Care: +233 54 932 6089 | WhatsApp Support Available
                        </div>
                    `;
                    modal.show();
                }
                </script>

                <!-- My Reviews Table -->
                <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4">
                    <div class="card-header bg-white py-3 border-bottom">
                        <h5 class="fw-bold text-dark mb-0"><i class="fa-solid fa-star text-warning me-2"></i> My Submitted Reviews</h5>
                    </div>
                    <div class="card-body p-0">
                        <?php if (empty($user_reviews)): ?>
                            <p class="text-center py-4 text-muted mb-0">You have not submitted any reviews yet.</p>
                        <?php else: ?>
                            <div class="list-group list-group-flush">
                                <?php foreach ($user_reviews as $r): ?>
                                    <div class="list-group-item p-3">
                                        <div class="d-flex justify-content-between align-items-center mb-1">
                                            <h6 class="fw-bold mb-0"><?= sanitize($r['destination_name']) ?></h6>
                                            <div><?= status_badge($r['status']) ?></div>
                                        </div>
                                        <div class="mb-2"><?= render_stars($r['rating']) ?></div>
                                        <p class="text-dark small mb-0"><?= sanitize($r['comment']) ?></p>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- My Inquiries + Admin Replies -->
                <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
                    <div class="card-header bg-white py-3 border-bottom d-flex flex-wrap justify-content-between align-items-center gap-2">
                        <h5 class="fw-bold text-dark mb-0"><i class="fa-solid fa-envelope-open-text text-success me-2"></i> My Inquiries</h5>
                        <a href="inquiry.php" class="btn btn-sm btn-digitour-primary"><i class="fa-solid fa-plus me-1"></i> New Inquiry</a>
                    </div>
                    <div class="card-body p-0">
                        <?php if (empty($user_inquiries)): ?>
                            <p class="text-center py-4 text-muted mb-0">You have not sent any inquiries yet. <a href="inquiry.php" class="fw-bold text-success">Ask the tourism board</a></p>
                        <?php else: ?>
                            <div class="list-group list-group-flush">
                                <?php foreach ($user_inquiries as $inq): ?>
                                    <div class="list-group-item p-3">
                                        <div class="d-flex flex-wrap justify-content-between align-items-start gap-2 mb-2">
                                            <div class="min-w-0">
                                                <h6 class="fw-bold mb-1"><?= sanitize($inq['subject']) ?></h6>
                                                <small class="text-muted"><?= date('M d, Y · H:i', strtotime($inq['created_at'])) ?></small>
                                            </div>
                                            <?= status_badge($inq['status']) ?>
                                        </div>
                                        <p class="text-dark small mb-2" style="white-space:pre-wrap;"><?= sanitize($inq['message']) ?></p>
                                        <?php if (!empty($inq['admin_reply'])): ?>
                                            <div class="p-3 rounded-3 border border-success-subtle" style="background:#f0fdf4;">
                                                <div class="d-flex flex-wrap justify-content-between gap-2 mb-1">
                                                    <strong class="text-success small"><i class="fa-solid fa-reply me-1"></i> Tourism Board Reply</strong>
                                                    <?php if (!empty($inq['replied_at'])): ?>
                                                        <small class="text-muted"><?= date('M d, Y · H:i', strtotime($inq['replied_at'])) ?></small>
                                                    <?php endif; ?>
                                                </div>
                                                <p class="mb-0 small text-dark" style="white-space:pre-wrap;"><?= sanitize($inq['admin_reply']) ?></p>
                                            </div>
                                        <?php else: ?>
                                            <small class="text-muted"><i class="fa-solid fa-hourglass-half me-1"></i> Awaiting admin reply…</small>
                                        <?php endif; ?>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Right Column: Profile Update Form -->
            <div class="col-lg-4">
                <div class="card border-0 shadow-sm rounded-4 p-4">
                    <h5 class="fw-bold text-dark mb-3"><i class="fa-solid fa-user-pen text-success me-2"></i> Edit Account Profile</h5>
                    
                    <form action="" method="POST">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Full Name</label>
                            <input type="text" name="full_name" class="form-control" value="<?= sanitize($current_user['full_name']) ?>" required>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label fw-bold">Email Address</label>
                            <input type="email" class="form-control bg-light" value="<?= sanitize($current_user['email']) ?>" readonly>
                            <small class="text-muted">Email cannot be changed.</small>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">Phone Number</label>
                            <input type="text" name="phone" class="form-control" value="<?= sanitize($current_user['phone']) ?>">
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-bold">New Password (Optional)</label>
                            <input type="password" name="new_password" class="form-control" placeholder="Leave blank to keep current">
                        </div>

                        <button type="submit" name="update_profile" class="btn btn-digitour-primary w-100">
                            <i class="fa-solid fa-save me-1"></i> Save Changes
                        </button>
                    </form>
                </div>
            </div>

        </div>

    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
