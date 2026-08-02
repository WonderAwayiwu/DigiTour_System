<?php
// config/db.php - DigiTour Database Connection Config

$host = 'localhost';
$username = 'root';
$password = ''; // Default WAMP MySQL password is empty
$dbname = 'digitour_db';

try {
    // Direct PDO connection to digitour_db for fast response
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);
} catch (PDOException $e) {
    // If database does not exist yet (error 1049), set $pdo = null so setup message can display
    if ($e->getCode() == 1049 || strpos($e->getMessage(), 'Unknown database') !== false) {
        $pdo = null;
    } else {
        die("Database Connection Error: " . $e->getMessage() . "<br>Please ensure WAMP MySQL service is running.");
    }
}
?>
