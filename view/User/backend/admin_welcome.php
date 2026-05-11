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
            <a href="../../User/backend/admin_welcome.php" class="menu-item">📊 Vue d'ensemble</a>
            <a href="user_list.php" class="menu-item active">👥 Utilisateurs & Logins</a>
            
            <!-- Nouvelles pages simples -->
            <a href="#" class="menu-item">📦 Produit</a>
            <div class="nav-dropdown">
                <a href="#" class="menu-item" onclick="toggleSubMenu(event, 'recettesMenu')">
                    🍲 Recettes
                    <span id="arrow-recettesMenu" style="float: right; font-size: 0.8em; margin-top: 4px; transition: transform 0.3s;">▼</span>
                </a>
                <div id="recettesMenu" style="display: none; background: rgba(0,0,0,0.03); border-radius: 8px; margin-bottom: 5px; padding-top: 5px; padding-bottom: 5px;">
                    <a href="../../Recette/backoffice/recettes.php" class="menu-item" style="padding-left: 40px; font-size: 0.9em; margin-bottom: 2px;">🍽 Gestion Recettes</a>
                    <a href="../../Recette/backoffice/ingredients.php" class="menu-item" style="padding-left: 40px; font-size: 0.9em; margin-bottom: 2px;">🥕 Gestion Ingrédients</a>
                    <a href="../../Recette/backoffice/dashboard.php" class="menu-item" style="padding-left: 40px; font-size: 0.9em; margin-bottom: 2px;">📊 Dashboard</a>
                </div>
            </div>

            <!-- Menu déroulant : Suivi nutritionnel -->
            <div class="nav-dropdown">
                <a href="#" class="menu-item" onclick="toggleSubMenu(event, 'suiviNutritionnelMenu')">
                    📈 Suivi nutritionnel 
                    <span id="arrow-suiviNutritionnelMenu" style="float: right; font-size: 0.8em; margin-top: 4px; transition: transform 0.3s;">▼</span>
                </a>
                <!-- Sous-menu (masqué par défaut) -->
                <div id="suiviNutritionnelMenu" style="display: none; background: rgba(0,0,0,0.03); border-radius: 8px; margin-bottom: 5px; padding-top: 5px; padding-bottom: 5px;">
                    <a href="../../Suivi_Nutritionnel/BackOffice/admin_dashboard.php" class="menu-item" style="padding-left: 40px; font-size: 0.9em; margin-bottom: 2px;">📊 Dashboard Analytics</a>
                    <a href="../../Suivi_Nutritionnel/BackOffice/admin_objectifs.php" class="menu-item" style="padding-left: 40px; font-size: 0.9em; margin-bottom: 2px;">🎯 Modération Objectifs</a>
                    <a href="../../Suivi_Nutritionnel/BackOffice/admin_journaux.php" class="menu-item" style="padding-left: 40px; font-size: 0.9em; margin-bottom: 2px;">🍽️ Journaux Utilisateurs</a>
                </div>
            </div>

            <!-- Forum avec sous-menu -->
            <div class="nav-dropdown">
                <a href="#" class="menu-item" onclick="toggleSubMenu(event, 'forumMenu')">
                    💬 Forum
                    <span id="arrow-forumMenu" style="float: right; font-size: 0.8em; margin-top: 4px; transition: transform 0.3s;">▼</span>
                </a>
                <div id="forumMenu" style="display: none; background: rgba(0,0,0,0.03); border-radius: 8px; margin-bottom: 5px; padding-top: 5px; padding-bottom: 5px;">
                    <a href="../../Forum/backoffice/admin_forum.php?view=dashboard" class="menu-item" style="padding-left: 40px; font-size: 0.9em; margin-bottom: 2px;">📊 Dashboard</a>
                    <a href="../../Forum/backoffice/admin_forum.php?view=articles" class="menu-item" style="padding-left: 40px; font-size: 0.9em; margin-bottom: 2px;">📝 Articles</a>
                    <a href="../../Forum/backoffice/admin_forum.php?view=drafts" class="menu-item" style="padding-left: 40px; font-size: 0.9em; margin-bottom: 2px;">🕒 Brouillons</a>
                </div>
            </div>
            <a href="../../Reclamation/back/admin_reclamations.php" class="menu-item">📝 Réclamation</a>

            <div class="menu-category" style="margin-top: 20px;">Système</div>
            <a href="#" class="menu-item">⚠️ Anomalies (3)</a>
            <a href="#" class="menu-item" id="openAdminSettingsBtn" style="margin-top: auto;">⚙️ Paramètres du site</a>
            <a href="#" class="menu-item" style="color: #ff6b6b;" onclick="event.preventDefault(); showCustomConfirm('Déconnexion', 'Voulez-vous vraiment vous déconnecter de la session administrateur ?', '🚪', 'Déconnexion', 'orange', () => window.location.href='../frontend/logout.php')">🚪 Déconnexion</a>
        </div>
    </aside>
    <script>
        // Fonction pour gérer l'ouverture/fermeture des sous-menus de la sidebar
        function toggleSubMenu(event, menuId) {
            event.preventDefault(); // Empêche le lien de remonter en haut de la page
            const menu = document.getElementById(menuId);
            const arrow = document.getElementById('arrow-' + menuId);
            
            if (menu.style.display === 'none' || menu.style.display === '') {
                menu.style.display = 'block';
                arrow.style.transform = 'rotate(180deg)'; // Tourne la flèche vers le haut
            } else {
                menu.style.display = 'none';
                arrow.style.transform = 'rotate(0deg)'; // Remet la flèche vers le bas
            }
        }
    </script>

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
