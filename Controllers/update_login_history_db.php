<?php
require_once __DIR__ . '/config.php';

$db = config::getConnexion();

$sql = "CREATE TABLE IF NOT EXISTS login_history (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    ip_address VARCHAR(45) NOT NULL,
    city VARCHAR(100) DEFAULT NULL,
    country VARCHAR(100) DEFAULT NULL,
    latitude DECIMAL(10, 8) DEFAULT NULL,
    longitude DECIMAL(11, 8) DEFAULT NULL,
    login_time DATETIME NOT NULL,
    device_info VARCHAR(255) DEFAULT NULL,
    status VARCHAR(20) DEFAULT 'Success',
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
)";

try {
    $db->exec($sql);
    echo "Table login_history créée avec succès.";
} catch (PDOException $e) {
    echo "Erreur lors de la création de la table : " . $e->getMessage();
}
