<?php
require_once __DIR__ . '/../config/db.php';

if ($pdo) {
    $pdo->exec("CREATE TABLE IF NOT EXISTS `destination_images` (
      `id` INT AUTO_INCREMENT PRIMARY KEY,
      `destination_id` INT NOT NULL,
      `image_path` VARCHAR(255) NOT NULL,
      `caption` VARCHAR(255) NULL,
      `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
      FOREIGN KEY (`destination_id`) REFERENCES `destinations`(`id`) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

    $pdo->exec("CREATE TABLE IF NOT EXISTS `hotel_images` (
      `id` INT AUTO_INCREMENT PRIMARY KEY,
      `hotel_id` INT NOT NULL,
      `image_path` VARCHAR(255) NOT NULL,
      `caption` VARCHAR(255) NULL,
      `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
      FOREIGN KEY (`hotel_id`) REFERENCES `hotels`(`id`) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

    echo "TABLES_CREATED_SUCCESSFULLY\n";
} else {
    echo "NO_DB_CONNECTION\n";
}
