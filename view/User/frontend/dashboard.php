<?php
session_start();
require_once __DIR__ . '/../../../controller/User/UserController.php';

if (!isset($_SESSION['user_email'])) {
    header("Location: login.php");
    exit();
}

$userC = new UserController();
$userInfo = $userC->getUserByEmail($_SESSION['user_email']);

function getProfilePhotoWebPath(int $userId): ?string {
    $baseDir = __DIR__ . '/../uploads/profile_pictures';
    $baseWeb = '../uploads/profile_pictures';
    $extensions = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
    foreach ($extensions as $ext) {
        $filePath = $baseDir . '/user_' . $userId . '.' . $ext;
        if (is_file($filePath)) {
            return $baseWeb . '/user_' . $userId . '.' . $ext . '?v=' . filemtime($filePath);
        }
    }
    return null;
}

if (!$userInfo || strtolower(trim($userInfo['statut'] ?? '')) === 'banni') {
    session_unset(); session_destroy();
    header("Location: login.php?banned=1");
    exit();
}


$userC->updateLastActivity($_SESSION['user_email']);

$profilePhotoPath = getProfilePhotoWebPath((int)$userInfo['id']);
$avatarSrc = $profilePhotoPath
    ? $profilePhotoPath
    : 'https://ui-avatars.com/api/?name=' . urlencode($userInfo['prenom'] . ' ' . $userInfo['nom']) . '&background=d4f283&color=1a1a1a&rounded=true';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title data-i18n="dashboard_title">SmartPlate - Tableau de bord</title>
    <meta name="description" content="SmartPlate - Votre espace personnel pour gérer vos objectifs nutritionnels.">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../css/Template_FrontOffice.css">
