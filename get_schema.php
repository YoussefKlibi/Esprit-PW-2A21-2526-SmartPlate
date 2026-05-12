<?php
require_once 'config.php';
$pdo = Config::getConnexion();
$stmt = $pdo->query("SHOW COLUMNS FROM stock");
print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
$stmt = $pdo->query("SHOW COLUMNS FROM boutique");
print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
