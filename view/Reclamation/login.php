<?php
require_once __DIR__ . '/../config/auth.php';

startSessionIfNeeded();

if (isset($_GET['role'])) {
    if ($_GET['role'] === 'client') {
        loginByCredentials('client@smartplate.test', 'client123');
        header('Location: list_reclamation.php');
        exit;
    } elseif ($_GET['role'] === 'admin') {
        loginByCredentials('admin@smartplate.test', 'admin123');
        header('Location: back/admin_dashboard.php');
        exit;
    }
}

$currentUser = getCurrentUser();
if ($currentUser !== null) {
    if (($currentUser['role'] ?? '') === 'admin') {
        header('Location: back/admin_dashboard.php');
    } else {
        header('Location: list_reclamation.php');
    }
    exit;
}

// Si aucun utilisateur n'est connecté et aucun rôle n'est spécifié, on redirige vers l'accueil
header('Location: index.php');
exit;
