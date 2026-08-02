<?php
require_once __DIR__ . '/../config/db.php';

$password = 'admin123';
$hash = password_hash($password, PASSWORD_DEFAULT);

echo "New Hash: " . $hash . "\n";
echo "Verify: " . (password_verify('admin123', $hash) ? 'YES' : 'NO') . "\n";

if ($pdo) {
    $stmt = $pdo->prepare("UPDATE users SET password = ?");
    $stmt->execute([$hash]);
    echo "Updated " . $stmt->rowCount() . " users in database!\n";
} else {
    echo "No DB connection.\n";
}
