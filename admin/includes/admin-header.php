<?php
// admin/includes/admin-header.php — DigiTour Admin Layout Shell
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../includes/functions.php';

require_admin();

$admin_active_page = basename($_SERVER['PHP_SELF']);
$page_heading = isset($admin_title) ? $admin_title : 'Dashboard';
$admin_title = $page_heading . ' | DigiTour Admin';

$admin_nav = [
    ['file' => 'dashboard.php', 'icon' => 'fa-gauge', 'label' => 'Dashboard'],
    ['file' => 'manage-destinations.php', 'icon' => 'fa-map-location-dot', 'label' => 'Destinations'],
    ['file' => 'manage-hotels.php', 'icon' => 'fa-hotel', 'label' => 'Hotels'],
    ['file' => 'manage-bookings.php', 'icon' => 'fa-calendar-check', 'label' => 'Bookings'],
    ['file' => 'manage-reviews.php', 'icon' => 'fa-comments', 'label' => 'Reviews'],
    ['file' => 'manage-users.php', 'icon' => 'fa-users', 'label' => 'Users'],
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= sanitize($admin_title) ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Syne:wght@600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body class="admin-body">

<!-- Mobile offcanvas sidebar -->
<div class="offcanvas offcanvas-start admin-offcanvas" tabindex="-1" id="adminMobileSidebar" aria-labelledby="adminMobileSidebarLabel">
    <div class="offcanvas-header">
        <h5 class="offcanvas-title" id="adminMobileSidebarLabel">
            <i class="fa-solid fa-compass me-2 text-warning"></i>DigiTour Admin
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body d-flex flex-column p-0">
        <nav class="admin-nav flex-grow-1 px-3 py-2">
            <?php foreach ($admin_nav as $item): ?>
                <a href="<?= $item['file'] ?>" class="admin-nav-link <?= ($admin_active_page === $item['file']) ? 'active' : '' ?>">
                    <i class="fa-solid <?= $item['icon'] ?>"></i>
                    <span><?= $item['label'] ?></span>
                </a>
            <?php endforeach; ?>
        </nav>
        <div class="admin-sidebar-foot px-3 pb-3">
            <a href="../index.php" class="btn btn-admin-outline w-100 mb-2"><i class="fa-solid fa-globe me-1"></i> View Site</a>
            <a href="../logout.php" class="btn btn-admin-danger w-100"><i class="fa-solid fa-right-from-bracket me-1"></i> Logout</a>
        </div>
    </div>
</div>

<!-- Fixed desktop sidebar -->
<aside class="admin-sidebar-desktop d-none d-lg-flex flex-column" aria-label="Admin navigation">
    <a href="dashboard.php" class="admin-brand">
        <i class="fa-solid fa-compass text-warning"></i>
        <span>Digi<span class="admin-brand-accent">Tour</span></span>
    </a>
    <nav class="admin-nav flex-grow-1">
        <?php foreach ($admin_nav as $item): ?>
            <a href="<?= $item['file'] ?>" class="admin-nav-link <?= ($admin_active_page === $item['file']) ? 'active' : '' ?>">
                <i class="fa-solid <?= $item['icon'] ?>"></i>
                <span><?= $item['label'] ?></span>
            </a>
        <?php endforeach; ?>
    </nav>
    <div class="admin-sidebar-foot">
        <a href="../index.php" class="btn btn-admin-outline w-100 mb-2"><i class="fa-solid fa-globe me-1"></i> View Site</a>
        <a href="../logout.php" class="btn btn-admin-danger w-100"><i class="fa-solid fa-right-from-bracket me-1"></i> Logout</a>
    </div>
</aside>

<!-- Main shell (header stays sticky while content scrolls) -->
<div class="admin-shell">
    <header class="admin-top-header">
        <div class="admin-top-left">
            <button type="button" class="admin-menu-btn d-lg-none" data-bs-toggle="offcanvas" data-bs-target="#adminMobileSidebar" aria-label="Open menu">
                <i class="fa-solid fa-bars"></i>
            </button>
            <button type="button" class="admin-back-btn" onclick="if(document.referrer && document.referrer.indexOf(window.location.host) !== -1){ history.back(); } else { window.location.href='dashboard.php'; }" title="Go Back">
                <i class="fa-solid fa-arrow-left"></i>
                <span class="d-none d-sm-inline">Back</span>
            </button>
            <h1 class="admin-page-title"><?= sanitize($page_heading) ?></h1>
        </div>
        <div class="admin-top-right">
            <span class="admin-user-badge">
                <i class="fa-solid fa-shield-halved"></i>
                <span class="d-none d-sm-inline">Admin:</span>
                <strong><?= sanitize($_SESSION['full_name']) ?></strong>
            </span>
        </div>
    </header>

    <main class="admin-main-content">
