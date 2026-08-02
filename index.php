<?php
// DigiTour Ghana — Bright Professional Homepage
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/functions.php';

$page_title = 'DigiTour Ghana | Discover Attractions & Book Hotels';

$all_destinations = [];
$featured_hotels = [];
$approved_reviews = [];
$orbit_destinations = [];

$total_destinations = 0;
$total_hotels = 0;
$homepage_limit = 50;

if ($pdo) {
    $total_destinations = (int)$pdo->query('SELECT COUNT(*) FROM destinations')->fetchColumn();
    $total_hotels = (int)$pdo->query('SELECT COUNT(*) FROM hotels')->fetchColumn();

    // Homepage shows Top 50 (featured first) — full catalogue is on destinations.php
    $dest_stmt = $pdo->query("SELECT d.*, COUNT(h.id) AS nearby_hotel_count FROM destinations d LEFT JOIN hotels h ON d.id = h.destination_id GROUP BY d.id ORDER BY d.is_featured DESC, d.id ASC LIMIT {$homepage_limit}");
    $all_destinations = $dest_stmt->fetchAll();

    $hotel_stmt = $pdo->query("SELECT h.*, d.title AS destination_title, d.region FROM hotels h JOIN destinations d ON h.destination_id = d.id ORDER BY h.price_per_night DESC LIMIT 12");
    $featured_hotels = $hotel_stmt->fetchAll();

    $review_stmt = $pdo->query("SELECT r.*, u.full_name, d.title AS dest_title, h.name AS hotel_name FROM reviews r JOIN users u ON r.user_id = u.id LEFT JOIN destinations d ON r.destination_id = d.id LEFT JOIN hotels h ON r.hotel_id = h.id WHERE r.status = 'Approved' ORDER BY r.id DESC LIMIT 6");
    $approved_reviews = $review_stmt->fetchAll();

    $orbit_stmt = $pdo->query("SELECT id, title, region, image_main FROM destinations ORDER BY is_featured DESC, id ASC LIMIT 24");
    $orbit_destinations = $orbit_stmt->fetchAll();
}

/** Bento layout shape + accent for mixed grid — see dt_bento_meta() in functions.php */

// Hero slides — each video plays 10s then advances forever
$hero_slides = [
    [
        'label' => 'Kakum Rainforest',
        'title' => 'Walk Above the Rainforest Canopy',
        'desc'  => 'Experience West Africa’s iconic canopy walkway suspended above ancient tropical rainforest in Ghana’s Central Region.',
        'url'   => 'destination-detail.php?id=32',
        'video' => 'sources/videos/kakum-park.mp4',
        'poster'=> 'sources/images/all_tourist_sites/Kakum%20National%20Park%20Canopy%20Walkway.jpg',
    ],
    [
        'label' => 'Cape Coast Heritage',
        'title' => 'Stand Where History Echoes',
        'desc'  => 'Walk UNESCO World Heritage fortresses overlooking the Atlantic — Cape Coast Castle and Elmina’s storied walls.',
        'url'   => 'destination-detail.php?id=33',
        'video' => 'sources/videos/cape-coast-catle-3.mp4',
        'poster'=> 'sources/images/all_tourist_sites/Cape%20Coast%20Castle.jpg',
    ],
    [
        'label' => 'Larabanga Legacy',
        'title' => 'Discover Ancient Mud Architecture',
        'desc'  => 'Visit Ghana’s oldest mosque (c. 1421) and the sacred Mystic Stone in the heart of the Savannah Region.',
        'url'   => 'destination-detail.php?id=92',
        'video' => 'sources/videos/larabanga-mosque.mp4',
        'poster'=> 'sources/images/all_tourist_sites/Larabanga%20Mosque.jpg',
    ],
    [
        'label' => 'Nkrumah Memorial',
        'title' => 'Honor Ghana’s Founding Vision',
        'desc'  => 'Explore marble gardens, personal artifacts, and the story of independence at Kwame Nkrumah Memorial Park.',
        'url'   => 'destination-detail.php?id=1',
        'video' => 'sources/videos/kwame-nkrumah-museum.mp4',
        'poster'=> 'sources/images/all_tourist_sites/Kwame%20Nkrumah%20Memorial%20Park.jpg',
    ],
];

