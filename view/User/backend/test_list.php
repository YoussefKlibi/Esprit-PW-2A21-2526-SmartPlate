<?php
require_once 'c:\xampp\htdocs\template\Controllers\UserController.php';
$userC = new UserController();
$users = $userC->listeUsers();
foreach ($users as $user) {
    $statut_db = strtolower(trim($user['statut'] ?? 'inactif'));
    echo "ID " . $user['id'] . " = " . $statut_db . "\n";
}
