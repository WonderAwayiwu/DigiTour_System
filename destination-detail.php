<?php
// destination-detail.php - Single Destination View + Hotelier Nearby Accommodations + Review System
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/functions.php';

$destination_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Fetch Destination
$stmt = $pdo->prepare("SELECT * FROM destinations WHERE id = ?");
$stmt->execute([$destination_id]);
$destination = $stmt->fetch();

if (!$destination) {
    header("Location: destinations.php");
    exit;
}

$page_title = $destination['title'] . " | DigiTour Ghana";
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/navbar.php';

// Fetch Nearby Hotels for this destination (Relational Query)
$hotel_stmt = $pdo->prepare("SELECT * FROM hotels WHERE destination_id = ? ORDER BY price_per_night ASC");
$hotel_stmt->execute([$destination_id]);
$nearby_hotels = $hotel_stmt->fetchAll();

// Fetch Approved Reviews for this destination
$review_stmt = $pdo->prepare("SELECT r.*, u.full_name FROM reviews r JOIN users u ON r.user_id = u.id WHERE r.destination_id = ? AND r.status = 'Approved' ORDER BY r.id DESC");
$review_stmt->execute([$destination_id]);
$approved_reviews = $review_stmt->fetchAll();

// Handle Review Submission
$review_msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_review'])) {
    if (!is_logged_in()) {
        $review_msg = '<div class="alert alert-warning">Please <a href="login.php">log in</a> to leave a review.</div>';
    } else {
        $rating = (int)$_POST['rating'];
        $comment = sanitize($_POST['comment']);
        $user_id = $_SESSION['user_id'];

        if ($rating >= 1 && $rating <= 5 && !empty($comment)) {
            $ins = $pdo->prepare("INSERT INTO reviews (user_id, destination_id, rating, comment, status) VALUES (?, ?, ?, ?, 'Pending')");
            $ins->execute([$user_id, $destination_id, $rating, $comment]);
            $review_msg = '<div class="alert alert-success"><i class="fa-solid fa-check-circle me-1"></i> Thank you! Your review has been submitted and is pending Admin approval.</div>';
        } else {
            $review_msg = '<div class="alert alert-danger">Please select a rating and write your comments.</div>';
        }
    }
}
?>

<?php
$dest_images = get_destination_images($destination['title'], $destination['image_main'], $destination['id']);
$hero_bg_image = $dest_images[0];
?>

<!-- DESTINATION HERO BANNER -->
<section class="detail-hero" style="--detail-bg: url('<?= $hero_bg_image ?>');">
    <div class="container py-2">
        <div class="row align-items-center">
            <div class="col-lg-8 reveal reveal-left">
                <a href="javascript:history.back()" class="btn btn-sm btn-outline-light mb-3 rounded-pill px-3 shadow-sm d-inline-flex align-items-center gap-2" style="background: rgba(0,0,0,0.3); border-color: rgba(255,255,255,0.4);">
                    <i class="fa-solid fa-arrow-left"></i> Back to Destinations
                </a>
                <br>
                <span class="dt-badge dt-badge-gold mb-2 me-2"><?= sanitize($destination['region']) ?></span>
                <span class="dt-badge dt-badge-navy mb-2"><?= sanitize($destination['category']) ?></span>
                <h1 class="dt-detail-title text-white mb-3"><?= sanitize($destination['title']) ?></h1>
                <p class="lead mb-0 dt-detail-lead"><i class="fa-solid fa-location-dot me-2" style="color:#FEBD69"></i> <?= sanitize($destination['location_contact']) ?></p>
            </div>
            <div class="col-lg-4 text-lg-end mt-4 mt-lg-0 reveal reveal-right">
                <a href="#nearby-hotels" class="btn btn-digitour-gold"><i class="fa-solid fa-bed me-2"></i> Book Nearby Hotel</a>
            </div>
        </div>
    </div>
</section>