$hero_json = htmlspecialchars(json_encode(array_map(function ($s) {
    return [
        'label' => $s['label'],
        'title' => $s['title'],
        'desc'  => $s['desc'],
        'url'   => $s['url'],
    ];
}, $hero_slides)), ENT_QUOTES, 'UTF-8');

require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/navbar.php';
?>

<!-- ================= HERO ================= -->
<section class="dt-hero" id="dtHero" data-slides="<?= $hero_json ?>">
  <div class="dt-hero-videos" aria-hidden="true">
    <?php foreach ($hero_slides as $i => $slide): ?>
      <video class="dt-hero-video <?= $i === 0 ? 'is-active' : '' ?>"
             muted playsinline loop preload="<?= $i === 0 ? 'auto' : 'metadata' ?>"
             poster="<?= $slide['poster'] ?>">
        <source src="<?= $slide['video'] ?>" type="video/mp4">
      </video>
    <?php endforeach; ?>
  </div>
  <div class="dt-hero-overlay"></div>

  <div class="container dt-hero-content">
    <div class="row align-items-end">
      <div class="col-lg-8">
        <div class="dt-hero-kicker"><i class="fa-solid fa-compass"></i> Smart Tourism · Ghana</div>
        <h1 class="dt-hero-brand">Digi<span>Tour</span></h1>
        <div class="dt-hero-slide-copy">
          <p class="mb-1 small text-uppercase fw-bold" style="letter-spacing:.12em;color:#FEBD69;" id="dtHeroLabel"><?= sanitize($hero_slides[0]['label']) ?></p>
          <h2 id="dtHeroTitle"><?= sanitize($hero_slides[0]['title']) ?></h2>
          <p id="dtHeroDesc"><?= sanitize($hero_slides[0]['desc']) ?></p>
          <div class="dt-hero-actions">
            <a href="<?= $hero_slides[0]['url'] ?>" class="btn btn-digitour-gold" id="dtHeroCta">
              <i class="fa-solid fa-arrow-right me-2"></i> Explore This Site
            </a>
            <a href="#destinations-grid" class="btn btn-digitour-outline" style="border-color:#fff;color:#fff;">
              Browse All Destinations
            </a>
          </div>
        </div>
        <div class="dt-hero-progress" role="tablist" aria-label="Hero slides">
          <?php foreach ($hero_slides as $i => $slide): ?>
            <button type="button" class="dt-hero-dot <?= $i === 0 ? 'is-active' : '' ?>" aria-label="Slide <?= $i + 1 ?>">
              <span></span>
            </button>
          <?php endforeach; ?>
        </div>
      </div>
    </div>
  </div>

  <!-- Endless circular destination carousel -->
  <div class="dt-orbit-wrap">
    <div class="dt-orbit-track" id="dtOrbitTrack">
      <?php
      $orbit_loop = array_merge($orbit_destinations, $orbit_destinations);
      if (empty($orbit_loop)) {
          $orbit_loop = $all_destinations;
          $orbit_loop = array_merge($orbit_loop, $orbit_loop);
      }
      foreach ($orbit_loop as $od):
          $img = get_destination_main_image($od['title'], $od['image_main'] ?? '');
      ?>
        <a class="dt-orbit-item" href="destination-detail.php?id=<?= (int)$od['id'] ?>" title="<?= sanitize($od['title']) ?>">
          <div class="dt-orbit-circle">
            <img src="<?= $img ?>" alt="<?= sanitize($od['title']) ?>" loading="lazy">
          </div>
          <span><?= sanitize($od['title']) ?></span>
        </a>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- ================= SEARCH ================= -->
