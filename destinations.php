<?php
// destinations.php - Browse ALL Ghana destinations (bento layout)
$page_title = 'Browse All Ghanaian Tourist Destinations';
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/navbar.php';

$selected_region = isset($_GET['region']) ? sanitize($_GET['region']) : '';
$selected_category = isset($_GET['category']) ? sanitize($_GET['category']) : '';

$total_all = (int)$pdo->query('SELECT COUNT(*) FROM destinations')->fetchColumn();

$sql = 'SELECT d.*, COUNT(h.id) AS nearby_hotel_count FROM destinations d LEFT JOIN hotels h ON d.id = h.destination_id';
$params = [];
$conditions = [];

if (!empty($selected_region)) {
    $conditions[] = 'd.region = ?';
    $params[] = $selected_region;
}
if (!empty($selected_category)) {
    $conditions[] = 'd.category = ?';
    $params[] = $selected_category;
}

if (!empty($conditions)) {
    $sql .= ' WHERE ' . implode(' AND ', $conditions);
}

$sql .= ' GROUP BY d.id ORDER BY d.is_featured DESC, d.id ASC';

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$destinations = $stmt->fetchAll();
?>

<section class="detail-hero position-relative overflow-hidden dt-dest-banner">
    <div class="dt-section-videos" id="destBannerVideos" aria-hidden="true">
        <video class="dt-section-video is-active" muted playsinline loop preload="metadata" poster="sources/images/all_tourist_sites/Cape%20Coast%20Castle.jpg">
            <source src="sources/videos/cape-coast-catle-3.mp4" type="video/mp4">
        </video>
        <video class="dt-section-video" muted playsinline loop preload="none" poster="sources/images/all_tourist_sites/Kakum%20National%20Park%20Canopy%20Walkway.jpg">
            <source src="sources/videos/kakum-park.mp4" type="video/mp4">
        </video>
        <video class="dt-section-video" muted playsinline loop preload="none" poster="sources/images/all_tourist_sites/Larabanga%20Mosque.jpg">
            <source src="sources/videos/larabanga-mosque.mp4" type="video/mp4">
        </video>
        <video class="dt-section-video" muted playsinline loop preload="none" poster="sources/images/all_tourist_sites/Kwame%20Nkrumah%20Memorial%20Park.jpg">
            <source src="sources/videos/kwame-nkrumah-museum.mp4" type="video/mp4">
        </video>
        <div class="dt-section-video-wash" style="background: linear-gradient(135deg, rgba(35, 47, 62, 0.72), rgba(255, 153, 0, 0.45));"></div>
    </div>
    <div class="container text-center position-relative" style="z-index: 3;">
        <span class="section-eyebrow" style="background:rgba(255,255,255,.2);color:#fff;border-color:rgba(255,255,255,.35)">Full catalogue</span>
        <h1 class="dt-page-hero-title text-white mb-2">Explore <?= (int)$total_all ?> destinations</h1>
        <p class="lead mb-0 mx-auto" style="max-width:560px;color:rgba(255,255,255,.9)">Complete directory — castles, canopy walks, parks, waterfalls, and beaches in a dynamic mixed layout.</p>
    </div>
</section>

<div class="py-3" style="background:rgba(255,255,255,.85);backdrop-filter:blur(8px);border-bottom:1px solid var(--border-soft)">
    <div class="container d-flex flex-wrap align-items-center justify-content-center gap-2">
        <a href="destinations.php" class="category-pill <?= empty($selected_category) ? 'active' : '' ?>">
            <i class="fa-solid fa-layer-group"></i> All
        </a>
        <a href="destinations.php?category=History" class="category-pill <?= ($selected_category == 'History') ? 'active' : '' ?>">
            <i class="fa-solid fa-landmark"></i> History
        </a>
        <a href="destinations.php?category=Nature+%26+Wildlife" class="category-pill <?= ($selected_category == 'Nature & Wildlife') ? 'active' : '' ?>">
            <i class="fa-solid fa-tree"></i> Nature & Wildlife
        </a>
        <a href="destinations.php?category=Beach" class="category-pill <?= ($selected_category == 'Beach') ? 'active' : '' ?>">
            <i class="fa-solid fa-umbrella-beach"></i> Beaches
        </a>
        <a href="destinations.php?category=History+%26+Culture" class="category-pill <?= ($selected_category == 'History & Culture') ? 'active' : '' ?>">
            <i class="fa-solid fa-masks-theater"></i> Heritage
        </a>
    </div>
</div>

<section class="section-shell">
    <div class="container">
        <div class="dt-search-panel mb-4 reveal">
            <div class="row g-3 align-items-end">
                <div class="col-md-6">
                    <input type="text" id="search-input" class="form-control" placeholder="Search by attraction title or keyword…">
                </div>
                <div class="col-md-4">
                    <select id="region-filter" class="form-select">
                        <option value="all">Filter by Region</option>
                        <option value="Central Region" <?= ($selected_region == 'Central Region') ? 'selected' : '' ?>>Central Region</option>
                        <option value="Greater Accra" <?= ($selected_region == 'Greater Accra') ? 'selected' : '' ?>>Greater Accra</option>
                        <option value="Savannah Region" <?= ($selected_region == 'Savannah Region') ? 'selected' : '' ?>>Savannah Region</option>
                        <option value="Western Region" <?= ($selected_region == 'Western Region') ? 'selected' : '' ?>>Western Region</option>
                        <option value="Eastern Region" <?= ($selected_region == 'Eastern Region') ? 'selected' : '' ?>>Eastern Region</option>
                        <option value="Volta Region" <?= ($selected_region == 'Volta Region') ? 'selected' : '' ?>>Volta Region</option>
                        <option value="Ashanti Region" <?= ($selected_region == 'Ashanti Region') ? 'selected' : '' ?>>Ashanti Region</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <span class="dt-badge dt-badge-gold w-100 justify-content-center">
                        <span id="filtered-count"><?= count($destinations) ?></span> / <?= (int)$total_all ?>
                    </span>
                </div>
            </div>
        </div>

        <div class="bento-grid" id="destination-container">
            <?php if (empty($destinations)): ?>
                <div class="bento-item bento-wide" style="grid-column:1/-1;min-height:180px;display:grid;place-items:center;background:#fff;border-radius:24px;">
                    <div class="text-center p-4">
                        <p class="lead mb-3">No destinations match your filter criteria.</p>
                        <a href="destinations.php" class="btn btn-digitour-outline">Reset Filters</a>
                    </div>
                </div>
            <?php else: ?>
                <?php foreach ($destinations as $i => $dest):
                    $meta = dt_bento_meta($i);
                    $img_src = get_destination_main_image($dest['title'], $dest['image_main']);
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
                            <?php if ($meta['shape'] !== 'circle'): ?>
                                <p><?= dt_short_desc($dest['description'], $meta['shape'] === 'hero' || $meta['shape'] === 'wide' ? 140 : 80) ?></p>
                            <?php endif; ?>
                            <span class="bento-meta"><i class="fa-solid fa-bed"></i> <?= (int)$dest['nearby_hotel_count'] ?> nearby · <?= sanitize($dest['category']) ?></span>
                        </div>
                    </a>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
