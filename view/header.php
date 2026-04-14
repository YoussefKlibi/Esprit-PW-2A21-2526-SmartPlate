<?php
require_once __DIR__ . '/../config/auth.php';
startSessionIfNeeded();

$pageTitle = $pageTitle ?? 'SmartPlate - Front Office';
$currentPage = $currentPage ?? 'index';
$currentUser = getCurrentUser();
$isIndexPage = $currentPage === 'index';
$isAddReclamationPage = $currentPage === 'reclamation-add';
$isListReclamationPage = in_array($currentPage, ['reclamation-list', 'reclamation-edit'], true);
$isLoginPage = $currentPage === 'login';
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
    <header class="site-header">
        <div class="logo-group">
            <div class="logo-mark" aria-hidden="true">SP</div>
            <div class="logo-text">
                <strong>SmartPlate</strong>
                <span>front office</span>
            </div>
        </div>

        <nav class="site-nav" aria-label="Navigation principale">
            <a href="index.php#home" class="<?php echo $isIndexPage ? 'active' : ''; ?>">Accueil</a>
            <a href="index.php#services" class="<?php echo $isIndexPage ? 'active' : ''; ?>">Services</a>
            <a href="index.php#plans" class="<?php echo $isIndexPage ? 'active' : ''; ?>">Offres</a>
            <a href="Add_reclamation.php" class="<?php echo $isAddReclamationPage ? 'active' : ''; ?>">Réclamation</a>
            <a href="list_reclamation.php" class="<?php echo $isListReclamationPage ? 'active' : ''; ?>">Mes réclamations</a>
        </nav>

        <?php if ($currentUser === null): ?>
            <a href="login.php" class="btn btn-ghost <?php echo $isLoginPage ? 'active' : ''; ?>">Se connecter</a>
        <?php else: ?>
            <a href="logout.php" class="btn btn-ghost">Deconnexion (<?php echo htmlspecialchars((string) ($currentUser['name'] ?? 'Compte'), ENT_QUOTES, 'UTF-8'); ?>)</a>
        <?php endif; ?>
    </header>