<section class="container dt-search-float" id="search-section">
  <div class="dt-search-panel reveal">
    <form id="search-filter-form" onsubmit="return false;">
      <div class="row g-3 align-items-end">
        <div class="col-lg-5 col-md-6">
          <label for="search-input" class="form-label small fw-bold text-uppercase text-secondary mb-1">
            <i class="fa-solid fa-magnifying-glass text-warning me-1"></i> Search destination
          </label>
          <input type="text" id="search-input" class="form-control" placeholder="Try Kakum, Castle, Mole, Waterfall…">
        </div>
        <div class="col-lg-4 col-md-6">
          <label for="region-filter" class="form-label small fw-bold text-uppercase text-secondary mb-1">
            <i class="fa-solid fa-map text-warning me-1"></i> Region
          </label>
          <select id="region-filter" class="form-select">
            <option value="all">All Ghanaian Regions</option>
            <option value="Central Region">Central Region</option>
            <option value="Greater Accra">Greater Accra</option>
            <option value="Ashanti Region">Ashanti Region</option>
            <option value="Volta Region">Volta Region</option>
            <option value="Western Region">Western Region</option>
            <option value="Savannah Region">Savannah Region</option>
            <option value="Eastern Region">Eastern Region</option>
            <option value="Northern Region">Northern Region</option>
          </select>
        </div>
        <div class="col-lg-3">
          <div class="d-flex align-items-center justify-content-lg-end gap-2 pt-2 flex-wrap">
            <span class="dt-badge dt-badge-gold">
              <i class="fa-solid fa-eye"></i>
              Showing <span id="filtered-count"><?= count($all_destinations) ?></span> of <?= (int)$total_destinations ?>
            </span>
          </div>
        </div>
      </div>
    </form>
  </div>
</section>

<!-- ================= LANDING PAGE BACKGROUND VIDEOS (CYCLES 4 VIDEOS EVERY 5s) ================= -->
<div class="dt-grid-section position-relative">
  <div class="dt-section-videos" id="dtLandingBodyVideos" aria-hidden="true">
    <video class="dt-section-video is-active" muted playsinline loop preload="metadata" poster="sources/images/all_tourist_sites/Kakum%20National%20Park%20Canopy%20Walkway.jpg">
      <source src="sources/videos/kakum-park.mp4" type="video/mp4">
    </video>
    <video class="dt-section-video" muted playsinline loop preload="none" poster="sources/images/all_tourist_sites/Cape%20Coast%20Castle.jpg">
      <source src="sources/videos/cape-coast-catle-3.mp4" type="video/mp4">
    </video>
    <video class="dt-section-video" muted playsinline loop preload="none" poster="sources/images/all_tourist_sites/Larabanga%20Mosque.jpg">
      <source src="sources/videos/larabanga-mosque.mp4" type="video/mp4">
    </video>
    <video class="dt-section-video" muted playsinline loop preload="none" poster="sources/images/all_tourist_sites/Kwame%20Nkrumah%20Memorial%20Park.jpg">
      <source src="sources/videos/kwame-nkrumah-museum.mp4" type="video/mp4">
    </video>
    <div class="dt-section-video-wash"></div>
  </div>

  <!-- ================= WHY / STATS ================= -->
  <section class="section-shell position-relative" style="z-index:2">
  <div class="container">
    <div class="row g-4 align-items-center">
      <div class="col-lg-5 reveal reveal-left">
        <span class="section-eyebrow"><i class="fa-solid fa-sparkles"></i> Why DigiTour</span>
        <h2 class="section-title text-start">Ghana’s attractions & hotels — one bright place</h2>
        <p class="text-secondary mb-0">
          Stop juggling Google, Facebook pages, and phone calls. DigiTour brings destinations, nearby stays, reviews, and bookings into a single professional portal built for Ghana.
        </p>
      </div>
      <div class="col-lg-7">
        <div class="dt-stats">
          <div class="dt-stat reveal reveal-delay-1">
            <div class="dt-stat-num" data-count="<?= max($total_destinations, 1) ?>">0</div>
            <div class="dt-stat-label">Tourist Sites</div>
          </div>
          <div class="dt-stat reveal reveal-delay-2">
            <div class="dt-stat-num" data-count="16">0</div>
            <div class="dt-stat-label">Regions Covered</div>
          </div>
          <div class="dt-stat reveal reveal-delay-3">
            <div class="dt-stat-num" data-count="<?= max($total_hotels, 1) ?>">0</div>
            <div class="dt-stat-label">Nearby Hotels</div>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- ================= HOW IT WORKS ================= -->