</head>
<body>

    <!-- SIDEBAR -->
    <?php include __DIR__ . '/../../front_sidebar.php'; ?>

    <!-- CONTENU PRINCIPAL -->
    <div class="dashboard">

        <!-- HEADER PROFESSIONNEL -->
        <header class="card" style="display: flex; justify-content: space-between; align-items: center; padding: 1.8rem 2rem; border-radius: 20px; margin-bottom: 2rem; box-shadow: 0 4px 20px rgba(0,0,0,0.03);">
            <div>
                <h1 style="font-size: 1.8rem; font-weight: 700; margin-bottom: 0.4rem;">
                    <span data-i18n="dashboard_welcome">Bienvenue</span>, 
                    <span style="color: #20c997;"><?= htmlspecialchars($userInfo['prenom']) ?></span> 👋
                </h1>
                <p style="color: var(--text-gray); font-size: 0.95rem; margin: 0;" data-i18n="dashboard_subtitle">
                    Voici votre espace personnel. Accédez à toutes les fonctionnalités de SmartPlate.
                </p>
            </div>
            
            <div class="journal-actions-right" style="display: flex; align-items: center; gap: 0.8rem;">
                <button type="button" id="openSettingsBtn" style="background: var(--bg-main); border: 1px solid var(--border-color); cursor: pointer; color: var(--text-dark); display: flex; align-items: center; justify-content: center; width: 44px; height: 44px; border-radius: 12px; transition: all 0.2s;" title="Paramètres">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="12" r="3"></circle>
                        <path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"></path>
                    </svg>
                </button>
                <a href="user_profile.php" style="background: #20c997; color: white; border-radius: 12px; padding: 0.6rem 1.2rem; text-decoration: none; font-weight: 600; display: flex; align-items: center; gap: 8px; box-shadow: 0 4px 12px rgba(32, 201, 151, 0.2);">👤 <span data-i18n="btn_profil">Profil</span></a>
                <a href="#" style="background: #feebeb; color: #e74c3c; border-radius: 12px; padding: 0.6rem 1.2rem; text-decoration: none; font-weight: 600; display: flex; align-items: center; gap: 8px;" onclick="event.preventDefault(); showCustomConfirm('Déconnexion', 'Voulez-vous vraiment vous déconnecter ?', '🚪', 'Déconnexion', 'orange', () => window.location.href='logout.php')">🚪 <span data-i18n="btn_logout">Déconnexion</span></a>
            </div>
        </header>

        <!-- DASHBOARD GRID -->
        <div class="dashboard-grid">
            <!-- Card: Journal Alimentaire -->
            <a href="../../Suivi_Nutritionnel/FrontOffice/Journal.php" class="card-stat" style="text-decoration: none; color: inherit;">
                <div class="stat-icon shake">📖</div>
                <div>
                    <h3 style="font-size: 1.1rem; margin-bottom: 0.2rem;" data-i18n="card_journal_title">Journal</h3>
                    <p style="font-size: 0.85rem; color: var(--text-gray);" data-i18n="card_journal_desc">Suivre vos repas quotidiens.</p>
                </div>
            </a>

            <!-- Card: Objectifs -->
            <a href="../../Suivi_Nutritionnel/FrontOffice/Objectif.php" class="card-stat" style="text-decoration: none; color: inherit;">
                <div class="stat-icon shake" style="background: #e8f8f5; color: #20c997;">🎯</div>
                <div>
                    <h3 style="font-size: 1.1rem; margin-bottom: 0.2rem;" data-i18n="card_objectifs_title">Objectifs</h3>
                    <p style="font-size: 0.85rem; color: var(--text-gray);" data-i18n="card_objectifs_desc">Définir vos objectifs nutritionnels.</p>
                </div>
            </a>

            <!-- Card: Progression -->
            <a href="../../Suivi_Nutritionnel/FrontOffice/Progression.php" class="card-stat" style="text-decoration: none; color: inherit;">
                <div class="stat-icon shake" style="background: #e3f2fd; color: #3498db;">📊</div>
                <div>
                    <h3 style="font-size: 1.1rem; margin-bottom: 0.2rem;" data-i18n="card_progression_title">Progression</h3>
                    <p style="font-size: 0.85rem; color: var(--text-gray);" data-i18n="card_progression_desc">Visualiser vos statistiques.</p>
                </div>
            </a>

            <!-- Card: Profil -->
            <a href="user_profile.php" class="card-stat" style="text-decoration: none; color: inherit;">
                <div class="stat-icon shake" style="background: #f3e5f5; color: #9b59b6;">👤</div>
                <div>
                    <h3 style="font-size: 1.1rem; margin-bottom: 0.2rem;" data-i18n="card_profil_title">Profil</h3>
                    <p style="font-size: 0.85rem; color: var(--text-gray);" data-i18n="card_profil_desc">Modifier vos informations.</p>
                </div>
            </a>

            <!-- Card: Recettes -->
            <a href="../../Recette/frontoffice/frontoffice.php" class="card-stat" style="text-decoration: none; color: inherit;">
                <div class="stat-icon shake" style="background: #feebeb; color: #e74c3c;">🍽️</div>
                <div>
                    <h3 style="font-size: 1.1rem; margin-bottom: 0.2rem;" data-i18n="card_recettes_title">Recettes</h3>
                    <p style="font-size: 0.85rem; color: var(--text-gray);" data-i18n="card_recettes_desc">Découvrir des idées de repas.</p>
                </div>
            </a>
        </div>

    </div>

    <!-- Intégration du Chatbot Assistant -->
    <?php include __DIR__ . '/chatbot.php'; ?>
    
    <!-- Modal de confirmation personnalisée -->
    <?php include __DIR__ . '/shared_confirm_modal.php'; ?>

    <!-- Modal overlay commun -->
    <div id="modalOverlay" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 9999; justify-content: center; align-items: center;">
        <!-- Modal Paramètres -->
        <div id="paramModal" class="settings-modal" style="display: none;">
            <div class="settings-modal-header">
                <h3 data-i18n="settings">Paramètres</h3>
            </div>
            
            <div class="settings-modal-body">
                <p class="settings-modal-section-title" data-i18n="appearance">Apparence</p>
                <div style="display: flex; gap: 10px; margin-bottom: 20px;">
                    <button id="btn-light-mode" onclick="toggleDarkMode(false)" class="settings-modal-btn" style="flex: 1; text-align: center;">☀️ Clair</button>
                    <button id="btn-dark-mode" onclick="toggleDarkMode(true)" class="settings-modal-btn" style="flex: 1; text-align: center;">🌙 Sombre</button>
                </div>

                <p class="settings-modal-section-title" data-i18n="language">Langue</p>
                <div style="display: flex; flex-direction: column; gap: 8px;">
                    <button id="btn-lang-fr" onclick="changeLanguage('fr')" class="settings-modal-btn">🇫🇷 Français</button>
                    <button id="btn-lang-en" onclick="changeLanguage('en')" class="settings-modal-btn">🇬🇧 English</button>
                    <button id="btn-lang-ar" onclick="changeLanguage('ar')" class="settings-modal-btn">🇸🇦 العربية</button>
                </div>
            </div>

            <button type="button" class="settings-modal-action-btn" id="closeParamBtn" data-i18n="modal_cancel">Fermer</button>
        </div>
    </div>

    <!-- Translations engine -->
    <script src="../js/translations.js"></script>

    <script>
        const openSettingsBtn = document.getElementById('openSettingsBtn');
        const closeParamBtn = document.getElementById('closeParamBtn');
        const modalOverlay = document.getElementById('modalOverlay');
        const paramModal = document.getElementById('paramModal');

        openSettingsBtn.addEventListener('click', () => {
            modalOverlay.style.display = 'flex';
            paramModal.style.display = 'flex';
            updateModalButtons();
        });

        closeParamBtn.addEventListener('click', () => {
            modalOverlay.style.display = 'none';
            paramModal.style.display = 'none';
        });

        modalOverlay.addEventListener('click', (e) => {
            if(e.target === modalOverlay) {
                modalOverlay.style.display = 'none';
                paramModal.style.display = 'none';
            }
        });

        function updateModalButtons() {
            const isDark = document.body.classList.contains('dark-mode');
            if (document.getElementById('btn-light-mode')) {
                document.getElementById('btn-light-mode').classList.toggle('active', !isDark);
                document.getElementById('btn-dark-mode').classList.toggle('active', isDark);
            }

            const currentLang = localStorage.getItem('smartplate_lang') || 'fr';
            if (document.getElementById('btn-lang-fr')) {
                document.getElementById('btn-lang-fr').classList.toggle('active', currentLang === 'fr');
                document.getElementById('btn-lang-en').classList.toggle('active', currentLang === 'en');
                document.getElementById('btn-lang-ar').classList.toggle('active', currentLang === 'ar');
            }
        }

        // Gestion du mode sombre
        function toggleDarkMode(isDark) {
            if (isDark) {
                document.body.classList.add('dark-mode');
                localStorage.setItem('theme', 'dark');
            } else {
                document.body.classList.remove('dark-mode');
                localStorage.setItem('theme', 'light');
            }
            updateModalButtons();
        }
        
        // Appliquer le thème au chargement
        if (localStorage.getItem('theme') === 'dark') {
            document.body.classList.add('dark-mode');
        }

        // Gestion de la langue
        function changeLanguage(lang) {
            localStorage.setItem('smartplate_lang', lang);
            if (typeof applyLanguage === 'function') {
                applyLanguage(lang);
            } else if (typeof updateContent === 'function') {
                updateContent(lang); // depuis translations.js
            } else {
                window.location.reload();
            }
            updateModalButtons();
        }
    </script>
</body>
</html>