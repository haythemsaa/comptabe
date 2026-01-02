<?php

try {
    $pdo = new PDO('mysql:host=127.0.0.1;dbname=compta', 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Create cache table
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS `cache` (
          `key` varchar(255) NOT NULL,
          `value` mediumtext NOT NULL,
          `expiration` int NOT NULL,
          PRIMARY KEY (`key`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");

    // Create cache_locks table
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS `cache_locks` (
          `key` varchar(255) NOT NULL,
          `owner` varchar(255) NOT NULL,
          `expiration` int NOT NULL,
          PRIMARY KEY (`key`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");

    echo "✓ Tables cache créées avec succès\n";

} catch (PDOException $e) {
    echo "✗ Erreur: " . $e->getMessage() . "\n";
    exit(1);
}