<section class="section-shell section-soft">
  <div class="container">
    <div class="text-center mb-5 reveal">
      <span class="section-eyebrow"><i class="fa-solid fa-route"></i> How it works</span>
      <h2 class="section-title">Plan. Book. Explore.</h2>
      <p class="section-lead">Three clear steps from first glance to confirmed stay.</p>
    </div>
    <div class="row g-4">
      <div class="col-md-4 reveal reveal-delay-1">
        <div class="dt-step">
          <span class="dt-step-num">01</span>
          <div class="dt-step-icon"><i class="fa-solid fa-binoculars"></i></div>
          <h5 class="fw-bold">Discover destinations</h5>
          <p class="text-secondary mb-0 small">Browse featured sites, filter by region, or search instantly — no account needed.</p>
        </div>
      </div>
      <div class="col-md-4 reveal reveal-delay-2">
        <div class="dt-step">
          <span class="dt-step-num">02</span>
          <div class="dt-step-icon"><i class="fa-solid fa-hotel"></i></div>
          <h5 class="fw-bold">Compare nearby hotels</h5>
          <p class="text-secondary mb-0 small">Every attraction shows mapped accommodations with nightly rates and capacity.</p>
        </div>
      </div>
      <div class="col-md-4 reveal reveal-delay-3">
        <div class="dt-step">
          <span class="dt-step-num">03</span>
          <div class="dt-step-icon"><i class="fa-solid fa-calendar-check"></i></div>
          <h5 class="fw-bold">Book & track status</h5>
          <p class="text-secondary mb-0 small">Register once, reserve your dates, and follow confirmations on your dashboard.</p>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- ================= FEATURED STRIP ================= -->
<?php if (!empty($all_destinations)): ?>
<section class="section-shell">
  <div class="container">
    <div class="d-flex flex-wrap justify-content-between align-items-end gap-3 mb-4 reveal">
      <div>
        <span class="section-eyebrow"><i class="fa-solid fa-star"></i> Featured</span>
        <h2 class="section-title text-start mb-0">Must-see Ghana</h2>
      </div>
      <a href="destinations.php" class="btn btn-digitour-outline btn-sm">View all destinations</a>
    </div>
    <div class="dt-featured-strip reveal">
      <?php foreach (array_slice($all_destinations, 0, 8) as $feat): ?>
        <a class="dt-featured-card" href="destination-detail.php?id=<?= (int)$feat['id'] ?>">
          <img src="<?= get_destination_main_image($feat['title'], $feat['image_main']) ?>" alt="<?= sanitize($feat['title']) ?>" loading="lazy">
          <div class="dt-featured-card-cap">
            <span class="dt-badge dt-badge-gold mb-2"><?= sanitize($feat['region']) ?></span>
            <h5><?= sanitize($feat['title']) ?></h5>
            <small><?= sanitize($feat['category']) ?> · <?= (int)$feat['nearby_hotel_count'] ?> hotels nearby</small>
          </div>
        </a>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<?php endif; ?>

