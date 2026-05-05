<?php
session_start();
if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
    header("Location: ../frontend/login.php");
    exit();
}

// Empêcher la mise en cache par le navigateur
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

require_once __DIR__ . '/../../Controllers/UserController.php';

$userC = new UserController();

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

// Mettre à jour l'activité de l'admin
$userC->updateLastActivity($_SESSION['user_email']);

$search = isset($_GET['q']) ? trim($_GET['q']) : '';
$users = $userC->listeUsers($search);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SmartPlate - Admin Utilisateurs</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../css/Template_BackOffice.css">
    <style>
        .th-hover:hover {
            background-color: #f1f5f9;
        }
        .sort-icon .sort-up, .sort-icon .sort-down {
            opacity: 0.4;
            transition: opacity 0.2s, color 0.2s;
        }
        .sort-active.asc .sort-icon .sort-up {
            opacity: 1;
            color: #3b82f6;
        }
        .sort-active.desc .sort-icon .sort-down {
            opacity: 1;
            color: #3b82f6;
        }
        .users-table th {
            user-select: none;
        }
        /* Action buttons style (supp image equivalent) */
        .btn-action-premium {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 34px;
            height: 34px;
            border-radius: 8px;
            font-size: 1.1rem;
            text-decoration: none;
            transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
            border: 1px solid transparent;
        }
        .btn-action-premium:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }
        .btn-edit { background: #f0f9ff; color: #0ea5e9; border-color: #bae6fd; }
        .btn-edit:hover { background: #e0f2fe; }
        .btn-history { background: #f8fafc; color: #64748b; border-color: #e2e8f0; }
        .btn-history:hover { background: #f1f5f9; }
        .btn-ban { background: #fff1f2; color: #e11d48; border-color: #fecdd3; }
        .btn-ban:hover { background: #ffe4e6; }
        .btn-unban { background: #f0fdf4; color: #16a34a; border-color: #bbf7d0; }
        .btn-unban:hover { background: #dcfce7; }
        .btn-delete { background: #fef2f2; color: #dc2626; border-color: #fecaca; }
        .btn-delete:hover { background: #fee2e2; }
    </style>
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
            <a href="user_list.php" class="menu-item active">👥 Utilisateurs & Logins</a>
            <a href="#" class="menu-item">🎯 Modération Objectifs</a>
            <a href="#" class="menu-item">🍽️ Journaux Utilisateurs</a>

            <div class="menu-category" style="margin-top: 20px;">Système</div>
            <a href="#" class="menu-item">⚠️ Anomalies (3)</a>
            <a href="#" class="menu-item" id="openAdminSettingsBtn" style="margin-top: auto;">⚙️ Paramètres du site</a>
            <a href="#" class="menu-item" style="color: #ff6b6b;" onclick="event.preventDefault(); showCustomConfirm('Déconnexion', 'Voulez-vous vraiment vous déconnecter de la session administrateur ?', '🚪', 'Déconnexion', 'orange', () => window.location.href='../frontend/logout.php')">🚪 Déconnexion</a>
        </div>
    </aside>

    <main class="main-content">

        <header class="topbar">
            <div class="search-bar">
                <form method="GET" action="user_list.php" style="display: flex; align-items: center; gap: 8px;">
                    <input type="text" name="q" value="<?= htmlspecialchars($search) ?>" placeholder="Rechercher un utilisateur (email, ID, nom)..."
                        style="padding: 8px 15px; border: 1px solid #eef0f5; border-radius: 20px; width: 300px; outline: none;">
                    <button type="submit" style="padding: 8px 14px; border: none; border-radius: 20px; background: #20c997; color: #fff; cursor: pointer; font-weight: 600;">Rechercher</button>
                    <?php if ($search !== ''): ?>
                        <a href="user_list.php" style="padding: 8px 14px; border-radius: 20px; background: #eef0f5; color: #334155; text-decoration: none; font-weight: 600;">Reset</a>
                    <?php endif; ?>
                </form>
            </div>
            <div class="journal-actions-right" style="display: flex; align-items: center; gap: 0.8rem;">
                <a href="../frontend/user_profile.php" style="background: #20c997; color: white; border-radius: 12px; padding: 0.6rem 1.2rem; text-decoration: none; font-weight: 600; display: flex; align-items: center; gap: 8px; box-shadow: 0 4px 12px rgba(32, 201, 151, 0.2); transition: transform 0.2s;" onmouseover="this.style.transform='scale(1.05)'" onmouseout="this.style.transform='scale(1)'">👤 <span data-i18n="btn_profil">Profil Admin</span></a>
                <a href="#" style="background: #feebeb; color: #e74c3c; border-radius: 12px; padding: 0.6rem 1.2rem; text-decoration: none; font-weight: 600; display: flex; align-items: center; gap: 8px; border: none; cursor: pointer; transition: transform 0.2s;" onmouseover="this.style.transform='scale(1.05)'" onmouseout="this.style.transform='scale(1)'" onclick="event.preventDefault(); showCustomConfirm('Déconnexion', 'Voulez-vous vraiment vous déconnecter de la session administrateur ?', '🚪', 'Déconnexion', 'orange', () => window.location.href='../frontend/logout.php')">🚪 <span data-i18n="btn_logout">Déconnexion</span></a>
            </div>
        </header>

        <div class="dashboard-container">

            <div class="page-header">
                <div>
                    <h1>Gestion des Utilisateurs</h1>
                    <p>Liste complète des utilisateurs inscrits sur la plateforme.</p>
                </div>
                <a href="user_create.php" class="btn-action" style="padding: 10px 20px; font-size: 0.9rem; font-weight: 600; text-decoration: none;">+ Ajouter un utilisateur</a>
            </div>

            <div class="content-grid" style="display: block;">
                <div class="card">
                    <div class="card-header">
                        <h2>Liste des Utilisateurs</h2>
                    </div>

                    <div class="users-table-container">
                        <table class="users-table" style="width: 100%;">
                            <thead>
                                <tr>
                                    <th style="cursor: pointer; padding: 4px;" onclick="sortTable(0, this)">
                                        <div style="display: flex; align-items: center; justify-content: space-between; padding: 10px; border-radius: 8px; transition: background 0.2s;" class="th-hover">
                                            <span>ID</span>
                                            <span class="sort-icon" style="color: #94a3b8; font-size: 0.65rem; display: flex; flex-direction: column; line-height: 0.9;">
                                                <span class="sort-up">▲</span><span class="sort-down">▼</span>
                                            </span>
                                        </div>
                                    </th>
                                    <th style="cursor: pointer; padding: 4px;" onclick="sortTable(1, this)">
                                        <div style="display: flex; align-items: center; justify-content: space-between; padding: 10px; border-radius: 8px; transition: background 0.2s;" class="th-hover">
                                            <span>Utilisateur</span>
                                            <span class="sort-icon" style="color: #94a3b8; font-size: 0.65rem; display: flex; flex-direction: column; line-height: 0.9;">
                                                <span class="sort-up">▲</span><span class="sort-down">▼</span>
                                            </span>
                                        </div>
                                    </th>
                                    <th style="cursor: pointer; padding: 4px;" onclick="sortTable(2, this)">
                                        <div style="display: flex; align-items: center; justify-content: space-between; padding: 10px; border-radius: 8px; transition: background 0.2s;" class="th-hover">
                                            <span>Email</span>
                                            <span class="sort-icon" style="color: #94a3b8; font-size: 0.65rem; display: flex; flex-direction: column; line-height: 0.9;">
                                                <span class="sort-up">▲</span><span class="sort-down">▼</span>
                                            </span>
                                        </div>
                                    </th>
                                    <th style="cursor: pointer; padding: 4px;" onclick="sortTable(3, this)">
                                        <div style="display: flex; align-items: center; justify-content: space-between; padding: 10px; border-radius: 8px; transition: background 0.2s;" class="th-hover">
                                            <span>Date d'inscription</span>
                                            <span class="sort-icon" style="color: #94a3b8; font-size: 0.65rem; display: flex; flex-direction: column; line-height: 0.9;">
                                                <span class="sort-up">▲</span><span class="sort-down">▼</span>
                                            </span>
                                        </div>
                                    </th>
                                    <th style="padding: 14px;">Localisation</th>
                                    <th style="cursor: pointer; padding: 4px;" onclick="sortTable(5, this)">
                                        <div style="display: flex; align-items: center; justify-content: space-between; padding: 10px; border-radius: 8px; transition: background 0.2s;" class="th-hover">
                                            <span>Statut</span>
                                            <span class="sort-icon" style="color: #94a3b8; font-size: 0.65rem; display: flex; flex-direction: column; line-height: 0.9;">
                                                <span class="sort-up">▲</span><span class="sort-down">▼</span>
                                            </span>
                                        </div>
                                    </th>
                                    <th style="padding: 14px;">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($users)): ?>
                                <tr>
                                    <td colspan="7" style="text-align: center; padding: 3rem; color: var(--text-gray);">Aucun utilisateur inscrit pour le moment.</td>
                                </tr>
                                <?php else: ?>
                                <?php foreach ($users as $user): ?>
                                <tr>
                                    <td><?= htmlspecialchars($user['id']) ?></td>
                                    <td>
                                        <?php
                                        $userPhotoPath = getProfilePhotoWebPath((int)($user['id'] ?? 0));
                                        $userAvatarSrc = $userPhotoPath
                                            ? $userPhotoPath
                                            : 'https://ui-avatars.com/api/?name=' . urlencode(($user['prenom'] ?? '') . ' ' . ($user['nom'] ?? '')) . '&background=random';
                                        ?>
                                        <div class="user-cell" style="display: flex; align-items: center; gap: 10px;">
                                            <img src="<?= htmlspecialchars($userAvatarSrc) ?>"
                                                class="user-avatar" style="width: 40px; height: 40px; border-radius: 50%; object-fit: cover;" alt="Avatar">
                                            <div class="user-name-col" style="display: flex; flex-direction: column;">
                                                <strong style="color: var(--text-dark);"><?= htmlspecialchars(($user['prenom'] ?? '') . ' ' . $user['nom']) ?></strong>
                                            </div>
                                        </div>
                                    </td>
                                    <td style="color: var(--text-gray);"><?= htmlspecialchars($user['email']) ?></td>
                                    <td style="color: var(--text-gray);"><?= $user['created_at'] ?? 'N/A' ?></td>
                                    <td>
                                        <?php if (!empty($user['last_latitude']) && !empty($user['last_longitude'])): ?>
                                            <div class="user-location-city" data-lat="<?= htmlspecialchars($user['last_latitude']) ?>" data-lng="<?= htmlspecialchars($user['last_longitude']) ?>" style="font-size: 0.9rem; color: var(--text-dark); margin-bottom: 3px;">
                                                <span style="color: #22c55e;">📍</span> <strong>Recherche...</strong>
                                            </div>
                                            <div style="font-size: 0.85rem; margin-bottom: 3px;">
                                                <a href="https://www.openstreetmap.org/?mlat=<?= urlencode($user['last_latitude']) ?>&mlon=<?= urlencode($user['last_longitude']) ?>#map=16/<?= urlencode($user['last_latitude']) ?>/<?= urlencode($user['last_longitude']) ?>" target="_blank" style="color: #3b82f6; text-decoration: none;">Voir sur la carte ⍈</a>
                                            </div>
                                            <div style="font-size: 0.8rem; color: #64748b;">
                                                <?php 
                                                    $dateStr = $user['last_location_update'] ?? '';
                                                    if ($dateStr) {
                                                        echo date('d/m/Y H:i:s', strtotime($dateStr));
                                                    } else {
                                                        echo 'Inconnu';
                                                    }
                                                ?>
                                            </div>
                                        <?php else: ?>
                                            <span style="color: #94a3b8; font-size: 0.85rem; font-style: italic;">Inconnue</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php 
                                        $statut_db = strtolower(trim($user['statut'] ?? 'inactif'));
                                        
                                        if ($statut_db === 'banni') {
                                            $badgeClass = 'background: #feebeb; color: #e74c3c;';
                                            $badgeText = 'Banni';
                                        } elseif ($statut_db === 'actif') {
                                            $badgeClass = 'background: #e8f8f5; color: #20c997;';
                                            $badgeText = 'Actif';
                                        } else {
                                            $badgeClass = 'background: #f1f5f9; color: #64748b;';
                                            $badgeText = 'Inactif';
                                        }
                                        ?>
                                        <span style="padding: 4px 8px; border-radius: 4px; font-size: 0.8rem; font-weight: 600; <?= $badgeClass ?>"><?= htmlspecialchars($badgeText) ?></span>
                                    </td>
                                    <td>
                                        <div style="display: flex; gap: 6px; flex-wrap: nowrap;">
                                            <a href="user_update.php?id=<?= $user['id'] ?>" class="btn-action-premium btn-edit" title="Modifier">
                                                ✏️
                                            </a>
                                            <a href="user_history.php?id=<?= $user['id'] ?>" class="btn-action-premium btn-history" title="Historique">
                                                🕒
                                            </a>
                                        
                                        <?php if ($user['email'] !== 'ilyesgaied32@gmail.com'): ?>
                                            <?php if ($statut_db === 'banni'): ?>
                                                <a href="#" 
                                                   onclick="event.preventDefault(); showCustomConfirm('Débannir l\'utilisateur', 'Êtes-vous sûr de vouloir débannir <?= htmlspecialchars(addslashes($user['prenom'])) ?> ?', '✅', 'Débannir', 'blue', () => window.location.href='user_ban.php?id=<?= $user['id'] ?>&action=unban')" 
                                                   class="btn-action-premium btn-unban" title="Débannir">
                                                    ✅
                                                </a>
                                            <?php else: ?>
                                                <a href="#" 
                                                   onclick="event.preventDefault(); showCustomConfirm('Bannir l\'utilisateur', 'Êtes-vous sûr de vouloir bannir <?= htmlspecialchars(addslashes($user['prenom'])) ?> ?', '🚫', 'Bannir', 'orange', () => window.location.href='user_ban.php?id=<?= $user['id'] ?>&action=ban')" 
                                                   class="btn-action-premium btn-ban" title="Bannir">
                                                    🚫
                                                </a>
                                            <?php endif; ?>
                                        <?php endif; ?>

                                        <a href="#"  
                                           onclick="event.preventDefault(); showCustomConfirm('Supprimer l\'utilisateur', 'Êtes-vous sûr de vouloir supprimer définitivement cet utilisateur ? Cette action est irréversible.', '🗑️', 'Supprimer', 'red', () => window.location.href='user_delete.php?id=<?= $user['id'] ?>')" 
                                           class="btn-action-premium btn-delete" title="Supprimer">
                                            🗑️
                                        </a>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <!-- Intégration du Chatbot Assistant -->
    <?php include __DIR__ . '/../frontend/chatbot.php'; ?>
    
    <!-- Modal de confirmation personnalisée -->
    <?php include __DIR__ . '/../frontend/shared_confirm_modal.php'; ?>

    <!-- Modal overlay admin settings -->
    <div id="adminModalOverlay" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 9999; justify-content: center; align-items: center;">
        <div id="adminParamModal" class="settings-modal" style="display: none; background: #fff; width: 350px; border-radius: 16px; padding: 20px; box-shadow: 0 10px 40px rgba(0,0,0,0.1);">
            <div class="settings-modal-header" style="text-align: center; border-bottom: 1px solid #f1f5f9; padding-bottom: 16px; margin-bottom: 16px;">
                <h3 data-i18n="settings" style="margin:0; font-size:1.1rem;">Paramètres</h3>
            </div>
            <div class="settings-modal-body">
                <p class="settings-modal-section-title" data-i18n="appearance" style="font-size:0.85rem; color:#64748b; font-weight:bold; margin-bottom:10px;">Apparence</p>
                <div style="display: flex; gap: 10px; margin-bottom: 20px;">
                    <button id="btn-admin-light-mode" onclick="toggleAdminDarkMode(false)" class="settings-modal-btn" style="flex: 1; padding:10px; border:1px solid #e2e8f0; border-radius:8px; background:#fff; cursor:pointer;">☀️ Clair</button>
                    <button id="btn-admin-dark-mode" onclick="toggleAdminDarkMode(true)" class="settings-modal-btn" style="flex: 1; padding:10px; border:1px solid #e2e8f0; border-radius:8px; background:#fff; cursor:pointer;">🌙 Sombre</button>
                </div>
                <p class="settings-modal-section-title" data-i18n="language" style="font-size:0.85rem; color:#64748b; font-weight:bold; margin-bottom:10px;">Langue</p>
                <div style="display: flex; flex-direction: column; gap: 8px;">
                    <button id="btn-admin-lang-fr" onclick="changeAdminLanguage('fr')" class="settings-modal-btn" style="padding:10px; border:1px solid #e2e8f0; border-radius:8px; background:#fff; cursor:pointer; text-align:left;">🇫🇷 Français</button>
                    <button id="btn-admin-lang-en" onclick="changeAdminLanguage('en')" class="settings-modal-btn" style="padding:10px; border:1px solid #e2e8f0; border-radius:8px; background:#fff; cursor:pointer; text-align:left;">🇬🇧 English</button>
                    <button id="btn-admin-lang-ar" onclick="changeAdminLanguage('ar')" class="settings-modal-btn" style="padding:10px; border:1px solid #e2e8f0; border-radius:8px; background:#fff; cursor:pointer; text-align:left;">🇸🇦 العربية</button>
                </div>
            </div>
            <button type="button" class="settings-modal-action-btn primary" id="closeAdminParamBtn" data-i18n="modal_cancel" style="width:100%; padding:14px; background:#f8fafc; border:none; border-top:1px solid #f1f5f9; cursor:pointer; font-weight:bold; margin-top:16px; border-radius:8px;">Fermer</button>
        </div>
    </div>

    <!-- Scripts Javascript -->
    <script src="../js/translations.js"></script>
    <script>
        const openSettingsBtn = document.getElementById('openAdminSettingsBtn');
        const closeParamBtn = document.getElementById('closeAdminParamBtn');
        const modalOverlay = document.getElementById('adminModalOverlay');
        const paramModal = document.getElementById('adminParamModal');

        openSettingsBtn.addEventListener('click', (e) => {
            e.preventDefault();
            modalOverlay.style.display = 'flex';
            paramModal.style.display = 'block';
            updateAdminModalButtons();
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

        function updateAdminModalButtons() {
            const isDark = document.body.classList.contains('dark-mode');
            document.getElementById('btn-admin-light-mode').style.background = isDark ? '#fff' : '#e2e8f0';
            document.getElementById('btn-admin-dark-mode').style.background = isDark ? '#333' : '#fff';
            document.getElementById('btn-admin-dark-mode').style.color = isDark ? '#fff' : '#000';

            const currentLang = localStorage.getItem('smartplate_lang') || 'fr';
            document.getElementById('btn-admin-lang-fr').style.background = currentLang === 'fr' ? '#e2e8f0' : '#fff';
            document.getElementById('btn-admin-lang-en').style.background = currentLang === 'en' ? '#e2e8f0' : '#fff';
            document.getElementById('btn-admin-lang-ar').style.background = currentLang === 'ar' ? '#e2e8f0' : '#fff';
        }

        function toggleAdminDarkMode(isDark) {
            if (isDark) {
                document.body.classList.add('dark-mode');
                localStorage.setItem('theme', 'dark');
            } else {
                document.body.classList.remove('dark-mode');
                localStorage.setItem('theme', 'light');
            }
            updateAdminModalButtons();
        }

        if (localStorage.getItem('theme') === 'dark') {
            document.body.classList.add('dark-mode');
        }

        function changeAdminLanguage(lang) {
            localStorage.setItem('smartplate_lang', lang);
            if (typeof updateContent === 'function') {
                updateContent(lang);
            }
            updateAdminModalButtons();
        }
        async function reverseGeocode(lat, lng) {
            const cacheKey = `geo_${lat}_${lng}`;
            const cached = localStorage.getItem(cacheKey);
            if (cached) return JSON.parse(cached);

            const url = `https://nominatim.openstreetmap.org/reverse?format=jsonv2&lat=${encodeURIComponent(lat)}&lon=${encodeURIComponent(lng)}&zoom=16&addressdetails=1`;
            const response = await fetch(url, { headers: { 'Accept': 'application/json', 'User-Agent': 'SmartPlate-App' } });
            if (!response.ok) throw new Error('Erreur réseau');
            const data = await response.json();
            const address = data.address || {};
            const city = address.city || address.town || address.village || address.county || '';
            const country = address.country || '';
            const label = [city, country].filter(Boolean).join(', ');
            
            const result = {
                label: label || data.display_name || 'Lieu non déterminé',
                countryCode: (address.country_code || '').toUpperCase()
            };
            
            localStorage.setItem(cacheKey, JSON.stringify(result));
            return result;
        }

        function countryCodeToFlag(countryCode) {
            if (!countryCode || countryCode.length !== 2) return '';
            const codePoints = countryCode.toUpperCase().split('').map(char => 127397 + char.charCodeAt(0));
            return String.fromCodePoint(...codePoints);
        }

        async function resolveAllLocations() {
            const els = document.querySelectorAll('.user-location-city');
            for (let el of els) {
                let lat = el.dataset.lat;
                let lng = el.dataset.lng;
                try {
                    let res = await reverseGeocode(lat, lng);
                    let flag = countryCodeToFlag(res.countryCode);
                    el.innerHTML = `<span style="color: #22c55e;">📍</span> <strong>${res.label}</strong> ${flag}`;
                } catch (e) {
                    el.innerHTML = `<span style="color: #22c55e;">📍</span> <strong>${parseFloat(lat).toFixed(4)}, ${parseFloat(lng).toFixed(4)}</strong>`;
                }
                // Attendre 1 seconde pour respecter la limite de l'API Nominatim (1 req/sec)
                await new Promise(r => setTimeout(r, 1000));
            }
        }

        document.addEventListener('DOMContentLoaded', resolveAllLocations);

        function sortTable(n, thElement) {
            var table, rows, switching, i, x, y, shouldSwitch, dir, switchcount = 0;
            table = document.querySelector(".users-table");
            switching = true;
            dir = "asc"; 
            
            document.querySelectorAll('th .th-hover').forEach(el => {
                el.classList.remove('sort-active', 'asc', 'desc');
            });

            while (switching) {
                switching = false;
                rows = table.rows;
                for (i = 1; i < (rows.length - 1); i++) {
                    shouldSwitch = false;
                    x = rows[i].getElementsByTagName("TD")[n];
                    y = rows[i + 1].getElementsByTagName("TD")[n];
                    if (!x || !y) continue;
                    let cmpX = x.innerText.toLowerCase();
                    let cmpY = y.innerText.toLowerCase();
                    if (n === 0) {
                        cmpX = parseInt(cmpX) || 0;
                        cmpY = parseInt(cmpY) || 0;
                    }
                    if (dir == "asc") {
                        if (cmpX > cmpY) {
                            shouldSwitch = true;
                            break;
                        }
                    } else if (dir == "desc") {
                        if (cmpX < cmpY) {
                            shouldSwitch = true;
                            break;
                        }
                    }
                }
                if (shouldSwitch) {
                    rows[i].parentNode.insertBefore(rows[i + 1], rows[i]);
                    switching = true;
                    switchcount ++;
                } else {
                    if (switchcount == 0 && dir == "asc") {
                        dir = "desc";
                        switching = true;
                    }
                }
            }
            
            if (thElement) {
                thElement.querySelector('.th-hover').classList.add('sort-active', dir);
            }
        }
    </script>
</body>
</html>
