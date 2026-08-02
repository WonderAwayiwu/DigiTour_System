<?php
// inquiry.php - General Tourist Inquiry Form
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/functions.php';

$msg = '';
$site_param = isset($_GET['site']) ? sanitize($_GET['site']) : '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_inquiry'])) {
    $name = sanitize($_POST['name']);
    $email = sanitize($_POST['email']);
    $subject = sanitize($_POST['subject']);
    $message = sanitize($_POST['message']);
    $user_id = is_logged_in() ? $_SESSION['user_id'] : null;

    if (!empty($name) && !empty($email) && !empty($message)) {
        $ins = $pdo->prepare("INSERT INTO inquiries (user_id, name, email, subject, message, status) VALUES (?, ?, ?, ?, ?, 'New')");
        $ins->execute([$user_id, $name, $email, $subject, $message]);
        $msg = '<div class="alert alert-success"><i class="fa-solid fa-circle-check me-1"></i> Thank you! Your inquiry has been sent to the DigiTour Tourism Board. We will contact you via email shortly.</div>';
    } else {
        $msg = '<div class="alert alert-danger">Please fill in all required fields.</div>';
    }
}

$page_title = "General Tourist Inquiries | DigiTour Ghana";
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/navbar.php';
?>

<section class="py-5 bg-light min-vh-100">
    <div class="container py-4">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                
                <div class="card border-0 shadow-lg rounded-4 overflow-hidden">
                    <div class="text-white p-4 text-center" style="background: linear-gradient(135deg, #232F3E 0%, #E47911 120%);">
                        <i class="fa-solid fa-circle-question fa-2x text-warning mb-2"></i>
                        <h2 class="h3 fw-bold mb-1">Tourist Inquiry</h2>
                        <p class="text-light mb-0 small">Questions about attractions, hotel stays, or travel packages? Send us a message.</p>
                    </div>

                    <div class="card-body p-4 p-md-5">
                        <?= $msg ?>

                        <form action="" method="POST">
                            <div class="row g-3 mb-3">
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">Your Name <span class="text-danger">*</span></label>
                                    <input type="text" name="name" class="form-control" required value="<?= is_logged_in() ? sanitize($_SESSION['full_name']) : '' ?>">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">Email Address <span class="text-danger">*</span></label>
                                    <input type="email" name="email" class="form-control" required value="<?= is_logged_in() ? sanitize($_SESSION['email']) : '' ?>">
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-bold">Subject</label>
                                <input type="text" name="subject" class="form-control" placeholder="e.g. Opening hours for Kakum Canopy Walk" value="<?= !empty($site_param) ? 'Inquiry regarding ' . $site_param : '' ?>" required>
                            </div>

                            <div class="mb-4">
                                <label class="form-label fw-bold">Your Message <span class="text-danger">*</span></label>
                                <textarea name="message" class="form-control" rows="5" placeholder="Type your questions or special requests here..." required></textarea>
                            </div>

                            <div class="d-grid">
                                <button type="submit" name="submit_inquiry" class="btn btn-digitour-primary fw-bold">
                                    <i class="fa-solid fa-paper-plane me-2"></i> Send Inquiry
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

            </div>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