<!-- ================= DESTINATIONS BENTO GRID ================= -->
<section class="section-shell dt-grid-section" id="destinations-grid">
  <div class="dt-section-videos" id="dtGridVideos" aria-hidden="true">
    <video class="dt-section-video is-active" muted playsinline loop preload="metadata" poster="sources/images/all_tourist_sites/Kakum%20National%20Park%20Canopy%20Walkway.jpg">
      <source src="sources/videos/kakum-park.mp4" type="video/mp4">
    </video>
    <video class="dt-section-video" muted playsinline loop preload="none" poster="sources/images/all_tourist_sites/Cape%20Coast%20Castle.jpg">
      <source src="sources/videos/cape-coast-catle-3.mp4" type="video/mp4">
    </video>
    <video class="dt-section-video" muted playsinline loop preload="none" poster="sources/images/all_tourist_sites/Larabanga%20Mosque.jpg">
      <source src="sources/videos/larabanga-mosque.mp4" type="video/mp4">
    </video>
    <video class="dt-section-video" muted playsinline loop preload="none" poster="sources/images/all_tourist_sites/Kwame%20Nkrumah%20Memorial%20Park.jpg">
      <source src="sources/videos/kwame-nkrumah-museum.mp4" type="video/mp4">
    </video>
    <div class="dt-section-video-wash"></div>
  </div>

  <div class="container position-relative" style="z-index:2">
    <div class="text-center mb-4 reveal">
      <span class="section-eyebrow"><i class="fa-solid fa-map-location-dot"></i> Explore</span>
      <h2 class="section-title">Top <?= (int)$homepage_limit ?> Ghana destinations</h2>
      <p class="section-lead">
        Showing the top <?= count($all_destinations) ?> of <strong><?= (int)$total_destinations ?></strong> tourist sites in our catalogue (featured first). Mixed layouts — wide panoramas, circles, tall frames — tap any tile for details.
      </p>
      <?php if ($total_destinations > $homepage_limit): ?>
        <a href="destinations.php" class="btn btn-digitour-gold btn-sm mt-2">
          View all <?= (int)$total_destinations ?> destinations <i class="fa-solid fa-arrow-right ms-1"></i>
        </a>
      <?php endif; ?>
    </div>

    <div class="bento-grid" id="destination-container">
      <?php foreach ($all_destinations as $i => $dest):
        $meta = dt_bento_meta($i);
        $img_src = get_destination_main_image($dest['title'], $dest['image_main']);
        $lazy = $i < 4 ? 'eager' : 'lazy';
        $prio = $i < 2 ? 'high' : 'auto';
      ?>
        <a href="destination-detail.php?id=<?= (int)$dest['id'] ?>"
           class="destination-item bento-item bento-<?= $meta['shape'] ?> accent-<?= $meta['accent'] ?> scroll-fx"
           data-title="<?= strtolower(sanitize($dest['title'])) ?>"
           data-region="<?= sanitize($dest['region']) ?>"
           data-category="<?= sanitize($dest['category']) ?>"
           style="--i:<?= min($i, 12) ?>">
          <div class="bento-media">
            <img src="<?= $img_src ?>" alt="<?= sanitize($dest['title']) ?>" loading="eager" decoding="async" fetchpriority="<?= $i < 6 ? 'high' : 'auto' ?>" width="640" height="420">
          </div>
          <div class="bento-cap">
            <span class="bento-chip"><?= sanitize($dest['region']) ?></span>
            <h3><?= sanitize($dest['title']) ?></h3>
            <?php if (!in_array($meta['shape'], ['circle'], true)): ?>
              <p><?= dt_short_desc($dest['description'], $meta['shape'] === 'hero' || $meta['shape'] === 'wide' ? 140 : 80) ?></p>
            <?php endif; ?>
            <span class="bento-meta"><i class="fa-solid fa-hotel"></i> <?= (int)$dest['nearby_hotel_count'] ?> hotels · <?= sanitize($dest['category']) ?></span>
          </div>
        </a>
      <?php endforeach; ?>

      <?php if (empty($all_destinations)): ?>
        <div class="bento-item bento-wide" style="grid-column:1/-1;min-height:180px;display:grid;place-items:center;background:#fff;border-radius:24px;">
          <div class="text-center p-4">
            <p class="text-secondary mb-3">No destinations loaded yet. Run database setup first.</p>
            <a href="setup_db.php" class="btn btn-digitour-gold">Setup Database</a>
          </div>
        </div>
      <?php endif; ?>
    </div>
  </div>
</section>

