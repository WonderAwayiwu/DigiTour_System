<?php
// login.php - Dual Tourist & Admin Login Form
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/functions.php';

if (is_logged_in()) {
    if (is_admin()) {
        header("Location: admin/dashboard.php");
    } else {
        header("Location: tourist-dashboard.php");
    }
    exit;
}

$error = '';
$msg = isset($_GET['msg']) ? sanitize($_GET['msg']) : '';
$redirect = isset($_GET['redirect']) ? sanitize($_GET['redirect']) : '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {
    $email = sanitize($_POST['email']);
    $password = $_POST['password'];

    if (empty($email) || empty($password)) {
        $error = "Please enter both email and password.";
    } else {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            // Set session variables
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['full_name'] = $user['full_name'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['role'] = $user['role'];

            if (!empty($redirect)) {
                header("Location: " . $redirect);
            } elseif ($user['role'] === 'admin') {
                header("Location: admin/dashboard.php");
            } else {
                header("Location: tourist-dashboard.php");
            }
            exit;
        } else {
            $error = "Invalid email address or password.";
        }
    }
}

$page_title = "Log In to DigiTour Ghana";
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/navbar.php';
?>

<section class="auth-shell">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-5 col-md-7">
                
                <?php if (!empty($msg)): ?>
                    <div class="alert alert-info alert-dismissible fade show" role="alert">
                        <i class="fa-solid fa-info-circle me-1"></i> <?= $msg ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <div class="auth-card overflow-hidden p-0 reveal">
                    <div class="text-white p-3 p-md-4 text-center" style="background: linear-gradient(135deg, #232F3E 0%, #E47911 120%);">
                        <i class="fa-solid fa-right-to-bracket fa-2x mb-2" style="color:#FFD814"></i>
                        <h3 class="h4 fw-bold mb-0 text-white">Welcome back</h3>
                        <p class="mb-0 small" style="color:rgba(255,255,255,.85)">Sign in to manage bookings or the admin panel</p>
                    </div>

                    <div class="card-body p-3 p-md-4">
                        <?php if (!empty($error)): ?>
                            <div class="alert alert-danger"><i class="fa-solid fa-triangle-exclamation me-1"></i> <?= $error ?></div>
                        <?php endif; ?>

                        <form action="" method="POST">
                            <input type="hidden" name="redirect" value="<?= $redirect ?>">

                            <div class="mb-3">
                                <label class="form-label fw-bold">Email Address</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light"><i class="fa-solid fa-envelope" style="color:var(--amazon-orange)"></i></span>
                                    <input type="email" name="email" class="form-control" placeholder="name@example.com" required value="<?= isset($_POST['email']) ? sanitize($_POST['email']) : '' ?>">
                                </div>
                            </div>

                            <div class="mb-4">
                                <div class="d-flex justify-content-between">
                                    <label class="form-label fw-bold">Password</label>
                                </div>
                                <div class="input-group">
                                    <span class="input-group-text bg-light"><i class="fa-solid fa-lock" style="color:var(--amazon-orange)"></i></span>
                                    <input type="password" name="password" class="form-control" placeholder="••••••••" required>
                                </div>
                            </div>

                            <div class="d-grid mb-3">
                                <button type="submit" name="login" class="btn btn-digitour-gold fw-bold">
                                    <i class="fa-solid fa-right-to-bracket me-2"></i> Log In
                                </button>
                            </div>

                            <div class="text-center mt-3 border-top pt-3">
                                <p class="text-muted mb-0 small">Don't have an account? <a href="register.php" class="fw-bold" style="color:var(--amazon-orange-deep)">Create account</a></p>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- <div class="text-center mt-4 text-muted small">
                    <p class="mb-1"><strong>Demo Admin:</strong> <code>admin@digitour.gh</code> | Pass: <code>admin123</code></p>
                    <p class="mb-0"><strong>Demo Tourist:</strong> <code>kwame@example.com</code> | Pass: <code>admin123</code></p>
                </div> -->

            </div>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
