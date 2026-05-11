<?php
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../../controller/User/UserController.php';
startSessionIfNeeded();

$pageTitle = $pageTitle ?? 'SmartPlate';
$currentPage = $currentPage ?? 'index';
$currentUser = getCurrentUser();
$isIndexPage = $currentPage === 'index';
$isAddReclamationPage = $currentPage === 'reclamation-add';
$isListReclamationPage = in_array($currentPage, ['reclamation-list', 'reclamation-edit'], true);
$isLoginPage = $currentPage === 'login';

$firstName = trim((string) ($currentUser['prenom'] ?? ''));
$lastName = trim((string) ($currentUser['nom'] ?? ''));
$fullName = trim($firstName . ' ' . $lastName);
if ($fullName === '') {
    $fullName = (string) ($currentUser['email'] ?? 'Utilisateur');
}

$avatarSrc = 'https://ui-avatars.com/api/?name=' . urlencode($fullName) . '&background=d4f283&color=1a1a1a&rounded=true';
if (isset($currentUser['id'])) {
    $baseDir = __DIR__ . '/../User/uploads/profile_pictures';
    $baseWeb = '../User/uploads/profile_pictures';
    $extensions = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
    foreach ($extensions as $ext) {
        $filePath = $baseDir . '/user_' . $currentUser['id'] . '.' . $ext;
        if (is_file($filePath)) {
            $avatarSrc = $baseWeb . '/user_' . $currentUser['id'] . '.' . $ext . '?v=' . filemtime($filePath);
            break;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($pageTitle, ENT_QUOTES, 'UTF-8'); ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&family=Sora:wght@600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="Template.css">
</head>
<body>
    <?php include __DIR__ . '/../front_sidebar.php'; ?>

    <div class="dashboard">
