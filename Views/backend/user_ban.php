<?php
session_start();
if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
    header("Location: ../frontend/login.php");
    exit();
}

require_once __DIR__ . '/../../Controllers/UserController.php';

if (isset($_GET['id']) && isset($_GET['action'])) {
    $id = intval($_GET['id']);
    $action = $_GET['action'];
    
    $userC = new UserController();
    
    if ($action === 'ban' || $action === 'unban') {
        // Assurer qu'on ne puisse pas bannir le compte admin
        $targetUser = $userC->getUserById($id);
        if ($targetUser && $targetUser['email'] !== 'ilyesgaied32@gmail.com') {
            $statut = ($action === 'ban') ? 'banni' : 'actif';
            $userC->updateStatus($id, $statut);
        }
    }
}

header("Location: user_list.php");
exit();
?>
