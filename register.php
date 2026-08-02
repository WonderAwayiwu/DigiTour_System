<?php
// register.php - New Tourist Registration Form
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/functions.php';

if (is_logged_in()) {
    header("Location: tourist-dashboard.php");
    exit;
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['register'])) {
    $full_name = sanitize($_POST['full_name']);
    $email = sanitize($_POST['email']);
    $phone = sanitize($_POST['phone']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    if (empty($full_name) || empty($email) || empty($password)) {
        $error = "Please fill in all required fields.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Please enter a valid email address.";
    } elseif ($password !== $confirm_password) {
        $error = "Passwords do not match!";
    } elseif (strlen($password) < 6) {
        $error = "Password must be at least 6 characters long.";
    } else {
        // Check duplicate email
        $chk = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $chk->execute([$email]);
        if ($chk->fetch()) {
            $error = "An account with this email already exists.";
        } else {
            // Hash Password
            $hashed_pass = password_hash($password, PASSWORD_BCRYPT);
            $ins = $pdo->prepare("INSERT INTO users (full_name, email, phone, password, role) VALUES (?, ?, ?, ?, 'tourist')");
            if ($ins->execute([$full_name, $email, $phone, $hashed_pass])) {
                $user_id = $pdo->lastInsertId();
                $_SESSION['user_id'] = $user_id;
                $_SESSION['full_name'] = $full_name;
                $_SESSION['email'] = $email;
                $_SESSION['role'] = 'tourist';

                header("Location: tourist-dashboard.php?msg=" . urlencode("Welcome to DigiTour! Account created successfully."));
                exit;
            } else {
                $error = "Failed to register account. Please try again.";
            }
        }
    }
}

$page_title = "Register New Tourist Account";
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/navbar.php';
?>

<section class="auth-shell">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-6 col-md-8">
                <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
                    <div class="text-white p-3 p-md-4 text-center" style="background: linear-gradient(135deg, #232F3E 0%, #E47911 120%);">
                        <i class="fa-solid fa-user-plus fa-2x text-warning mb-2"></i>
                        <h3 class="h4 fw-bold mb-0">Create your account</h3>
                        <p class="text-light mb-0 small">Book hotels and share reviews on DigiTour</p>
                    </div>

                    <div class="card-body p-3 p-md-4">
                        <?php if (!empty($error)): ?>
                            <div class="alert alert-danger"><i class="fa-solid fa-triangle-exclamation me-1"></i> <?= $error ?></div>
                        <?php endif; ?>

                        <form action="" method="POST">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Full Name <span class="text-danger">*</span></label>
                                <input type="text" name="full_name" class="form-control" placeholder="e.g. Kwame Mensah" required value="<?= isset($_POST['full_name']) ? sanitize($_POST['full_name']) : '' ?>">
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-bold">Email Address <span class="text-danger">*</span></label>
                                <input type="email" name="email" class="form-control" placeholder="name@example.com" required value="<?= isset($_POST['email']) ? sanitize($_POST['email']) : '' ?>">
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-bold">Phone Number</label>
                                <input type="text" name="phone" class="form-control" placeholder="+233 24 000 0000" value="<?= isset($_POST['phone']) ? sanitize($_POST['phone']) : '' ?>">
                            </div>

                            <div class="row g-3 mb-4">
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">Password <span class="text-danger">*</span></label>
                                    <input type="password" name="password" class="form-control" placeholder="••••••••" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">Confirm Password <span class="text-danger">*</span></label>
                                    <input type="password" name="confirm_password" class="form-control" placeholder="••••••••" required>
                                </div>
                            </div>

                            <div class="d-grid mb-3">
                                <button type="submit" name="register" class="btn btn-digitour-primary fw-bold">
                                    <i class="fa-solid fa-circle-check me-2"></i> Create Account
                                </button>
                            </div>

                            <div class="text-center mt-3 border-top pt-3">
                                <p class="text-muted mb-0 small">Already have an account? <a href="login.php" class="fw-bold" style="color:var(--amazon-teal)">Log in</a></p>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
