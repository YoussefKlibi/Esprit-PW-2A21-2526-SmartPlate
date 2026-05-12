<?php
$base_url = '/integration/view';
$current_page = basename($_SERVER['PHP_SELF']);
?>
<style>
/* --- CENTRAL ADMIN SIDEBAR CSS --- */
:root {
    --admin-green: #20c997;
    --admin-green-dark: #1aa179;
    --white: #ffffff;
}
.admin-sidebar {
    width: 260px !important;
    background: linear-gradient(180deg, var(--admin-green) 0%, var(--admin-green-dark) 100%) !important;
    color: var(--white) !important;
    display: flex !important;
    flex-direction: column !important;
    min-height: 100vh !important;
    position: sticky !important;
    top: 0 !important;
    overflow-y: auto !important;
}
.sidebar-header {
    padding: 30px 20px 20px !important;
    font-size: 1.8rem !important;
    font-weight: 800 !important;
    border-bottom: 1px solid rgba(255, 255, 255, 0.2) !important;
    display: flex !important;
    flex-direction: column !important;
    align-items: center !important;
    justify-content: center !important;
    gap: 15px !important;
    text-align: center !important;
}
.sidebar-menu {
    padding: 20px 0 !important;
    flex: 1 !important;
}
.menu-category {
    font-size: 0.75rem !important;
    text-transform: uppercase !important;
    letter-spacing: 1px !important;
    padding: 10px 20px !important;
    color: rgba(255, 255, 255, 0.7) !important;
    font-weight: 600 !important;
}
.menu-item {
    padding: 12px 20px 12px 30px !important;
    display: flex !important;
    align-items: center !important;
    gap: 10px !important;
    color: var(--white) !important;
    text-decoration: none !important;
    transition: 0.2s !important;
    font-weight: 500 !important;
    font-size: 0.95rem !important;
    border-left: 4px solid transparent !important;
}
.menu-item:hover,
.menu-item.active {
    background-color: rgba(255, 255, 255, 0.1) !important;
    border-left-color: var(--white) !important;
}
.nav-dropdown > a.menu-item {
    justify-content: space-between !important;
}
</style>
<aside class="admin-sidebar">
    <div class="sidebar-header">
        <span>
            <img src="<?= $base_url ?>/User/xpdf/logo.jpg" alt="SmartPlate Logo" style="width:80px; height:80px; object-fit:cover; border-radius:20px; box-shadow: 0 4px 15px rgba(0,0,0,0.15); display:block; margin: 0 auto;">
        </span> 
        <span style="font-size: 1.6rem; letter-spacing: 0.5px;">SmartPlate</span>
    </div>
    <div class="sidebar-menu">
        <div class="menu-category">Menu Principal</div>
        <a href="<?= $base_url ?>/User/backend/admin_welcome.php" class="menu-item <?= in_array($current_page, ['admin_welcome.php']) ? 'active' : '' ?>">📊 Vue d'ensemble</a>
        <a href="<?= $base_url ?>/User/backend/user_list.php" class="menu-item <?= in_array($current_page, ['user_list.php']) ? 'active' : '' ?>">👥 Utilisateurs & Logins</a>
        
        <a href="<?= $base_url ?>/Produit/Admin_Produits.php" class="menu-item <?= in_array($current_page, ['Admin_Produits.php']) ? 'active' : '' ?>">📦 Produits & Boutiques</a>
        
        <div class="nav-dropdown">
            <a href="#" class="menu-item <?= in_array($current_page, ['recettes.php', 'ingredients.php', 'dashboard.php']) ? 'active' : '' ?>" onclick="toggleSubMenu(event, 'recettesMenu')">
                🍲 Recettes
                <span id="arrow-recettesMenu" style="float: right; font-size: 0.8em; margin-top: 4px; transition: transform 0.3s;">▼</span>
            </a>
            <div id="recettesMenu" style="display: <?= in_array($current_page, ['recettes.php', 'ingredients.php', 'dashboard.php']) ? 'block' : 'none' ?>; background: rgba(0,0,0,0.03); border-radius: 8px; margin-bottom: 5px; padding-top: 5px; padding-bottom: 5px;">
                <a href="<?= $base_url ?>/Recette/backoffice/recettes.php" class="menu-item <?= $current_page == 'recettes.php' ? 'active' : '' ?>" style="padding-left: 40px; font-size: 0.9em; margin-bottom: 2px;">🍽 Gestion Recettes</a>
                <a href="<?= $base_url ?>/Recette/backoffice/ingredients.php" class="menu-item <?= $current_page == 'ingredients.php' ? 'active' : '' ?>" style="padding-left: 40px; font-size: 0.9em; margin-bottom: 2px;">🥕 Gestion Ingrédients</a>
                <a href="<?= $base_url ?>/Recette/backoffice/dashboard.php" class="menu-item <?= $current_page == 'dashboard.php' ? 'active' : '' ?>" style="padding-left: 40px; font-size: 0.9em; margin-bottom: 2px;">📊 Dashboard</a>
            </div>
        </div>

        <div class="nav-dropdown">
            <a href="#" class="menu-item <?= in_array($current_page, ['admin_dashboard.php', 'admin_objectifs.php', 'admin_journaux.php']) ? 'active' : '' ?>" onclick="toggleSubMenu(event, 'suiviNutritionnelMenu')">
                📈 Suivi nutritionnel 
                <span id="arrow-suiviNutritionnelMenu" style="float: right; font-size: 0.8em; margin-top: 4px; transition: transform 0.3s;">▼</span>
            </a>
            <div id="suiviNutritionnelMenu" style="display: <?= in_array($current_page, ['admin_dashboard.php', 'admin_objectifs.php', 'admin_journaux.php']) ? 'block' : 'none' ?>; background: rgba(0,0,0,0.03); border-radius: 8px; margin-bottom: 5px; padding-top: 5px; padding-bottom: 5px;">
                <a href="<?= $base_url ?>/Suivi_Nutritionnel/BackOffice/admin_dashboard.php" class="menu-item <?= $current_page == 'admin_dashboard.php' ? 'active' : '' ?>" style="padding-left: 40px; font-size: 0.9em; margin-bottom: 2px;">📊 Dashboard Analytics</a>
                <a href="<?= $base_url ?>/Suivi_Nutritionnel/BackOffice/admin_objectifs.php" class="menu-item <?= $current_page == 'admin_objectifs.php' ? 'active' : '' ?>" style="padding-left: 40px; font-size: 0.9em; margin-bottom: 2px;">🎯 Modération Objectifs</a>
                <a href="<?= $base_url ?>/Suivi_Nutritionnel/BackOffice/admin_journaux.php" class="menu-item <?= $current_page == 'admin_journaux.php' ? 'active' : '' ?>" style="padding-left: 40px; font-size: 0.9em; margin-bottom: 2px;">🍽️ Journaux Utilisateurs</a>
            </div>
        </div>

        <a href="<?= $base_url ?>/Forum/backoffice/admin_forum.php" class="menu-item <?= in_array($current_page, ['admin_forum.php']) ? 'active' : '' ?>">💬 Forum</a>
        <a href="<?= $base_url ?>/Reclamation/back/admin_reclamations.php" class="menu-item <?= in_array($current_page, ['admin_reclamations.php', 'admin_reply_reclamation.php']) ? 'active' : '' ?>">📝 Réclamation</a>

        <div class="menu-category" style="margin-top: 20px;">Système</div>
        <a href="#" class="menu-item">⚠️ Anomalies (3)</a>
        <a href="#" class="menu-item" id="openAdminSettingsBtn" style="margin-top: auto;">⚙️ Paramètres du site</a>
        <a href="#" class="menu-item" style="color: #ff6b6b;" onclick="event.preventDefault(); showCustomConfirm('Déconnexion', 'Voulez-vous vraiment vous déconnecter de la session administrateur ?', '🚪', 'Déconnexion', 'orange', () => window.location.href='<?= $base_url ?>/User/frontend/logout.php')">🚪 Déconnexion</a>
    </div>
</aside>
<script>
    function toggleSubMenu(event, menuId) {
        event.preventDefault();
        const menu = document.getElementById(menuId);
        const arrow = document.getElementById('arrow-' + menuId);

        if (menu.style.display === 'none' || menu.style.display === '') {
            menu.style.display = 'block';
            if (arrow) arrow.style.transform = 'rotate(180deg)';
        } else {
            menu.style.display = 'none';
            if (arrow) arrow.style.transform = 'rotate(0deg)';
        }
    }
</script>
