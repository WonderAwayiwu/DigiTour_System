<?php
// setup_db.php - One-click Database & Directory Initializer

$host = 'localhost';
$username = 'root';
$password = ''; // WAMP default

$message = '';
$success = false;

try {
    // 1. Connect to MySQL
    $pdo = new PDO("mysql:host=$host;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // 2. Read SQL File
    $sql_file = __DIR__ . '/sql/digitour_db.sql';
    if (!file_exists($sql_file)) {
        throw new Exception("SQL File not found at: $sql_file");
    }

    $sql = file_get_contents($sql_file);

    // 3. Execute queries
    $pdo->exec($sql);

    // 3b. Ensure indexes exist on digitour_db
    $pdo->exec("USE `digitour_db`");
    $indexes = [
        "CREATE INDEX idx_hotels_dest_id ON hotels(destination_id)",
        "CREATE INDEX idx_dest_featured_id ON destinations(is_featured, id)",
        "CREATE INDEX idx_dest_region ON destinations(region)",
        "CREATE INDEX idx_dest_category ON destinations(category)",
        "CREATE INDEX idx_reviews_status ON reviews(status)"
    ];
    foreach ($indexes as $idx_sql) {
        try {
            $pdo->exec($idx_sql);
        } catch (PDOException $ex) {
            // Index may already exist, ignore error
        }
    }

    // 4. Ensure upload directory exists
    $upload_dir = __DIR__ . '/assets/uploads';
    if (!file_exists($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }

    $success = true;
    $message = "Database <strong>digitour_db</strong> and seed data created successfully!";
} catch (Exception $e) {
    $success = false;
    $message = "Database Setup Error: " . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DigiTour - System Database Setup</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { background: #f4f7f6; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; display: flex; align-items: center; justify-content: center; min-height: 100vh; }
        .setup-card { max-width: 600px; width: 100%; border-radius: 15px; border: none; box-shadow: 0 10px 30px rgba(0,0,0,0.1); overflow: hidden; }
        .setup-header { background: linear-gradient(135deg, #0F5A47 0%, #1A2332 100%); color: #E5A93C; padding: 30px; text-align: center; }
    </style>
</head>
<body>
    <div class="card setup-card">
        <div class="setup-header">
            <h2><i class="fa-solid fa-compass me-2"></i> DigiTour Ghana</h2>
            <p class="text-light mb-0">System Database & Initializer</p>
        </div>
        <div class="card-body p-4 text-center">
            <?php if ($success): ?>
                <div class="alert alert-success py-3 mb-4">
                    <i class="fa-solid fa-circle-check fa-2x d-block mb-2"></i>
                    <?= $message ?>
                </div>
                <h5 class="fw-bold mb-3">Pre-Configured Accounts:</h5>
                <div class="text-start bg-light p-3 rounded mb-4">
                    <p class="mb-1"><strong>Administrator Login:</strong> <code>admin@digitour.gh</code> | Password: <code>password123</code></p>
                    <p class="mb-0"><strong>Tourist Login:</strong> <code>kwame@example.com</code> | Password: <code>password123</code></p>
                </div>
                <div class="d-grid gap-2 d-md-flex justify-content-md-center">
                    <a href="index.php" class="btn btn-success btn-lg px-4 me-md-2"><i class="fa-solid fa-globe me-2"></i> Launch Main Website</a>
                    <a href="login.php" class="btn btn-outline-dark btn-lg px-4"><i class="fa-solid fa-right-to-bracket me-2"></i> Login Page</a>
                </div>
            <?php else: ?>
                <div class="alert alert-danger py-3 mb-4">
                    <i class="fa-solid fa-triangle-exclamation fa-2x d-block mb-2"></i>
                    <?= $message ?>
                </div>
                <p>Please make sure WampServer MySQL service is running, then refresh this page.</p>
                <a href="setup_db.php" class="btn btn-primary"><i class="fa-solid fa-rotate-right me-2"></i> Retry Database Creation</a>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
