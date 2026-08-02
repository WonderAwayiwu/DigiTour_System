<?php
// book-hotel.php - Hotelier Template Powered Room Reservation Engine
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/functions.php';

$hotel_id = isset($_GET['hotel_id']) ? (int)$_GET['hotel_id'] : 0;

if ($hotel_id <= 0) {
    header("Location: destinations.php");
    exit;
}

// Require Login for Booking
if (!is_logged_in()) {
    $redirect_url = "book-hotel.php?hotel_id=" . $hotel_id;
    header("Location: login.php?msg=" . urlencode("Please log in to reserve a room.") . "&redirect=" . urlencode($redirect_url));
    exit;
}

// Fetch Hotel & Mapped Destination
$stmt = $pdo->prepare("SELECT h.*, d.title AS destination_name, d.region FROM hotels h JOIN destinations d ON h.destination_id = d.id WHERE h.id = ?");
$stmt->execute([$hotel_id]);
$hotel = $stmt->fetch();

if (!$hotel) {
    header("Location: destinations.php");
    exit;
}

$error = '';
$today = date('Y-m-d');
$default_check_in = date('Y-m-d', strtotime('+1 day'));
$default_check_out = date('Y-m-d', strtotime('+3 days'));

// Handle Booking Form Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirm_booking'])) {
    $check_in = sanitize($_POST['check_in_date']);
    $check_out = sanitize($_POST['check_out_date']);
    $guests_count = (int)$_POST['guests_count'];
    $user_id = $_SESSION['user_id'];

    if (empty($check_in) || empty($check_out) || $guests_count <= 0) {
        $error = "Please select valid check-in and check-out dates.";
    } elseif ($check_in < $today) {
        $error = "Check-in date cannot be in the past.";
    } elseif ($check_out <= $check_in) {
        $error = "Check-out date must be after check-in date.";
    } else {
        $nights = calculate_nights($check_in, $check_out);
        $total_price = $nights * $hotel['price_per_night'];

        $ins = $pdo->prepare("INSERT INTO bookings (user_id, hotel_id, check_in_date, check_out_date, guests_count, total_price, status) VALUES (?, ?, ?, ?, ?, ?, 'Pending')");
        if ($ins->execute([$user_id, $hotel_id, $check_in, $check_out, $guests_count, $total_price])) {
            header("Location: tourist-dashboard.php?msg=" . urlencode("Reservation submitted successfully! Your booking status is currently Pending Admin confirmation."));
            exit;
        } else {
            $error = "Failed to submit reservation. Please try again.";
        }
    }
}

$page_title = "Book " . $hotel['name'] . " | Hotelier Booking Portal";
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/navbar.php';
?>

<!-- BOOKING PAGE HEADER -->
<div class="container-fluid page-header mb-4 mb-md-5 p-0" style="background: linear-gradient(rgba(15, 23, 43, .72), rgba(15, 23, 43, .72)), url('<?= get_image_url($hotel['image'], 'https://images.unsplash.com/photo-1566073771259-6a8506099945?auto=format&fit=crop&w=1600&q=80') ?>') center/cover;">
    <div class="container-fluid page-header-inner py-4 py-md-5">
        <div class="container text-center pb-3 pb-md-4 pt-3">
            <h1 class="dt-page-hero-title text-white mb-2"><?= sanitize($hotel['name']) ?></h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb justify-content-center text-uppercase mb-0 small">
                    <li class="breadcrumb-item"><a href="index.php" class="text-warning">Home</a></li>
                    <li class="breadcrumb-item"><a href="destinations.php" class="text-warning">Destinations</a></li>
                    <li class="breadcrumb-item text-white active" aria-current="page">Book Room</li>
                </ol>
            </nav>
        </div>
    </div>
</div>