<!-- MAIN CONTENT SECTION -->
<section class="section-shell section-soft">
    <div class="container">
        <div class="row g-4 g-lg-5">
            <!-- Left Column: Overview & History -->
            <div class="col-lg-8 min-w-0">
                <!-- Article Navigation Tabs -->
                <div class="dt-article-nav sticky-top">
                    <a href="#article-overview" class="btn btn-sm btn-outline-dark fw-semibold"><i class="fa-solid fa-align-left me-1"></i> Overview</a>
                    <a href="#article-history" class="btn btn-sm btn-outline-dark fw-semibold"><i class="fa-solid fa-landmark me-1"></i> History</a>
                    <a href="#article-gallery" class="btn btn-sm btn-outline-dark fw-semibold"><i class="fa-solid fa-images me-1"></i> Gallery</a>
                    <a href="#article-reviews" class="btn btn-sm btn-outline-dark fw-semibold"><i class="fa-solid fa-star me-1"></i> Reviews</a>
                    <a href="#nearby-hotels" class="btn btn-sm btn-digitour-gold fw-semibold"><i class="fa-solid fa-bed me-1"></i> Hotels</a>
                </div>

                <!-- OVERVIEW ARTICLE SECTION -->
                <div id="article-overview" class="mb-4 mb-md-5 reveal p-3 p-md-4 bg-white rounded-4 shadow-sm border">
                    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 border-bottom pb-3 mb-3 mb-md-4">
                        <h3 class="fw-bold mb-0 h4" style="color:var(--amazon-navy)"><i class="fa-solid fa-circle-info me-2" style="color:var(--amazon-orange)"></i> Overview</h3>
                        <span class="badge bg-light text-dark border px-3 py-2"><i class="fa-solid fa-book-bookmark me-1 text-warning"></i> Heritage</span>
                    </div>
                    <div class="dt-article-body text-secondary">
                        <?= $destination['description'] ?>
                    </div>
                </div>

                <!-- HISTORICAL ARTICLE SECTION -->
                <?php if (!empty($destination['history'])): ?>
                <div id="article-history" class="mb-4 mb-md-5 p-3 p-md-4 rounded-4 shadow-sm reveal" style="background:#FAF8F5; border:1px solid #EAE3D2; border-left:6px solid var(--amazon-orange);">
                    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 border-bottom pb-3 mb-3 mb-md-4">
                        <h3 class="fw-bold mb-0 h4" style="color:var(--amazon-navy)"><i class="fa-solid fa-landmark me-2" style="color:var(--amazon-orange)"></i> History &amp; Legacy</h3>
                        <span class="badge bg-warning-subtle text-dark border border-warning px-3 py-2"><i class="fa-solid fa-graduation-cap me-1"></i> Archive</span>
                    </div>
                    <div class="dt-article-body text-dark">
                        <?= $destination['history'] ?>
                    </div>
                </div>
                <?php endif; ?>

                <!-- AUTO-PLAYING PHOTO GALLERY -->
                <div id="article-gallery" class="mb-5 reveal">
                    <h3 class="fw-bold border-bottom pb-2 mb-3"><i class="fa-solid fa-images me-2" style="color:var(--amazon-orange)"></i> Photo Gallery (<?= count($dest_images) ?> photos)</h3>

                    <div id="destinationGalleryCarousel" class="carousel slide dt-gallery-carousel mb-3" data-bs-ride="carousel" data-bs-interval="4000" data-bs-pause="false" data-bs-wrap="true">
                        <div class="carousel-indicators">
                            <?php foreach ($dest_images as $idx => $img): ?>
                                <button type="button" data-bs-target="#destinationGalleryCarousel" data-bs-slide-to="<?= $idx ?>" class="<?= ($idx === 0) ? 'active' : '' ?>" aria-label="Slide <?= $idx + 1 ?>"></button>
                            <?php endforeach; ?>
                        </div>
                        <div class="carousel-inner">
                            <?php foreach ($dest_images as $idx => $img): ?>
                                <div class="carousel-item <?= ($idx === 0) ? 'active' : '' ?>">
                                    <img src="<?= $img ?>" class="d-block w-100" alt="<?= sanitize($destination['title']) ?> Photo <?= $idx + 1 ?>">
                                    <div class="carousel-caption d-none d-md-block rounded p-2" style="background:rgba(35,47,62,.55);">
                                        <p class="mb-0 fw-bold"><?= sanitize($destination['title']) ?> — <?= $idx + 1 ?> / <?= count($dest_images) ?></p>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        <button class="carousel-control-prev" type="button" data-bs-target="#destinationGalleryCarousel" data-bs-slide="prev">
                            <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                            <span class="visually-hidden">Previous</span>
                        </button>
                        <button class="carousel-control-next" type="button" data-bs-target="#destinationGalleryCarousel" data-bs-slide="next">
                            <span class="carousel-control-next-icon" aria-hidden="true"></span>
                            <span class="visually-hidden">Next</span>
                        </button>
                    </div>

                    <div class="dt-thumb-rail <?= count($dest_images) > 6 ? 'auto-scroll' : '' ?>">
                        <?php
                        $thumb_loop = count($dest_images) > 6 ? array_merge($dest_images, $dest_images) : $dest_images;
                        foreach ($thumb_loop as $idx => $img):
                            $real_idx = $idx % count($dest_images);
                        ?>
                            <img src="<?= $img ?>" data-slide="<?= $real_idx ?>" alt="Thumbnail <?= $real_idx + 1 ?>" class="<?= $real_idx === 0 ? 'is-active' : '' ?>">
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- USER REVIEWS SECTION -->
                <div id="article-reviews" class="mb-5 reveal">
                    <h3 class="fw-bold border-bottom pb-2 mb-4 h4"><i class="fa-solid fa-comments me-2" style="color:var(--amazon-orange)"></i> Reviews</h3>
                    
                    <?= $review_msg ?>

                    <?php if (empty($approved_reviews)): ?>
                        <p class="text-secondary p-3 rounded-3" style="background:var(--bg-soft)">No reviews approved for this site yet. Be the first to share your experience!</p>
                    <?php else: ?>
                        <div class="d-flex flex-column gap-3 mb-4">
                            <?php foreach ($approved_reviews as $rev): ?>
                                <div class="review-card">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <div class="d-flex align-items-center">
                                            <div class="avatar-circle me-2"><?= strtoupper(substr($rev['full_name'], 0, 1)) ?></div>
                                            <div>
                                                <h6 class="fw-bold mb-0"><?= sanitize($rev['full_name']) ?></h6>
                                                <span class="small text-secondary"><?= date('M d, Y', strtotime($rev['created_at'])) ?></span>
                                            </div>
                                        </div>
                                        <div><?= render_stars($rev['rating']) ?></div>
                                    </div>
                                    <p class="mb-0 small text-secondary"><?= sanitize($rev['comment']) ?></p>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>

                    <div class="auth-card mt-4">
                        <h5 class="fw-bold mb-3"><i class="fa-solid fa-pen-to-square me-2" style="color:var(--amazon-orange)"></i> Leave a Review for <?= sanitize($destination['title']) ?></h5>
                        <?php if (is_logged_in()): ?>
                            <form action="" method="POST">
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Your Rating</label>
                                    <select name="rating" class="form-select w-auto" required>
                                        <option value="5">5 Stars (Excellent)</option>
                                        <option value="4">4 Stars (Very Good)</option>
                                        <option value="3">3 Stars (Average)</option>
                                        <option value="2">2 Stars (Poor)</option>
                                        <option value="1">1 Star (Very Poor)</option>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Your Review Comments</label>
                                    <textarea name="comment" class="form-control" rows="3" placeholder="Describe your experience visiting this site..." required></textarea>
                                </div>
                                <button type="submit" name="submit_review" class="btn btn-digitour-primary"><i class="fa-solid fa-paper-plane me-1"></i> Submit Review</button>
                            </form>
                        <?php else: ?>
                            <p class="text-secondary mb-2">You must be logged in as a registered tourist to submit reviews.</p>
                            <a href="login.php" class="btn btn-digitour-outline btn-sm"><i class="fa-solid fa-right-to-bracket me-1"></i> Log In to Review</a>
                        <?php endif; ?>
                    </div>
                </div>

            </div>

            <!-- Right Column: Redesigned Non-Overlapping Destination Facts -->
            <div class="col-lg-4">
                <div class="dt-sidebar-sticky reveal reveal-right">
                    <div class="card border-0 shadow-sm rounded-4 overflow-hidden bg-white p-4">
                        <div class="d-flex align-items-center justify-content-between border-bottom pb-3 mb-3">
                            <h5 class="fw-bold mb-0 text-dark"><i class="fa-solid fa-compass me-2" style="color:var(--amazon-orange)"></i> Destination Facts</h5>
                            <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1"><i class="fa-solid fa-check-circle me-1"></i> Verified</span>
                        </div>
                        <ul class="list-group list-group-flush mb-4">
                            <li class="list-group-item d-flex justify-content-between align-items-center px-0 bg-transparent py-2">
                                <span class="text-secondary fw-semibold"><i class="fa-solid fa-map-pin me-2 text-warning"></i> Region</span>
                                <span class="fw-bold text-dark"><?= sanitize($destination['region']) ?></span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center px-0 bg-transparent py-2">
                                <span class="text-secondary fw-semibold"><i class="fa-solid fa-layer-group me-2 text-info"></i> Category</span>
                                <span class="fw-bold text-dark"><?= sanitize($destination['category']) ?></span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center px-0 bg-transparent py-2">
                                <span class="text-secondary fw-semibold"><i class="fa-solid fa-camera me-2 text-danger"></i> Photos</span>
                                <span class="dt-badge dt-badge-gold fw-bold"><?= count($dest_images) ?> photos</span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center px-0 bg-transparent py-2">
                                <span class="text-secondary fw-semibold"><i class="fa-solid fa-hotel me-2 text-primary"></i> Accommodations</span>
                                <span class="dt-badge dt-badge-teal fw-bold"><?= count($nearby_hotels) ?> available</span>
                            </li>
                        </ul>

                        <div class="d-grid gap-2 mb-3">
                            <a href="#nearby-hotels" class="btn btn-digitour-gold py-2.5 fw-bold shadow-sm">
                                <i class="fa-solid fa-bed me-2"></i> Book Nearby Hotels
                            </a>
                            <a href="inquiry.php?site=<?= urlencode($destination['title']) ?>" class="btn btn-digitour-outline py-2.5 fw-bold">
                                <i class="fa-solid fa-paper-plane me-2"></i> Ask Admin a Question
                            </a>
                        </div>

                        <div class="p-3 rounded-3 bg-light text-center border">
                            <small class="text-secondary d-block"><i class="fa-solid fa-shield-cat me-1 text-success"></i> Instant Confirmation & Guided Tours Available</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- HOTELS NEARBY -->
