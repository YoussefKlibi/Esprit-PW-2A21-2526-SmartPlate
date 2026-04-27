<?php
session_start();
if (isset($_SESSION['user_email'])) {
    require_once __DIR__ . '/../../Controllers/UserController.php';
    $userC = new UserController();
    $userInfo = $userC->getUserByEmail($_SESSION['user_email']);

    // Couper la session immédiatement si l'utilisateur n'existe plus ou est banni
    if (!$userInfo || strtolower(trim($userInfo['statut'] ?? '')) === 'banni') {
        session_unset();
        session_destroy();
        http_response_code(403);
        exit('banned');
    }

    $userC->updateLastActivity($_SESSION['user_email']);
}
