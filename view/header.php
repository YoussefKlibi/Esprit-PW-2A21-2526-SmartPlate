<?php
require_once __DIR__ . '/../config/auth.php';
startSessionIfNeeded();

$pageTitle = $pageTitle ?? 'SmartPlate';
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
    <aside class="sidebar">
        <div class="sidebar-logo">
            <div class="logo-icon">SP</div>
            <h2>SmartPlate</h2>
        </div>

        <nav class="sidebar-nav">
        <a href="Accueil.php" class="nav-item">
            <span class="icon">🏠</span>
            <span>Accueil</span>
        </a>

        <a href="Produits.php" class="nav-item">
            <span class="icon">🛍️</span>
            <span>Produits</span>
        </a>

        <a href="Recettes.php" class="nav-item">
            <span class="icon">🥗</span>
            <span>Recettes</span>
        </a>

        <div class="nav-section" style="margin-top: 1.5rem; margin-bottom: 1.5rem;">
            <span class="nav-section-title">Suivi Nutritionnel</span>
            
            <div class="sub-nav" style="padding-left: 1.2rem; margin-top: 0.5rem; border-left: 2px solid #e2e8f0; margin-left: 1rem;">
                <a href="Journal.php" class="nav-item">
                    <span class="icon">🍽️</span>
                    <span>Journal Alimentaire</span>
                </a>
                
                <a href="Objectif.php" class="nav-item">
                    <span class="icon">🎯</span>
                    <span>Mon Objectif</span>
                </a>
                
                <a href="Progression.php" class="nav-item">
                    <span class="icon">📈</span>
                    <span>Ma Progression</span>
                </a>
            </div>
        </div>

        <a href="Forum.php" class="nav-item">
            <span class="icon">💬</span>
            <span>Forum</span>
        </a>

        <a href="Reclamation.php" class="nav-item">
            <span class="icon">📝</span>
            <span>Réclamation</span>
        </a>
    </nav>

        <div class="sidebar-footer">
            <?php if ($currentUser === null): ?>
                <a href="login.php" class="btn btn-ghost" style="width: 100%; text-align: center;">Se connecter</a>
            <?php else: ?>
                <div class="user-badge">
                    <div class="user-info">
                        <span class="user-name"><?php echo htmlspecialchars((string) ($currentUser['name'] ?? 'Compte'), ENT_QUOTES, 'UTF-8'); ?></span>
                        <a href="logout.php" style="font-size: 0.75rem; color: #8c8c8c; text-decoration: none;">Déconnexion</a>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </aside>

    <div class="dashboard">
