<?php
session_start();
if (isset($_SESSION['user_email'])) {
    require_once __DIR__ . '/../../Controllers/UserController.php';
    $userC = new UserController();
    $db = config::getConnexion();
    $stmt = $db->prepare("UPDATE users SET last_activity = NULL, statut = 'inactif' WHERE email = :email AND statut != 'banni'");
    $stmt->execute(['email' => $_SESSION['user_email']]);
}
session_destroy();
header("Location: login.php");
exit();
?>
