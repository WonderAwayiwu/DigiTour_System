<?php
require_once __DIR__ . '/../config/db.php';

$password = 'password123';
$hash = password_hash($password, PASSWORD_BCRYPT);

echo "Password: " . $password . PHP_EOL;
echo "Hash: " . $hash . PHP_EOL;

if ($pdo) {
    $stmt = $pdo->prepare("UPDATE users SET password = ?");
    $stmt->execute([$hash]);
    echo "SUCCESS: Updated " . $stmt->rowCount() . " users in MySQL database 'digitour_db'." . PHP_EOL;
} else {
    echo "ERROR: Database connection not available." . PHP_EOL;
}
