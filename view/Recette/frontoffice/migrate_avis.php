<?php
require_once __DIR__ . '/../../../config.php';
$pdo = Config::getConnexion();
try {
    $pdo->exec("ALTER TABLE avis ADD COLUMN id_user INT DEFAULT NULL");
    echo "Column id_user added successfully\n";
} catch (Exception $e) {
    echo "Error or column already exists: " . $e->getMessage() . "\n";
}

$stmt = $pdo->query("DESCRIBE avis");
print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