<?php 
$bk_h_images = get_hotel_images($hotel['name'], $hotel['image'], $hotel['id']);
?>
<!-- HOTELIER ROOM BOOKING CONTAINER -->
<div class="container-xxl py-5">
    <div class="container">
        <div class="text-center mb-4 mb-md-5">
            <h6 class="section-eyebrow justify-content-center mb-2"><i class="fa-solid fa-bed"></i> Room Booking</h6>
            <h1 class="h3 mb-2">Reserve your stay</h1>
            <p class="text-muted mb-0">Near <strong><?= sanitize($hotel['destination_name']) ?></strong> · <?= sanitize($hotel['region']) ?></p>
        </div>

        <div class="row g-5">
            <!-- Left Column: Hotelier Image Carousel & Details -->
            <div class="col-lg-6">
                <div class="card border-0 shadow-lg rounded-4 overflow-hidden mb-4">
                    <?php if (count($bk_h_images) > 1): ?>
                        <div id="bookingHotelCarousel" class="carousel slide" data-bs-ride="carousel">
                            <div class="carousel-inner" style="height: 320px;">
                                <?php foreach ($bk_h_images as $b_idx => $b_img): ?>
                                    <div class="carousel-item h-100 <?= ($b_idx === 0) ? 'active' : '' ?>">
                                        <img class="img-fluid w-100 h-100 object-fit-cover" src="<?= $b_img ?>" alt="<?= sanitize($hotel['name']) ?> Photo <?= $b_idx + 1 ?>">
                                    </div>
                                <?php endforeach; ?>
                            </div>
                            <button class="carousel-control-prev" type="button" data-bs-target="#bookingHotelCarousel" data-bs-slide="prev">
                                <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                            </button>
                            <button class="carousel-control-next" type="button" data-bs-target="#bookingHotelCarousel" data-bs-slide="next">
                                <span class="carousel-control-next-icon" aria-hidden="true"></span>
                            </button>
                        </div>
                    <?php else: ?>
                        <img class="img-fluid w-100" style="height: 320px; object-fit: cover;" src="<?= $bk_h_images[0] ?>" alt="<?= sanitize($hotel['name']) ?>">
                    <?php endif; ?>

                    <div class="card-body p-4 bg-white">
                        <?php $curr = $_SESSION['currency'] ?? 'USD'; ?>
                        <div class="d-flex flex-wrap justify-content-between align-items-start gap-2 mb-3">
                            <h3 class="h5 fw-bold text-dark mb-0"><?= sanitize($hotel['name']) ?></h3>
                            <span class="badge px-3 py-2 text-dark shadow-sm" style="background: linear-gradient(135deg, #FFD814, #FF9900); font-weight: 700; font-size: 0.95rem;"><?= dt_format_currency($hotel['price_per_night'], $curr) ?> <small class="fw-normal">/ night</small></span>
                        </div>
                        <p class="text-muted mb-4"><?= sanitize($hotel['description']) ?></p>
                        
                        <!-- Hotelier Room Amenities -->
                        <div class="row g-3 border-top border-bottom py-3 mb-3">
                            <div class="col-4 text-center border-end">
                                <i class="fa fa-bed fa-2x mb-1 d-block" style="color: var(--amazon-orange-deep);"></i>
                                <span class="small text-dark fw-bold"><?= $hotel['room_capacity'] ?> Guests</span>
                            </div>
                            <div class="col-4 text-center border-end">
                                <i class="fa fa-bath fa-2x mb-1 d-block" style="color: var(--amazon-orange-deep);"></i>
                                <span class="small text-dark fw-bold">Private Bath</span>
                            </div>
                            <div class="col-4 text-center">
                                <i class="fa fa-wifi fa-2x mb-1 d-block" style="color: var(--amazon-orange-deep);"></i>
                                <span class="small text-dark fw-bold">Free Wi-Fi</span>
                            </div>
                        </div>

                        <div class="d-flex flex-wrap gap-2 justify-content-between align-items-center small text-muted">
                            <span class="text-break"><i class="fa fa-phone me-1" style="color: var(--amazon-orange-deep);"></i> <?= sanitize($hotel['contact_phone']) ?></span>
                            <span class="text-break"><i class="fa fa-envelope me-1" style="color: var(--amazon-orange-deep);"></i> <?= sanitize($hotel['contact_email'] ?? 'info@digitour.gh') ?></span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Column: Hotelier Floating Form -->
            <div class="col-lg-6">
                <div class="bg-white shadow-lg rounded-4 p-4 p-md-5 border">
                    <?php if (!empty($error)): ?>
                        <div class="alert alert-danger mb-4"><i class="fa fa-exclamation-triangle me-2"></i> <?= $error ?></div>
                    <?php endif; ?>

                    <form action="" method="POST">
                        <div class="row g-3">
                            <div class="col-12">
                                <div class="p-3 rounded border" style="background: var(--bg-soft); border-color: rgba(228, 121, 17, 0.25) !important;">
                                    <h6 class="fw-bold mb-1"><i class="fa fa-user me-2" style="color: var(--amazon-orange-deep);"></i> Guest Information</h6>
                                    <p class="mb-0 small text-muted">Booking for: <strong><?= sanitize($_SESSION['full_name']) ?></strong> (<?= sanitize($_SESSION['email'] ?? '') ?>)</p>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-floating">
                                    <input type="date" class="form-control" id="checkin" name="check_in_date" min="<?= $today ?>" value="<?= $default_check_in ?>" required />
                                    <label for="checkin"><i class="fa fa-calendar-alt me-1" style="color: var(--amazon-orange-deep);"></i> Check In Date</label>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-floating">
                                    <input type="date" class="form-control" id="checkout" name="check_out_date" min="<?= $default_check_in ?>" value="<?= $default_check_out ?>" required />
                                    <label for="checkout"><i class="fa fa-calendar-alt me-1" style="color: var(--amazon-orange-deep);"></i> Check Out Date</label>
                                </div>
                            </div>

                            <div class="col-12">
                                <div class="form-floating">
                                    <select class="form-select" id="select_guests" name="guests_count">
                                        <?php for ($g = 1; $g <= max(4, $hotel['room_capacity']); $g++): ?>
                                            <option value="<?= $g ?>"><?= $g ?> Guest<?= $g > 1 ? 's' : '' ?></option>
                                        <?php endfor; ?>
                                    </select>
                                    <label for="select_guests"><i class="fa fa-users me-1" style="color: var(--amazon-orange-deep);"></i> Select Guests</label>
                                </div>
                            </div>

                            <!-- Cost Calculation Display -->
                            <div class="col-12">
                                <div class="p-3 bg-light rounded-3 border" id="price_per_night" data-price="<?= $hotel['price_per_night'] ?>" data-curr="<?= $curr ?>">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <span class="text-muted">Total Nights:</span>
                                        <span class="fw-bold text-dark" id="total_nights">2</span>
                                    </div>
                                    <div class="d-flex justify-content-between align-items-center pt-2 border-top">
                                        <span class="fw-bold fs-5">Estimated Total Cost:</span>
                                        <span class="fw-bold fs-3" id="total_price_calc" style="color: var(--amazon-orange-deep);"><?= dt_format_currency($hotel['price_per_night'] * 2, $curr) ?></span>
                                    </div>
                                </div>
                            </div>

                            <div class="col-12 mt-4">
                                <button type="submit" name="confirm_booking" class="btn btn-digitour-gold w-100 py-3 fw-bold fs-5 text-uppercase rounded-3 shadow">
                                    <i class="fa fa-check-circle me-2"></i> Confirm Reservation
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- DYNAMIC PRICING SCRIPT -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const checkInInput = document.getElementById('checkin');
    const checkOutInput = document.getElementById('checkout');
    const totalNightsSpan = document.getElementById('total_nights');
    const totalPriceCalcSpan = document.getElementById('total_price_calc');
    const priceBox = document.getElementById('price_per_night');

    const pricePerNight = parseFloat(priceBox.getAttribute('data-price')) || 0;
    const curr = priceBox.getAttribute('data-curr') || 'USD';

    let rate = 1.0;
    let symbol = '$';
    if (curr === 'GHS') { rate = 15.5; symbol = '₵'; }
    else if (curr === 'EUR') { rate = 0.92; symbol = '€'; }

    function calculateTotal() {
        if (!checkInInput.value || !checkOutInput.value) return;

        const date1 = new Date(checkInInput.value);
        const date2 = new Date(checkOutInput.value);

        let diffDays = 1;
        if (date2 > date1) {
            const diffTime = Math.abs(date2 - date1);
            diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
        }

        totalNightsSpan.textContent = diffDays;
        const total = diffDays * pricePerNight * rate;
        totalPriceCalcSpan.textContent = symbol + total.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }

    checkInInput.addEventListener('change', function() {
        if (checkInInput.value) {
            checkOutInput.min = checkInInput.value;
        }
        calculateTotal();
    });

    checkOutInput.addEventListener('change', calculateTotal);
});
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
