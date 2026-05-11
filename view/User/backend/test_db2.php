<?php
require_once 'c:\xampp\htdocs\template\Controllers\UserController.php';
$db = config::getConnexion();
$stmt = $db->query('DESCRIBE users');
print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
