<?php
// includes/navbar.php — DigiTour public navigation
$active_page = basename($_SERVER['PHP_SELF'] ?? '');
$asset_prefix = (strpos($_SERVER['PHP_SELF'] ?? '', '/admin/') !== false) ? '../' : '';
?>
<nav class="navbar navbar-expand-lg navbar-digitour sticky-top">
    <div class="container">
        <div class="d-flex align-items-center gap-2 min-w-0">
            <?php if ($active_page !== 'index.php'): ?>
                <button type="button" class="btn btn-nav-back btn-sm rounded-pill" onclick="if(document.referrer && document.referrer.indexOf(window.location.host) !== -1){ history.back(); } else { window.location.href='<?= $asset_prefix ?>index.php'; }" title="Go Back">
                    <i class="fa-solid fa-arrow-left"></i>
                    <span class="d-none d-sm-inline">Back</span>
                </button>
            <?php endif; ?>
            <a class="navbar-brand text-truncate" href="<?= $asset_prefix ?>index.php">
                <i class="fa-solid fa-compass" style="color:var(--amazon-orange)"></i>
                Digi<span>Tour</span>
            </a>
        </div>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarDigiTour" aria-controls="navbarDigiTour" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarDigiTour">
            <ul class="navbar-nav ms-auto mb-2 mb-lg-0 align-items-lg-center gap-lg-1">
                <li class="nav-item">
                    <a class="nav-link <?= ($active_page == 'index.php') ? 'active' : '' ?>" href="<?= $asset_prefix ?>index.php">
                        <i class="fa-solid fa-house me-1"></i> Home
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= in_array($active_page, ['destinations.php', 'destination-detail.php']) ? 'active' : '' ?>" href="<?= $asset_prefix ?>destinations.php">
                        <i class="fa-solid fa-map-location-dot me-1"></i> Destinations
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= ($active_page == 'inquiry.php') ? 'active' : '' ?>" href="<?= $asset_prefix ?>inquiry.php">
                        <i class="fa-solid fa-circle-question me-1"></i> Inquiries
                    </a>
                </li>
                <li class="nav-item">
                    <button type="button" class="btn btn-nav-planner btn-sm rounded-pill my-1 my-lg-0" data-bs-toggle="modal" data-bs-target="#itineraryPlannerModal">
                        <i class="fa-solid fa-wand-magic-sparkles me-1"></i> Trip Planner
                    </button>
                </li>
                <li class="nav-item">
                    <?php require __DIR__ . '/currency-switcher.php'; ?>
                </li>

                <?php if (is_logged_in()): ?>
                    <?php if (is_admin()): ?>
                        <li class="nav-item ms-lg-1">
                            <a class="btn btn-digitour-gold btn-sm px-3" href="<?= $asset_prefix ?>admin/dashboard.php">
                                <i class="fa-solid fa-shield-halved me-1"></i> Admin
                            </a>
                        </li>
                    <?php else: ?>
                        <li class="nav-item ms-lg-1">
                            <a class="btn btn-digitour-primary btn-sm px-3" href="<?= $asset_prefix ?>tourist-dashboard.php">
                                <i class="fa-solid fa-gauge me-1"></i> Dashboard
                            </a>
                        </li>
                    <?php endif; ?>
                    <li class="nav-item d-lg-none">
                        <a class="nav-link text-danger" href="<?= $asset_prefix ?>logout.php">
                            <i class="fa-solid fa-right-from-bracket me-1"></i> Logout
                        </a>
                    </li>
                <?php else: ?>
                    <li class="nav-item ms-lg-1">
                        <a class="btn btn-digitour-outline btn-sm px-3" href="<?= $asset_prefix ?>login.php">
                            <i class="fa-solid fa-right-to-bracket me-1"></i> Login
                        </a>
                    </li>
                    <li class="nav-item ms-lg-1">
                        <a class="btn btn-digitour-gold btn-sm px-3" href="<?= $asset_prefix ?>register.php">
                            <i class="fa-solid fa-user-plus me-1"></i> Register
                        </a>
                    </li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>

<?php require_once __DIR__ . '/itinerary-planner-modal.php'; ?>
