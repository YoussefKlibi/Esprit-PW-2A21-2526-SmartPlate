<?php
// /view/front_sidebar.php
$base_url = '/integration/view';

// Déterminer la page active pour la classe "active"
$current_page = basename($_SERVER['PHP_SELF']);

// Avatar logique
$sidebarAvatarSrc = 'https://ui-avatars.com/api/?name=User&background=d4f283&color=1a1a1a&rounded=true';
$sidebarUserName = 'Utilisateur';

if (!empty($_SESSION['user_email'])) {
    // On essaie de charger les infos utilisateur
    $user_file_path = realpath(__DIR__ . '/../controller/User/UserController.php');
    if ($user_file_path && file_exists($user_file_path)) {
        require_once $user_file_path;
        if (class_exists('UserController')) {
            $userC_sidebar = new UserController();
            $userInfo_sidebar = $userC_sidebar->getUserByEmail($_SESSION['user_email']);
            
            if (!empty($userInfo_sidebar)) {
                $sidebarUserName = htmlspecialchars(trim(($userInfo_sidebar['prenom'] ?? '') . ' ' . ($userInfo_sidebar['nom'] ?? '')) ?: ($userInfo_sidebar['email'] ?? 'Utilisateur'));
                
                $baseDirSidebar = realpath(__DIR__ . '/User/uploads/profile_pictures');
                $baseWebSidebar = $base_url . '/User/uploads/profile_pictures';
                $userIdSidebar = (int)$userInfo_sidebar['id'];
                
                if ($baseDirSidebar) {
                    $extensions = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
                    foreach ($extensions as $ext) {
                        $fpath = $baseDirSidebar . '/user_' . $userIdSidebar . '.' . $ext;
                        if (is_file($fpath)) {
                            $sidebarAvatarSrc = $baseWebSidebar . '/user_' . $userIdSidebar . '.' . $ext . '?v=' . filemtime($fpath);
                            break;
                        }
                    }
                }
            }
        }
    }
}
?>
<style>
/* CSS strict pour forcer la sidebar à rester identique partout */
@import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap');

#global-sidebar.sidebar {
    width: 260px !important;
    min-width: 260px !important;
    max-width: 260px !important;
    background-color: #ffffff !important;
    border-right: 1px solid #e2e8f0 !important;
    display: flex !important;
    flex-direction: column !important;
    padding: 1.5rem !important;
    position: sticky !important; /* Modifié pour ne pas chevaucher le contenu */
    top: 0 !important;
    left: 0 !important;
    height: 100vh !important;
    overflow: hidden !important;
    box-sizing: border-box !important;
    margin: 0 !important;
    z-index: 1000 !important;
    font-family: 'Inter', sans-serif !important;
    flex-shrink: 0 !important; /* Empêche la sidebar d'être écrasée */
}

#global-sidebar .sidebar-logo {
    display: flex !important;
    align-items: center !important;
    flex-direction: column !important;
    gap: 0.8rem !important;
    margin-bottom: 3rem !important;
    padding-left: 0.5rem !important;
}

#global-sidebar .sidebar-logo h2 {
    font-size: 1.4rem !important;
    font-weight: 700 !important;
    color: #1a1a1a !important;
    margin: 0 !important;
    font-family: 'Inter', sans-serif !important;
}

#global-sidebar .sidebar-nav {
    display: flex !important;
    flex-direction: column !important;
    gap: 0.5rem !important;
    margin-bottom: 2rem !important;
    width: 100% !important;
}

#global-sidebar .nav-section-title {
    font-size: 0.75rem !important;
    text-transform: uppercase !important;
    font-weight: 600 !important;
    color: #8c8c8c !important;
    margin-bottom: 0.5rem !important;
    padding-left: 0.5rem !important;
    letter-spacing: 0.5px !important;
    font-family: 'Inter', sans-serif !important;
    display: block !important;
}

#global-sidebar .nav-item {
    display: flex !important;
    align-items: center !important;
    gap: 1rem !important;
    padding: 0.8rem 1rem !important;
    color: #4a4a4a !important;
    text-decoration: none !important;
    border-radius: 12px !important;
    font-weight: 500 !important;
    font-size: 1rem !important;
    font-family: 'Inter', sans-serif !important;
    transition: all 0.3s ease !important;
    margin: 0 !important;
    background: transparent !important;
}

#global-sidebar .nav-item .icon {
    font-size: 1.2rem !important;
    display: flex !important;
    align-items: center !important;
    justify-content: center !important;
}

#global-sidebar .nav-item:hover {
    background-color: #f8fafc !important;
    color: #1a1a1a !important;
}

#global-sidebar .nav-item.active {
    background-color: #d4f283 !important; /* Vert SmartPlate */
    color: #1a1a1a !important;
    font-weight: 600 !important;
    box-shadow: none !important;
}

#global-sidebar .sidebar-footer {
    margin-top: auto !important;
    padding-top: 1rem !important;
    border-top: 1px solid #e2e8f0 !important;
    width: 100% !important;
}

#global-sidebar .user-badge {
    display: flex !important;
    align-items: center !important;
    gap: 14px !important;
    padding: 12px 16px !important;
    background: #ffffff !important;
    border: 1px solid #eef2f6 !important;
    border-radius: 20px !important;
    box-shadow: 0 4px 10px rgba(0,0,0,0.03) !important;
    width: 100% !important;
    box-sizing: border-box !important;
}

#global-sidebar .user-info {
    display: flex !important;
    flex-direction: column !important;
    line-height: 1.3 !important;
}

