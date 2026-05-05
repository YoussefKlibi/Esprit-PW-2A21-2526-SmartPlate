<?php
session_start();
if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
    header("Location: ../frontend/login.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SmartPlate - Admin Accueil</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../css/Template_BackOffice.css">
</head>
<body>

    <aside class="admin-sidebar">
        <div class="sidebar-header">
            <span>
                <img src="../xpdf/logo.jpg" alt="SmartPlate Logo" style="width:30px; height:30px; object-fit:cover; border-radius:8px; display:block;">
            </span> SmartPlate
        </div>
        <div class="sidebar-menu">
            <div class="menu-category">Menu Principal</div>
            <a href="#" class="menu-item">📊 Vue d'ensemble</a>
            <a href="user_list.php" class="menu-item">👥 Utilisateurs & Logins</a>
            <a href="#" class="menu-item">🎯 Modération Objectifs</a>
            <a href="#" class="menu-item">🍽️ Journaux Utilisateurs</a>

            <div class="menu-category" style="margin-top: 20px;">Système</div>
            <a href="#" class="menu-item">⚠️ Anomalies (3)</a>
            <a href="#" class="menu-item" style="margin-top: auto;">⚙️ Paramètres du site</a>
            <a href="../frontend/logout.php" class="menu-item" style="color: #ff6b6b;" onclick="return confirm('Voulez-vous vraiment vous déconnecter ?');">🚪 Déconnexion</a>
        </div>
    </aside>

    <main class="main-content">
        <header class="topbar">
            <div class="search-bar"></div>
            <div class="admin-profile">
                <span>Ilyes (Admin)</span>
                <img src="https://ui-avatars.com/api/?name=Ilyes+Gaied&background=20c997&color=fff" alt="Profile">
            </div>
        </header>

        <div class="dashboard-container">
            <div class="page-header">
                <div>
                    <h1>Dashboard Overview</h1>
                    <p>Panneau d'administration principal de SmartPlate.</p>
                </div>
            </div>

            <div class="content-grid" style="display: block;">
                <div class="card" style="padding: 3rem; text-align: center; background: white; border-radius: 12px; box-shadow: 0 2px 10px rgba(0,0,0,0.05); border: 1px solid #e2e8f0;">
                    <div style="font-size: 3rem; margin-bottom: 1rem;">👋</div>
                    <h2 style="font-size: 1.5rem; color: #1e293b; margin-bottom: 0.5rem;">Bienvenue sur le Backoffice</h2>
                    <p style="color: #64748b; font-size: 1.1rem;">Sélectionnez un module dans le menu à gauche pour commencer votre gestion.</p>
                </div>
            </div>
        </div>
    </main>

    <!-- Intégration du Chatbot Assistant -->
    <?php include __DIR__ . '/../frontend/chatbot.php'; ?>
</body>
</html>
