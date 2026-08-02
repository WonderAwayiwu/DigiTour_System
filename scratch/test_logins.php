<?php
require_once __DIR__ . '/../config/db.php';

$stmt = $pdo->query('SELECT email, role, password FROM users');
while ($u = $stmt->fetch()) {
    $ok = password_verify('password123', $u['password']);
    echo $u['email'] . " (" . $u['role'] . ") -> password_verify('password123'): " . ($ok ? 'VALID' : 'INVALID') . "\n";
}