#global-sidebar .user-name {
    font-weight: 700 !important;
    color: #1e293b !important;
    font-size: 0.95rem !important;
    white-space: nowrap !important;
    overflow: hidden !important;
    text-overflow: ellipsis !important;
    max-width: 130px !important;
    margin-bottom: 2px !important;
    font-family: 'Inter', sans-serif !important;
}

#global-sidebar .user-info span {
    font-family: 'Inter', sans-serif !important;
}
</style>
<aside id="global-sidebar" class="sidebar">
    <div class="sidebar-logo">
        <div class="logo-icon" style="padding: 0; overflow: hidden; background: transparent;">
            <img src="<?= $base_url ?>/User/xpdf/logo.jpg" alt="SmartPlate Logo" style="width:100%; height:100%; object-fit:cover; border-radius:12px; display:block;">
        </div>
        <h2>SmartPlate</h2>
    </div>

    <nav class="sidebar-nav">
        <a href="<?= $base_url ?>/User/frontend/dashboard.php" class="nav-item <?= $current_page == 'dashboard.php' ? 'active' : '' ?>">
            <span class="icon">🏠</span>
            <span>Accueil</span>
        </a>

        <a href="<?= $base_url ?>/Produit/Front_produits.php" class="nav-item <?= in_array($current_page, ['Produits.php', 'Front_produits.php']) ? 'active' : '' ?>">
            <span class="icon">🛍️</span>
            <span>Produits</span>
        </a>
        <a href="<?= $base_url ?>/Produit/panier.php" class="nav-item <?= $current_page == 'panier.php' ? 'active' : '' ?>">
            <span class="icon">🛒</span>
            <span>Mon Panier</span>
        </a>

        <!-- Menu Recettes simple pour uniformité -->
        <a href="<?= $base_url ?>/Recette/frontoffice/frontoffice.php" class="nav-item <?= in_array($current_page, ['frontoffice.php', 'planning.php', 'recette-details.php']) ? 'active' : '' ?>">
            <span class="icon">🥗</span>
            <span>Recettes</span>
        </a>
        <a href="<?= $base_url ?>/Produit/Front_boutiques.php" class="nav-item <?= $current_page == 'Front_boutiques.php' ? 'active' : '' ?>">
            <span class="icon">🏬</span>
            <span>Boutiques</span>
        </a>

        <div class="nav-section" style="margin-top: 1.5rem; margin-bottom: 1.5rem;">
            <span class="nav-section-title">Suivi Nutritionnel</span>
            <div class="sub-nav" style="padding-left: 1.2rem; margin-top: 0.5rem; border-left: 2px solid #e2e8f0; margin-left: 1rem;">
                <a href="<?= $base_url ?>/Suivi_Nutritionnel/FrontOffice/Journal.php" class="nav-item <?= $current_page == 'Journal.php' ? 'active' : '' ?>">
                    <span class="icon">🍽️</span>
                    <span>Journal</span>
                </a>
                <a href="<?= $base_url ?>/Suivi_Nutritionnel/FrontOffice/Objectif.php" class="nav-item <?= $current_page == 'Objectif.php' ? 'active' : '' ?>">
                    <span class="icon">🎯</span>
                    <span>Objectif</span>
                </a>
                <a href="<?= $base_url ?>/Suivi_Nutritionnel/FrontOffice/Progression.php" class="nav-item <?= $current_page == 'Progression.php' ? 'active' : '' ?>">
                    <span class="icon">📈</span>
                    <span>Progression</span>
                </a>
            </div>
        </div>

        <a href="<?= $base_url ?>/Forum/frontoffice/forum.php" class="nav-item <?= $current_page == 'forum.php' ? 'active' : '' ?>">
            <span class="icon">💬</span>
            <span>Forum</span>
        </a>

        <a href="<?= $base_url ?>/Reclamation/list_reclamation.php" class="nav-item <?= $current_page == 'list_reclamation.php' ? 'active' : '' ?>">
            <span class="icon">📝</span>
            <span>Réclamation</span>
        </a>
    </nav>

    <div class="sidebar-footer" style="padding: 16px;">
        <div class="user-badge" style="display: flex; align-items: center; gap: 14px; padding: 12px 16px; background: var(--card-bg, #ffffff); border: 1px solid var(--border-color, #eef2f6); border-radius: 20px; box-shadow: 0 4px 10px rgba(0,0,0,0.03);">
            <div class="user-avatar-wrap" style="position: relative; display: inline-flex;">
                <img src="<?= htmlspecialchars($sidebarAvatarSrc) ?>" alt="Avatar" style="width: 48px; height: 48px; border-radius: 50%; object-fit: cover;">
                <span class="user-avatar-online-dot" style="position: absolute; bottom: 0; right: 0; width: 14px; height: 14px; background-color: #10b981; border: 2.5px solid var(--card-bg, #ffffff); border-radius: 50%;"></span>
            </div>
            <div class="user-info" style="display: flex; flex-direction: column; line-height: 1.3;">
                <span class="user-name" style="font-weight: 700; color: var(--text-dark, #1e293b); font-size: 0.95rem; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 130px; margin-bottom: 2px;">
                    <?= $sidebarUserName ?>
                </span>
                <span style="font-size: 0.8rem; color: var(--text-gray, #64748b); font-weight: 500;">Utilisateur</span>
                <span class="user-status" style="font-size: 0.8rem; color: var(--text-gray, #64748b); font-weight: 500; display: flex; align-items: center; gap: 6px; margin-top: 4px;">
                    <span style="width: 8px; height: 8px; background-color: #10b981; border-radius: 50%; display: inline-block;"></span>
                    <span>En ligne</span>
                </span>
            </div>
        </div>
    </div>
</aside>