<section id="nearby-hotels" class="section-shell section-warm">
    <div class="container">
        <div class="text-center mb-5 reveal">
            <span class="section-eyebrow"><i class="fa-solid fa-hotel"></i> Accommodations</span>
            <h2 class="section-title">Hotels near <?= sanitize($destination['title']) ?></h2>
            <p class="section-lead">Verified lodges and hotels mapped to this attraction.</p>
        </div>

        <?php if (empty($nearby_hotels)): ?>
            <div class="alert alert-info py-4 text-center reveal">
                <i class="fa-solid fa-hotel fa-2x mb-2 d-block"></i>
                Currently no hotels registered near this location. Check back soon or inquire with the Admin!
            </div>
        <?php else: ?>
            <div class="bento-grid bento-hotels">
                <?php foreach ($nearby_hotels as $hi => $hotel):
                    $meta = dt_bento_meta($hi + 1);
                    $shape = $meta['shape'] === 'circle' ? 'square' : $meta['shape'];
                    $h_images = get_hotel_images($hotel['name'], $hotel['image']);
                    $img = $h_images[0] ?? '';
                ?>
                    <article class="bento-item bento-<?= $shape ?> accent-<?= $meta['accent'] ?> hotel-bento reveal <?= $hi % 3 === 1 ? 'reveal-delay-1' : ($hi % 3 === 2 ? 'reveal-delay-2' : '') ?>" style="--i:<?= $hi ?>">
                        <div class="bento-media">
                            <img src="<?= $img ?>" alt="<?= sanitize($hotel['name']) ?>" loading="lazy">
                            <div class="bento-shine"></div>
                            <span class="hotel-price-tag bento-price">$<?= number_format($hotel['price_per_night'], 2) ?>/night</span>
                        </div>
                        <div class="bento-cap">
                            <span class="bento-chip"><?= (int)$hotel['room_capacity'] ?> bed(s)</span>
                            <h3><?= sanitize($hotel['name']) ?></h3>
                            <p><?= sanitize(mb_strimwidth($hotel['description'], 0, 90, '…')) ?></p>
                            <div class="d-flex gap-2 flex-wrap mt-2">
                                <a class="btn btn-digitour-gold btn-sm" href="book-hotel.php?hotel_id=<?= $hotel['id'] ?>">Book Now</a>
                                <a class="btn btn-digitour-outline btn-sm" href="<?= DT_ADMIN_WHATSAPP ?>?text=<?= rawurlencode('Hi, I want to ask about ' . $hotel['name'] . ' near ' . $destination['title']) ?>" target="_blank" rel="noopener"><i class="fab fa-whatsapp"></i></a>
                            </div>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>


