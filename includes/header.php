<?php
// includes/header.php — DigiTour bright global head
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/functions.php';

$page_title = isset($page_title) ? $page_title : 'DigiTour Ghana | Smart Tourism & Accommodation Portal';
if (strpos($page_title, 'DigiTour') === false) {
    $page_title .= ' | DigiTour Ghana';
}
$asset_prefix = '';
if (strpos($_SERVER['PHP_SELF'] ?? '', '/admin/') !== false) {
    $asset_prefix = '../';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="DigiTour Ghana — discover attractions, nearby hotels, reviews, and bookings across Ghana.">
    <title><?= sanitize($page_title) ?></title>
    <link rel="icon" type="image/svg+xml" href="<?= $asset_prefix ?>sources/images/logo-svg/logo-outline-white.svg">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="<?= $asset_prefix ?>assets/css/style.css">
</head>
<body>

<?php $is_home = (basename($_SERVER['PHP_SELF'] ?? '') === 'index.php'); ?>
<!-- Dynamic page backdrop -->
<div class="dt-page-bg" id="dtPageBg" aria-hidden="true">
    <div class="dt-page-bg-layer is-active" style="background-image:url('<?= $asset_prefix ?>sources/images/all_tourist_sites/Kakum%20National%20Park%20Canopy%20Walkway.jpg')"></div>
    <?php if ($is_home): ?>
        <div class="dt-page-bg-layer" style="background-image:url('<?= $asset_prefix ?>sources/images/all_tourist_sites/Cape%20Coast%20Castle.jpg')"></div>
        <div class="dt-page-bg-layer" style="background-image:url('<?= $asset_prefix ?>sources/images/all_tourist_sites/Wli%20Waterfalls.jpg')"></div>
        <div class="dt-page-bg-layer" style="background-image:url('<?= $asset_prefix ?>sources/images/all_tourist_sites/Mole%20National%20Park.jpg')"></div>
    <?php endif; ?>
    <div class="dt-page-bg-wash"></div>
</div>

<div id="scroll-progress"></div>

<div class="top-bar d-none d-md-block">
    <div class="container d-flex justify-content-between align-items-center flex-wrap gap-2">
        <div>
            <i class="fa-solid fa-location-dot me-2" style="color:#FEBD69"></i>
            Discover Ghana's premier attractions &amp; accommodation
            <span class="mx-2 opacity-50">|</span>
            <i class="fa-solid fa-envelope me-1" style="color:#FEBD69"></i> info@digitour.gh
            <span class="mx-2 opacity-50">|</span>
            <a href="<?= DT_ADMIN_WHATSAPP ?>" target="_blank" rel="noopener"><i class="fab fa-whatsapp me-1"></i> WhatsApp <?= DT_ADMIN_PHONE_LOCAL ?></a>
            <span class="mx-2 opacity-50">|</span>
            <a href="<?= DT_ADMIN_TEL ?>"><i class="fa-solid fa-phone me-1"></i> Call</a>
        </div>
        <div>
            <?php if (is_logged_in()): ?>
                <span class="me-3"><i class="fa-solid fa-user me-1" style="color:#FEBD69"></i> Welcome, <strong><?= sanitize($_SESSION['full_name']) ?></strong></span>
                <?php if (is_admin()): ?>
                    <a href="<?= $asset_prefix ?>admin/dashboard.php" class="me-2 badge text-dark" style="background:#FFD814"><i class="fa-solid fa-shield-halved me-1"></i> Admin</a>
                <?php else: ?>
                    <a href="<?= $asset_prefix ?>tourist-dashboard.php" class="me-2"><i class="fa-solid fa-gauge me-1"></i> My Dashboard</a>
                <?php endif; ?>
                <a href="<?= $asset_prefix ?>logout.php" class="text-danger fw-bold"><i class="fa-solid fa-right-from-bracket me-1"></i> Logout</a>
            <?php else: ?>
                <a href="<?= $asset_prefix ?>login.php" class="me-3"><i class="fa-solid fa-right-to-bracket me-1"></i> Login</a>
                <a href="<?= $asset_prefix ?>register.php" class="btn btn-sm btn-digitour-gold py-1 px-3"><i class="fa-solid fa-user-plus me-1"></i> Sign Up</a>
            <?php endif; ?>
        </div>
    </div>
</div>