<!-- ================= HOTELS BENTO ================= -->
<section class="section-shell section-warm" id="nearby-hotels">
  <div class="container">
    <div class="text-center mb-4 reveal">
      <span class="section-eyebrow"><i class="fa-solid fa-bed"></i> Stay</span>
      <h2 class="section-title">Featured accommodations</h2>
      <p class="section-lead">Dynamic hotel tiles from <?= (int)$total_hotels ?> mapped stays — book with clear nightly rates.</p>
    </div>
    <div class="bento-grid bento-hotels">
      <?php foreach ($featured_hotels as $i => $hotel):
        $meta = dt_bento_meta($i + 2);
        $img = get_hotel_main_image($hotel['name'], $hotel['image']);
      ?>
        <article class="bento-item bento-<?= $meta['shape'] === 'circle' ? 'square' : $meta['shape'] ?> accent-<?= $meta['accent'] ?> hotel-bento scroll-fx" style="--i:<?= min($i, 8) ?>">
          <div class="bento-media">
            <img src="<?= $img ?>" alt="<?= sanitize($hotel['name']) ?>" loading="<?= $i < 3 ? 'eager' : 'lazy' ?>" decoding="async" width="640" height="420">
            <span class="hotel-price-tag bento-price">$<?= number_format((float)$hotel['price_per_night'], 2) ?>/night</span>
          </div>
          <div class="bento-cap">
            <span class="bento-chip"><?= sanitize($hotel['region']) ?></span>
            <h3><?= sanitize($hotel['name']) ?></h3>
            <p>Near <strong><?= sanitize($hotel['destination_title']) ?></strong> · <?= (int)$hotel['room_capacity'] ?> bed(s)</p>
            <div class="d-flex gap-2 flex-wrap mt-2">
              <a class="btn btn-digitour-gold btn-sm" href="book-hotel.php?hotel_id=<?= (int)$hotel['id'] ?>">Book now</a>
              <a class="btn btn-digitour-outline btn-sm" href="destination-detail.php?id=<?= (int)$hotel['destination_id'] ?>">Site</a>
            </div>
          </div>
        </article>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- ================= REVIEWS ================= -->
<?php if (!empty($approved_reviews)): ?>
<section class="section-shell section-soft">
  <div class="container">
    <div class="text-center mb-5 reveal">
      <span class="section-eyebrow"><i class="fa-solid fa-comments"></i> Community</span>
      <h2 class="section-title">What travellers say</h2>
      <p class="section-lead">Approved reviews from visitors exploring Ghana with DigiTour.</p>
    </div>
    <div class="row g-4">
      <?php foreach ($approved_reviews as $i => $rev): ?>
        <div class="col-md-4 reveal <?= $i % 3 === 1 ? 'reveal-delay-1' : ($i % 3 === 2 ? 'reveal-delay-2' : '') ?>">
          <div class="review-card">
            <div class="d-flex align-items-center mb-3">
              <div class="avatar-circle me-3"><?= strtoupper(substr(sanitize($rev['full_name']), 0, 1)) ?></div>
              <div>
                <h6 class="fw-bold mb-0"><?= sanitize($rev['full_name']) ?></h6>
                <small class="text-secondary"><?= sanitize($rev['dest_title'] ?? $rev['hotel_name'] ?? 'Ghana traveller') ?></small>
              </div>
            </div>
            <p class="text-secondary small flex-grow-1">“<?= sanitize($rev['comment']) ?>”</p>
            <div class="d-flex justify-content-between align-items-center border-top pt-2 mt-2">
              <div><?= render_stars($rev['rating']) ?></div>
              <span class="dt-badge dt-badge-teal">Approved</span>
            </div>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<?php endif; ?>

<!-- ================= CTA ================= -->
<section class="section-shell">
  <div class="container">
    <div class="reveal p-4 p-md-5 rounded-4 text-center" style="background:linear-gradient(135deg,#232F3E 0%,#37475A 55%,#E47911 140%);color:#fff;box-shadow:var(--shadow-lg);">
      <h2 class="text-white mb-2" style="font-family:var(--font-display);font-weight:800;">Ready to explore Ghana?</h2>
      <p class="mb-4 mx-auto" style="max-width:520px;opacity:.9;">Create a free account to book hotels, leave reviews, and track your reservations.</p>
      <div class="d-flex flex-wrap justify-content-center gap-2">
        <?php if (is_logged_in()): ?>
          <a href="<?= is_admin() ? 'admin/dashboard.php' : 'tourist-dashboard.php' ?>" class="btn btn-digitour-gold">Go to dashboard</a>
        <?php else: ?>
          <a href="register.php" class="btn btn-digitour-gold">Create free account</a>
          <a href="login.php" class="btn btn-digitour-outline" style="border-color:#fff;color:#fff;">Log in</a>
        <?php endif; ?>
        <a href="inquiry.php" class="btn btn-digitour-outline" style="border-color:#FEBD69;color:#FEBD69;">Ask a question</a>
      </div>
    </div>
  </div>
</section>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
