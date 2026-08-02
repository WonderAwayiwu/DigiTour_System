<?php
// includes/footer.php — Bright DigiTour footer + scripts
$asset_prefix = (strpos($_SERVER['PHP_SELF'] ?? '', '/admin/') !== false) ? '../' : '';
?>
<footer class="footer-digitour">
    <div class="container">
        <div class="row g-4">
            <div class="col-lg-4 col-md-6">
                <a class="navbar-brand text-white fw-bold fs-3 mb-3 d-block" href="<?= $asset_prefix ?>index.php" style="font-family:var(--font-display)">
                    <i class="fa-solid fa-compass me-2" style="color:#FF9900"></i> Digi<span style="color:#FEBD69">Tour</span> Ghana
                </a>
                <p>Smart tourism information, accommodation, and booking for Ghana. Discover attractions, compare nearby hotels, and reserve with confidence.</p>
                <div class="d-flex gap-2 mt-3">
                    <a href="#" class="btn btn-outline-light btn-sm rounded-circle" aria-label="Facebook"><i class="fab fa-facebook-f"></i></a>
                    <a href="#" class="btn btn-outline-light btn-sm rounded-circle" aria-label="X"><i class="fab fa-x-twitter"></i></a>
                    <a href="#" class="btn btn-outline-light btn-sm rounded-circle" aria-label="Instagram"><i class="fab fa-instagram"></i></a>
                    <a href="#" class="btn btn-outline-light btn-sm rounded-circle" aria-label="YouTube"><i class="fab fa-youtube"></i></a>
                </div>
            </div>

            <div class="col-lg-2 col-md-6">
                <h5>Quick Links</h5>
                <ul class="list-unstyled">
                    <li class="mb-2"><a href="<?= $asset_prefix ?>index.php"><i class="fa-solid fa-angle-right me-2" style="color:#FF9900"></i> Home</a></li>
                    <li class="mb-2"><a href="<?= $asset_prefix ?>destinations.php"><i class="fa-solid fa-angle-right me-2" style="color:#FF9900"></i> Destinations</a></li>
                    <li class="mb-2"><a href="<?= $asset_prefix ?>inquiry.php"><i class="fa-solid fa-angle-right me-2" style="color:#FF9900"></i> Inquiries</a></li>
                    <li class="mb-2"><a href="<?= $asset_prefix ?>login.php"><i class="fa-solid fa-angle-right me-2" style="color:#FF9900"></i> Login</a></li>
                    <li class="mb-2"><a href="<?= $asset_prefix ?>register.php"><i class="fa-solid fa-angle-right me-2" style="color:#FF9900"></i> Sign Up</a></li>
                </ul>
            </div>

            <div class="col-lg-3 col-md-6">
                <h5>Top Regions</h5>
                <ul class="list-unstyled">
                    <li class="mb-2"><a href="<?= $asset_prefix ?>destinations.php?region=Central+Region"><i class="fa-solid fa-location-dot me-2" style="color:#FEBD69"></i> Central Region</a></li>
                    <li class="mb-2"><a href="<?= $asset_prefix ?>destinations.php?region=Greater+Accra"><i class="fa-solid fa-location-dot me-2" style="color:#FEBD69"></i> Greater Accra</a></li>
                    <li class="mb-2"><a href="<?= $asset_prefix ?>destinations.php?region=Savannah+Region"><i class="fa-solid fa-location-dot me-2" style="color:#FEBD69"></i> Savannah Region</a></li>
                    <li class="mb-2"><a href="<?= $asset_prefix ?>destinations.php?region=Western+Region"><i class="fa-solid fa-location-dot me-2" style="color:#FEBD69"></i> Western Region</a></li>
                    <li class="mb-2"><a href="<?= $asset_prefix ?>destinations.php?region=Eastern+Region"><i class="fa-solid fa-location-dot me-2" style="color:#FEBD69"></i> Eastern Region</a></li>
                </ul>
            </div>

            <div class="col-lg-3 col-md-6">
                <h5>Tourism Support</h5>
                <p><i class="fa-solid fa-map-marker-alt me-2" style="color:#FF9900"></i> Tourism Board Building, Accra, Ghana</p>
                <p>
                    <i class="fa-solid fa-phone me-2" style="color:#FF9900"></i>
                    <a href="<?= DT_ADMIN_TEL ?>"><?= DT_ADMIN_PHONE_LOCAL ?></a>
                </p>
                <p>
                    <i class="fab fa-whatsapp me-2" style="color:#25D366"></i>
                    <a href="<?= DT_ADMIN_WHATSAPP ?>" target="_blank" rel="noopener">WhatsApp Admin</a>
                </p>
                <p><i class="fa-solid fa-envelope me-2" style="color:#FF9900"></i> support@digitour.gh</p>
            </div>
        </div>

        <hr class="my-4" style="border-color:rgba(255,255,255,0.12)">
        <div class="row align-items-center">
            <div class="col-md-7 text-center text-md-start">
                <p class="mb-0 small">&copy; <?= date('Y') ?> DigiTour Ghana. Smart Tourism &amp; Accommodation Platform.</p>
            </div>
            <div class="col-md-5 text-center text-md-end mt-2 mt-md-0">
                <a href="<?= $asset_prefix ?>setup_db.php" class="small me-3" style="color:#FEBD69"><i class="fa-solid fa-database me-1"></i> Setup DB</a>
                <a href="<?= $asset_prefix ?>login.php" class="small" style="color:#94A3B8"><i class="fa-solid fa-lock me-1"></i> Admin Portal</a>
            </div>
        </div>
    </div>
</footer>

<button type="button" class="dt-back-top" id="dtBackTop" aria-label="Back to top">
  <i class="fa-solid fa-arrow-up"></i>
</button>

<!-- Floating Call + WhatsApp -->
<div class="dt-contact-float" id="dtContactFloat">
  <a class="dt-float-btn dt-float-call" href="<?= DT_ADMIN_TEL ?>" title="Call Admin <?= DT_ADMIN_PHONE_LOCAL ?>" aria-label="Call Admin">
    <i class="fa-solid fa-phone"></i>
    <span>Call</span>
  </a>
  <a class="dt-float-btn dt-float-wa" href="<?= DT_ADMIN_WHATSAPP ?>?text=<?= rawurlencode('Hello DigiTour Admin, I need help with a destination / hotel booking.') ?>" target="_blank" rel="noopener" title="WhatsApp <?= DT_ADMIN_PHONE_LOCAL ?>" aria-label="WhatsApp Admin">
    <i class="fab fa-whatsapp"></i>
    <span>WhatsApp</span>
  </a>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="<?= $asset_prefix ?>assets/js/main.js"></script>
<script src="<?= $asset_prefix ?>assets/js/effects.js"></script>
</body>
</html>
