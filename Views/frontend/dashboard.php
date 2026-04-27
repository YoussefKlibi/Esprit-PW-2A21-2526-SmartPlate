<?php
session_start();
require_once __DIR__ . '/../../Controllers/UserController.php';

if (!isset($_SESSION['user_email'])) {
    header("Location: login.php");
    exit();
}

$userC = new UserController();
$userInfo = $userC->getUserByEmail($_SESSION['user_email']);

if (!$userInfo || strtolower(trim($userInfo['statut'] ?? '')) === 'banni') {
    session_unset();
    session_destroy();
    header("Location: login.php?banned=1");
    exit();
}

// Mettre à jour l'activité
$userC->updateLastActivity($_SESSION['user_email']);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SmartPlate - Tableau de bord</title>
    <meta name="description" content="SmartPlate - Votre espace personnel pour gérer vos objectifs nutritionnels.">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../css/Template_FrontOffice.css">
</head>
<body>

    <!-- SIDEBAR -->
    <aside class="sidebar">
        <div class="sidebar-logo">
            <div class="logo-icon" style="padding: 0; overflow: hidden;">
                <img src="../xpdf/logo.jpg" alt="SmartPlate Logo" style="width:100%; height:100%; object-fit:cover; border-radius:12px; display:block;">
            </div>
            <h2>SmartPlate</h2>
        </div>

        <nav class="sidebar-nav">
            <span class="nav-section-title">Navigation</span>
            <a href="dashboard.php" class="nav-item active" id="nav-dashboard">
                <span class="icon">🏠</span>
                <span>Tableau de bord</span>
            </a>
            <a href="#" class="nav-item" id="nav-journal">
                <span class="icon">📖</span>
                <span>Journal</span>
            </a>
            <a href="#" class="nav-item" id="nav-objectifs">
                <span class="icon">🎯</span>
                <span>Objectifs</span>
            </a>
            <a href="#" class="nav-item" id="nav-progression">
                <span class="icon">📊</span>
                <span>Progression</span>
            </a>

            <span class="nav-section-title" style="margin-top: 20px;">Mon Compte</span>
            <a href="user_profile.php" class="nav-item" id="nav-profil">
                <span class="icon">👤</span>
                <span>Mon Profil</span>
            </a>
            <a href="logout.php" class="nav-item" id="nav-logout" onclick="return confirm('Voulez-vous vraiment vous déconnecter ?');">
                <span class="icon">🚪</span>
                <span>Déconnexion</span>
            </a>
        </nav>

        <div class="sidebar-footer">
            <div class="user-badge">
                <img src="https://ui-avatars.com/api/?name=<?= urlencode($userInfo['prenom'] . ' ' . $userInfo['nom']) ?>&background=d4f283&color=1a1a1a&rounded=true" alt="Avatar">
                <div class="user-info">
                    <span class="user-name"><?= htmlspecialchars($userInfo['prenom'] . ' ' . $userInfo['nom']) ?></span>
                    <span class="user-status">Utilisateur</span>
                </div>
            </div>
        </div>
    </aside>

    <!-- CONTENU PRINCIPAL -->
    <div class="dashboard">

        <!-- HEADER -->
        <header class="dashboard-header">
            <h1>Bienvenue <?= htmlspecialchars($userInfo['prenom']) ?> 👋</h1>
            <div class="journal-actions-right">
                <a href="user_profile.php" class="btn-icon">👤 Profil</a>
                <a href="logout.php" class="btn-icon danger" onclick="return confirm('Voulez-vous vraiment vous déconnecter ?');">🚪 Déconnexion</a>
            </div>
        </header>

        <p style="color: var(--text-gray); margin-bottom: 2rem;">Voici votre espace personnel. Accédez à toutes les fonctionnalités de SmartPlate.</p>

        <!-- DASHBOARD GRID -->
        <div class="dashboard-grid">
            <!-- Card: Journal Alimentaire -->
            <a href="#" class="card-stat" style="text-decoration: none; color: inherit;">
                <div class="stat-icon shake">📖</div>
                <div>
                    <h3 style="font-size: 1.1rem; margin-bottom: 0.2rem;">Journal</h3>
                    <p style="font-size: 0.85rem; color: var(--text-gray);">Suivre vos repas quotidiens.</p>
                </div>
            </a>

            <!-- Card: Objectifs -->
            <a href="#" class="card-stat" style="text-decoration: none; color: inherit;">
                <div class="stat-icon shake" style="background: #e8f8f5; color: #20c997;">🎯</div>
                <div>
                    <h3 style="font-size: 1.1rem; margin-bottom: 0.2rem;">Objectifs</h3>
                    <p style="font-size: 0.85rem; color: var(--text-gray);">Définir vos objectifs nutritionnels.</p>
                </div>
            </a>

            <!-- Card: Progression -->
            <a href="#" class="card-stat" style="text-decoration: none; color: inherit;">
                <div class="stat-icon shake" style="background: #e3f2fd; color: #3498db;">📊</div>
                <div>
                    <h3 style="font-size: 1.1rem; margin-bottom: 0.2rem;">Progression</h3>
                    <p style="font-size: 0.85rem; color: var(--text-gray);">Visualiser vos statistiques.</p>
                </div>
            </a>

            <!-- Card: Profil -->
            <a href="user_profile.php" class="card-stat" style="text-decoration: none; color: inherit;">
                <div class="stat-icon shake" style="background: #f3e5f5; color: #9b59b6;">👤</div>
                <div>
                    <h3 style="font-size: 1.1rem; margin-bottom: 0.2rem;">Profil</h3>
                    <p style="font-size: 0.85rem; color: var(--text-gray);">Modifier vos informations.</p>
                </div>
            </a>

            <!-- Card: Recettes -->
            <a href="#" class="card-stat" style="text-decoration: none; color: inherit;">
                <div class="stat-icon shake" style="background: #feebeb; color: #e74c3c;">🍽️</div>
                <div>
                    <h3 style="font-size: 1.1rem; margin-bottom: 0.2rem;">Recettes</h3>
                    <p style="font-size: 0.85rem; color: var(--text-gray);">Découvrir des idées de repas.</p>
                </div>
            </a>
        </div>
    </div>

    <!-- Intégration du Chatbot Assistant -->
    <?php include __DIR__ . '/chatbot.php'; ?>

    <script>
        setInterval(() => {
            fetch('ping.php')
                .then((response) => {
                    if (response.status === 403) {
                        window.location.href = 'login.php?banned=1';
                    }
                })
                .catch(e => console.log(e));
        }, 60000);
    </script>
</body>
</html>
