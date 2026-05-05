<?php
session_start();
require_once __DIR__ . '/../../Controllers/UserController.php';

if (!isset($_SESSION['user_email'])) {
    header("Location: login.php");
    exit();
}

$userC = new UserController();
$userInfo = $userC->getUserByEmail($_SESSION['user_email']);

$loginHistory = $userC->getLoginHistory($userInfo['id'], 50);

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
    session_unset();
    session_destroy();
    header("Location: login.php?banned=1");
    exit();
}



// Mettre à jour l'activité
$userC->updateLastActivity($_SESSION['user_email']);

$success = "";
$erreur = "";

// Handle profile update
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nom = trim($_POST['nom'] ?? '');
    $prenom = trim($_POST['prenom'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $ancien_mdp = $_POST['ancien_mdp'] ?? '';
    $nouveau_mdp = $_POST['nouveau_mdp'] ?? '';

    $hashedPassword = null;
    $removePhoto = isset($_POST['remove_photo']) && $_POST['remove_photo'] === '1';

    if ($removePhoto) {
        $uploadDir = __DIR__ . '/../uploads/profile_pictures';
        foreach (glob($uploadDir . '/user_' . $userInfo['id'] . '.*') ?: [] as $oldPhoto) {
            if (is_file($oldPhoto)) {
                @unlink($oldPhoto);
            }
        }
    }

    // Upload et validation de la photo de profil
    if (isset($_FILES['photo_profil']) && ($_FILES['photo_profil']['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE) {
        $uploadError = (int)($_FILES['photo_profil']['error'] ?? UPLOAD_ERR_NO_FILE);
        if ($uploadError !== UPLOAD_ERR_OK) {
            $erreur = "Erreur lors de l'envoi de la photo de profil.";
        } else {
            $maxBytes = 2 * 1024 * 1024; // 2 MB
            $tmpPath = $_FILES['photo_profil']['tmp_name'] ?? '';
            $fileSize = (int)($_FILES['photo_profil']['size'] ?? 0);

            if ($fileSize <= 0 || $fileSize > $maxBytes) {
                $erreur = "La photo doit faire au maximum 2 MB.";
            } elseif (!is_uploaded_file($tmpPath)) {
                $erreur = "Fichier upload invalide.";
            } elseif (@getimagesize($tmpPath) === false) {
                $erreur = "Le fichier sélectionné n'est pas une image valide.";
            } else {
                $finfo = new finfo(FILEINFO_MIME_TYPE);
                $mimeType = $finfo->file($tmpPath);
                $allowedTypes = [
                    'image/jpeg' => 'jpg',
                    'image/png' => 'png',
                    'image/webp' => 'webp',
                    'image/gif' => 'gif'
                ];

                if (!isset($allowedTypes[$mimeType])) {
                    $erreur = "Format de photo non autorisé. Utilisez JPG, PNG, WEBP ou GIF.";
                } else {
                    $uploadDir = __DIR__ . '/../uploads/profile_pictures';
                    if (!is_dir($uploadDir) && !mkdir($uploadDir, 0755, true)) {
                        $erreur = "Impossible de créer le dossier de stockage des photos.";
                    } else {
                        $extension = $allowedTypes[$mimeType];
                        $targetFile = $uploadDir . '/user_' . $userInfo['id'] . '.' . $extension;

                        foreach (glob($uploadDir . '/user_' . $userInfo['id'] . '.*') ?: [] as $oldPhoto) {
                            if ($oldPhoto !== $targetFile && is_file($oldPhoto)) {
                                @unlink($oldPhoto);
                            }
                        }

                        if (!move_uploaded_file($tmpPath, $targetFile)) {
                            $erreur = "Impossible d'enregistrer la photo de profil.";
                        }
                    }
                }
            }
        }
    }

    // Process password change if requested
    if (!empty($nouveau_mdp)) {
        if (empty($ancien_mdp)) {
            $erreur = "Veuillez saisir votre ancien mot de passe pour le modifier.";
        } elseif (!password_verify($ancien_mdp, $userInfo['mot_de_passe'])) {
            $erreur = "L'ancien mot de passe est incorrect.";
        } else {
            // Validation du nouveau mot de passe (côté serveur - sécurité)
            if (strlen($nouveau_mdp) < 8) {
                $erreur = "Le nouveau mot de passe doit contenir au moins 8 caractères.";
            } elseif (!preg_match('/[A-Z]/', $nouveau_mdp)) {
                $erreur = "Le nouveau mot de passe doit contenir au moins une majuscule.";
            } elseif (!preg_match('/[0-9]/', $nouveau_mdp)) {
                $erreur = "Le nouveau mot de passe doit contenir au moins un chiffre.";
            } elseif (!preg_match('/[@$!%*?&]/', $nouveau_mdp)) {
                $erreur = "Le nouveau mot de passe doit contenir au moins un caractère spécial (@$!%*?&).";
            } else {
                $hashedPassword = password_hash($nouveau_mdp, PASSWORD_DEFAULT);
            }
        }
    }

    if (empty($erreur)) {
        $result = $userC->updateUserProfile(
            $userInfo['id'],
            $nom,
            $prenom,
            $email,
            $hashedPassword
        );

        if ($result) {
            $_SESSION['user_email'] = $email;
            $success = "Profil mis à jour avec succès !";
            $userInfo = $userC->getUserByEmail($email);
        } else {
            $erreur = "Erreur lors de la mise à jour du profil.";
        }
    }
}

$profilePhotoPath = getProfilePhotoWebPath((int)$userInfo['id']);
$avatarSrc = $profilePhotoPath
    ? $profilePhotoPath
    : 'https://ui-avatars.com/api/?name=' . urlencode($userInfo['prenom'] . ' ' . $userInfo['nom']) . '&background=d4f283&color=1a1a1a&rounded=true';
$hasProfilePhoto = $profilePhotoPath !== null;
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title data-i18n="profile_title">SmartPlate - Mon Profil</title>
    <meta name="description" content="SmartPlate - Gérez votre profil et vos informations personnelles.">
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
            <span class="nav-section-title" data-i18n="nav_section_navigation">Navigation</span>
            <a href="dashboard.php" class="nav-item" id="nav-dashboard">
                <span class="icon">🏠</span>
                <span data-i18n="nav_dashboard">Tableau de bord</span>
            </a>
            <a href="#" class="nav-item" id="nav-journal">
                <span class="icon">📖</span>
                <span data-i18n="nav_journal">Journal</span>
            </a>
            <a href="#" class="nav-item" id="nav-objectifs">
                <span class="icon">🎯</span>
                <span data-i18n="nav_objectifs">Objectifs</span>
            </a>
            <a href="#" class="nav-item" id="nav-progression">
                <span class="icon">📊</span>
                <span data-i18n="nav_progression">Progression</span>
            </a>

            <span class="nav-section-title" style="margin-top: 20px;" data-i18n="nav_section_account">Mon Compte</span>
            <a href="user_profile.php" class="nav-item active" id="nav-profil">
                <span class="icon">👤</span>
                <span data-i18n="nav_profil">Mon Profil</span>
            </a>
            <a href="#" class="nav-item" id="nav-logout" onclick="event.preventDefault(); showCustomConfirm('Déconnexion', 'Voulez-vous vraiment vous déconnecter ?', '🚪', 'Déconnexion', 'orange', () => window.location.href='logout.php')">
                <span class="icon">🚪</span>
                <span data-i18n="nav_logout">Déconnexion</span>
            </a>
        </nav>

        <div class="sidebar-footer" style="padding: 16px;">
            <div class="user-badge" style="display: flex; align-items: center; gap: 14px; padding: 12px 16px; background: var(--card-bg, #ffffff); border: 1px solid var(--border-color, #eef2f6); border-radius: 20px; box-shadow: 0 4px 10px rgba(0,0,0,0.03);">
                <div class="user-avatar-wrap" style="position: relative; display: inline-flex;">
                    <img src="<?= htmlspecialchars($avatarSrc) ?>" alt="Avatar" style="width: 48px; height: 48px; border-radius: 50%; object-fit: cover;">
                    <span class="user-avatar-online-dot" style="position: absolute; bottom: 0; right: 0; width: 14px; height: 14px; background-color: #10b981; border: 2.5px solid var(--card-bg, #ffffff); border-radius: 50%;"></span>
                </div>
                <div class="user-info" style="display: flex; flex-direction: column; line-height: 1.3;">
                    <span class="user-name" style="font-weight: 700; color: var(--text-dark, #1e293b); font-size: 0.95rem; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 130px; margin-bottom: 2px;"><?= htmlspecialchars($userInfo['prenom'] . ' ' . $userInfo['nom']) ?></span>
                    <span style="font-size: 0.8rem; color: var(--text-gray, #64748b); font-weight: 500;">Utilisateur</span>
                    <span class="user-status" style="font-size: 0.8rem; color: var(--text-gray, #64748b); font-weight: 500; display: flex; align-items: center; gap: 6px; margin-top: 4px;">
                        <span style="width: 8px; height: 8px; background-color: #10b981; border-radius: 50%; display: inline-block;"></span>
                        <span data-i18n="badge_online">En ligne</span>
                    </span>
                </div>
            </div>
        </div>
    </aside>

    <!-- CONTENU PRINCIPAL -->
    <div class="dashboard">
        
        <header class="dashboard-header">
            <h1 style="display: flex; align-items: center; gap: 12px;">
                <span data-i18n="profile_page_title">Mon Profil</span>
                <button type="button" id="openSettingsBtn" style="background: none; border: none; cursor: pointer; color: #1e293b; display: flex; align-items: center; justify-content: center; padding: 8px; border-radius: 50%; transition: background 0.2s;" onmouseover="this.style.background='#f1f5f9'" onmouseout="this.style.background='none'" title="Sécurité et Connexion">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="12" r="3"></circle>
                        <path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"></path>
                    </svg>
                </button>
            </h1>
            <div class="journal-actions-right" style="align-items:center; gap:0.6rem;">
                <a href="dashboard.php" class="btn-icon">🏠 <span data-i18n="btn_home">Accueil</span></a>
                <a href="#" style="background: #feebeb; color: #e74c3c; border-radius: 12px; padding: 0.6rem 1.2rem; text-decoration: none; font-weight: 600; display: flex; align-items: center; gap: 8px; border: none; cursor: pointer; transition: transform 0.2s;" onmouseover="this.style.transform='scale(1.05)'" onmouseout="this.style.transform='scale(1)'" onclick="event.preventDefault(); showCustomConfirm('Déconnexion', 'Voulez-vous vraiment vous déconnecter ?', '🚪', 'Déconnexion', 'orange', () => window.location.href='logout.php')">🚪 <span data-i18n="btn_logout">Déconnexion</span></a>
            </div>
        </header>

        <p style="color: var(--text-gray); margin-bottom: 2rem;" data-i18n="profile_subtitle">Gérez vos informations personnelles et modifiez votre mot de passe.</p>

        <div class="journal-grid">
            <div class="form-section">
                <div class="card">
                    <div class="card-header">
                        <h2 data-i18n="card_account_info">Informations du compte</h2>
                    </div>

                    <?php if (!empty($success)): ?>
                        <div style="background: #e8f8f5; color: #12b886; padding: 1rem; border-radius: 12px; margin-bottom: 1.5rem; font-weight: 500; border: 1px solid #c3f0e0;">
                            ✅ <?= htmlspecialchars($success) ?>
                        </div>
                    <?php endif; ?>

                    <?php if (!empty($erreur)): ?>
                        <div style="background: #feebeb; color: #e74c3c; padding: 1rem; border-radius: 12px; margin-bottom: 1.5rem; font-weight: 500; border: 1px solid #f5c6c6;">
                            ❌ <?= htmlspecialchars($erreur) ?>
                        </div>
                    <?php endif; ?>

                    <form action="user_profile.php" method="POST" enctype="multipart/form-data" id="profileForm" class="modern-form" novalidate>
                        <div style="margin-bottom: 1.5rem; padding: 1rem; border: 1px solid #e2e8f0; border-radius: 14px; background: #fafafa;">
                            <h3 style="font-size: 1.05rem; margin-bottom: 0.8rem; color: var(--text-dark);">Photo de profil</h3>
                            <div style="display: flex; align-items: center; gap: 16px; flex-wrap: wrap;">
                                <img id="profilePreviewImage" src="<?= htmlspecialchars($avatarSrc) ?>" alt="Photo de profil" style="width: 72px; height: 72px; border-radius: 50%; object-fit: cover; border: 2px solid #e2e8f0; cursor: pointer; transition: transform 0.2s; box-shadow: 0 4px 10px rgba(0,0,0,0.1);" onclick="openLightbox(this.src)" onmouseover="this.style.transform='scale(1.05)'" onmouseout="this.style.transform='scale(1)'">
                                <div style="flex: 1; min-width: 220px;">
                                    <input type="file" id="photo_profil" name="photo_profil" accept=".jpg,.jpeg,.png,.webp,.gif,image/jpeg,image/png,image/webp,image/gif" style="display:none;">
                                    <input type="hidden" id="remove_photo" name="remove_photo" value="0">
                                    <div style="display:flex; gap:10px; flex-wrap:wrap; margin-bottom:6px;">
                                        <button type="button" id="choosePhotoBtn" class="btn-secondary" style="border:1px solid #d1d5db; background:#ffffff; color:#111827; border-radius:10px; padding:8px 14px; font-size:13px; font-weight:600; cursor:pointer;">
                                            Choisir photo
                                        </button>
                                        <button type="button" id="removePhotoBtn" class="btn-secondary" style="border: 1px solid #ef4444; color: #ef4444; background: #fff; border-radius: 10px; padding: 8px 14px; font-size: 13px; font-weight:600; cursor:pointer; <?= $hasProfilePhoto ? '' : 'display:none;' ?>">
                                            Supprimer photo
                                        </button>
                                    </div>
                                    <small style="color: #6b7280; font-size: 11px;">Formats autorisés: JPG, PNG, WEBP, GIF. Taille max: 2 MB.</small>
                                </div>
                            </div>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="prenom">Prénom</label>
                                <input type="text" id="prenom" name="prenom" class="form-control" 
                                       value="<?= htmlspecialchars($userInfo['prenom']) ?>">
                                <span id="errorPrenomProfile" style="color: red; font-size: 13px; display: block; margin-top: 5px;"></span>
                            </div>

                            <div class="form-group">
                                <label for="nom">Nom</label>
                                <input type="text" id="nom" name="nom" class="form-control" 
                                       value="<?= htmlspecialchars($userInfo['nom']) ?>">
                                <span id="errorNomProfile" style="color: red; font-size: 13px; display: block; margin-top: 5px;"></span>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label for="email">Email</label>
                                <input type="email" id="email" name="email" class="form-control" 
                                       value="<?= htmlspecialchars($userInfo['email']) ?>">
                                <span id="errorEmailProfile" style="color: red; font-size: 13px; display: block; margin-top: 5px;"></span>
                            </div>
                        </div>

                        <div style="margin-top: 1rem; padding-top: 1.5rem; border-top: 1px solid #e2e8f0;">
                            <h3 style="font-size: 1.1rem; margin-bottom: 1.5rem; color: var(--text-dark);">Sécurité</h3>
                            
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="ancien_mdp">Ancien mot de passe</label>
                                    <div style="position: relative;">
                                        <input type="password" id="ancien_mdp" name="ancien_mdp" class="form-control" 
                                               placeholder="Votre mot de passe actuel">
                                        <span onclick="togglePasswordVisibility('ancien_mdp', this)" style="position: absolute; right: 15px; top: 50%; transform: translateY(-50%); cursor: pointer; color: #6b7280; display: flex; align-items: center;">
                                            <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>
                                        </span>
                                    </div>
                                    <span id="errorAncienMdp" style="color: red; font-size: 13px; display: block; margin-top: 5px;"></span>
                                </div>
                            </div>

                            <div class="form-row">
                                <div class="form-group">
                                    <label for="nouveau_mdp">Nouveau mot de passe</label>
                                    <div style="position: relative;">
                                        <input type="password" id="nouveau_mdp" name="nouveau_mdp" class="form-control" 
                                               placeholder="Laissez vide pour ne pas changer">
                                        <span onclick="togglePasswordVisibility('nouveau_mdp', this)" style="position: absolute; right: 15px; top: 50%; transform: translateY(-50%); cursor: pointer; color: #6b7280; display: flex; align-items: center;">
                                            <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>
                                        </span>
                                    </div>
                                    <span id="errorNouveauMdp" style="color: red; font-size: 13px; display: block; margin-top: 5px;"></span>
                                    <small style="color: #6b7280; font-size: 11px;">Minimum 8 caractères, 1 majuscule, 1 chiffre, 1 caractère spécial (@$!%*?&)</small>
                                </div>
                            </div>
                        </div>


                        <div class="form-actions">
                            <button type="submit" class="btn-main">Enregistrer les modifications</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

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

                <button type="button" class="settings-modal-action-btn primary" id="openHistoryBtn">🛡️ Sécurité et Connexion</button>
                <button type="button" class="settings-modal-action-btn" id="closeParamBtn" data-i18n="modal_cancel">Annuler</button>
            </div>

            <!-- Modal Historique (White Mode / Dark Mode) -->
            <div id="historyModal" class="settings-modal" style="display: none; width: 90%; max-width: 400px; max-height: 80vh; background: var(--card-bg);">
                <div style="display: flex; align-items: center; padding: 12px 16px; border-bottom: 1px solid var(--border-color);">
                    <button type="button" id="backToParamBtn" style="background: none; border: none; font-size: 1.2rem; cursor: pointer; color: var(--text-dark); padding: 0;">&lt;</button>
                    <h2 style="flex: 1; text-align: center; font-size: 1rem; margin: 0; font-weight: 600; color: var(--text-dark);">Activité de connexion du compte</h2>
                    <button type="button" id="closeHistoryBtn" style="background: none; border: none; font-size: 1.2rem; cursor: pointer; color: var(--text-dark); padding: 0;">&times;</button>
                </div>
                <div style="padding: 16px; overflow-y: auto;">
                    <p style="font-size: 0.9rem; color: var(--text-gray); margin-top: 0; margin-bottom: 16px;">Vous êtes actuellement connecté(e) sur ces appareils :</p>
                    
                    <?php 
                    $currentDevice = null;
                    $otherDevices = [];
                    foreach ($loginHistory as $idx => $log) {
                        $log['original_index'] = $idx;
                        if (isset($_SESSION['login_history_id']) && $log['id'] == $_SESSION['login_history_id']) {
                            $currentDevice = $log;
                        } else {
                            $otherDevices[] = $log;
                        }
                    }
                    if (!$currentDevice && !empty($loginHistory)) {
                        $currentDevice = $loginHistory[0];
                        $currentDevice['original_index'] = 0;
                        array_shift($otherDevices);
                    }
                    ?>

                    <?php if ($currentDevice): ?>
                    <div style="display: flex; align-items: center; gap: 12px; padding: 16px; border: 1px solid var(--border-color); border-radius: 12px; margin-bottom: 24px; cursor: pointer; transition: background 0.2s; background: var(--bg-main);" onclick="openDeviceDetail(<?= $currentDevice['original_index'] ?>, true)">
                        <span style="font-size: 1.5rem;">💻</span>
                        <div style="flex: 1;">
                            <p style="margin: 0; font-size: 0.9rem; color: var(--text-dark); font-weight: 500;">
                                <?php
                                    $ua = $currentDevice['device_info'];
                                    echo (strpos($ua, 'Mobile') !== false) ? 'Smartphone' : 'Windows';
                                ?>
                            </p>
                            <span style="font-size: 0.8rem; color: var(--text-gray); display: block;">
                                <?= ($currentDevice['city'] && $currentDevice['country']) ? htmlspecialchars($currentDevice['city'] . ', ' . $currentDevice['country']) : 'Inconnu' ?>
                            </span>
                            <span style="font-size: 0.8rem; color: #22c55e; font-weight: 500; display: block;">Cet appareil</span>
                        </div>
                        <span style="color: var(--text-gray);">&gt;</span>
                    </div>
                    <?php endif; ?>

                    <h3 style="font-size: 0.95rem; font-weight: 600; margin-bottom: 12px; color: var(--text-dark);">Connexions sur d'autres appareils</h3>
                    
                    <div style="border: 1px solid var(--border-color); border-radius: 12px; overflow: hidden;">
                        <?php if (empty($otherDevices)): ?>
                            <div style="padding: 16px; text-align: center; color: var(--text-gray); font-size: 0.85rem;">Aucune autre connexion.</div>
                        <?php else: ?>
                            <?php foreach ($otherDevices as $index => $log): ?>
                                <div class="device-row" data-index="<?= $log['original_index'] ?>" data-id="<?= $log['id'] ?>" onclick="if(event.target.type !== 'checkbox') openDeviceDetail(<?= $log['original_index'] ?>, false)" style="display: flex; align-items: center; gap: 12px; padding: 16px; cursor: pointer; transition: background 0.2s; <?= $index < count($otherDevices) - 1 ? 'border-bottom: 1px solid var(--border-color);' : '' ?> background: var(--bg-main);">
                                    <input type="checkbox" class="device-checkbox" value="<?= $log['id'] ?>" style="display:none; width:18px; height:18px; accent-color:#dc2626; cursor:pointer;" onclick="event.stopPropagation()">
                                    <?php
                                        $ua = $log['device_info'];
                                        $isMobile = (strpos($ua, 'Mobile') !== false || strpos($ua, 'iPhone') !== false || strpos($ua, 'Android') !== false);
                                    ?>
                                    <span style="font-size: 1.5rem;"><?= $isMobile ? '📱' : '💻' ?></span>
                                    <div style="flex: 1;">
                                        <p style="margin: 0; font-size: 0.9rem; color: var(--text-dark); font-weight: 500;">
                                            <?php
                                                if (strpos($ua, 'iPhone') !== false) echo 'iPhone';
                                                elseif (strpos($ua, 'Mac') !== false) echo 'Mac';
                                                elseif (strpos($ua, 'Mobile') !== false) echo 'Smartphone';
                                                else echo 'Windows / PC';
                                            ?>
                                        </p>
                                        <span style="font-size: 0.8rem; color: var(--text-gray); display: block;">
                                            <?= ($log['city'] && $log['country']) ? htmlspecialchars($log['city'] . ', ' . $log['country']) : 'Inconnu' ?>
                                        </span>
                                        <span style="font-size: 0.8rem; color: var(--text-gray); display: block;">
                                            <?= date('d/m/Y à H:i', strtotime($log['login_time'])) ?>
                                        </span>
                                    </div>
                                    <span class="device-arrow" style="color: var(--text-gray); font-weight: bold;">&gt;</span>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                        
                    </div>
                </div>
            </div>

            <!-- Modal Device Detail -->
            <div id="deviceDetailModal" class="settings-modal" style="display: none; width: 90%; max-width: 400px; max-height: 90vh; background: var(--card-bg); position: relative;">
                <div style="display: flex; align-items: center; padding: 12px 16px; border-bottom: 1px solid var(--border-color);">
                    <button type="button" id="backToHistoryBtn" style="background: none; border: none; font-size: 1.2rem; cursor: pointer; color: var(--text-dark); padding: 0;">&lt;</button>
                    <h2 style="flex: 1; text-align: center; font-size: 1rem; margin: 0; font-weight: 600; color: var(--text-dark);">Lieux de connexion</h2>
                    <div style="width: 20px;"></div>
                </div>
                
                <div style="overflow-y: auto; flex: 1;">
                    <div id="mapContainer" style="width: 100%; height: 180px; background: #e5e7eb;">
                        <iframe id="deviceMap" width="100%" height="180" style="border:0;" loading="lazy" allowfullscreen src=""></iframe>
                    </div>
                    
                    <div style="padding: 24px 16px;">
                        <div style="display: flex; align-items: center; gap: 16px; margin-bottom: 24px;">
                            <div id="deviceDetailIcon" style="font-size: 2.2rem; background: var(--bg-main); padding: 16px; border-radius: 50%; width: 60px; height: 60px; display: flex; align-items: center; justify-content: center;">💻</div>
                            <div>
                                <h3 id="deviceDetailName" style="margin: 0; font-size: 1.1rem; font-weight: 600; color: var(--text-dark);">Windows</h3>
                                <p id="deviceDetailLocation" style="margin: 4px 0 0 0; font-size: 0.9rem; color: var(--text-gray);">Tunis, Tunisia</p>
                                <p id="deviceDetailStatus" style="margin: 4px 0 0 0; font-size: 0.85rem; color: #22c55e; font-weight: 500;">Actif maintenant</p>
                            </div>
                        </div>
                        
                        <div style="background: var(--bg-main); border-radius: 12px; padding: 16px; margin-bottom: 24px; border: 1px solid var(--border-color);">
                            <div style="display: flex; justify-content: space-between; margin-bottom: 12px;">
                                <span style="color: var(--text-gray); font-size: 0.9rem;">Date et heure</span>
                                <span id="deviceDetailTime" style="color: var(--text-dark); font-size: 0.9rem; font-weight: 500;"></span>
                            </div>
                            <div style="display: flex; justify-content: space-between; margin-bottom: 12px;">
                                <span style="color: var(--text-gray); font-size: 0.9rem;">Adresse IP</span>
                                <span id="deviceDetailIp" style="color: var(--text-dark); font-size: 0.9rem; font-weight: 500;"></span>
                            </div>
                            <div style="display: flex; justify-content: space-between;">
                                <span style="color: var(--text-gray); font-size: 0.9rem;">Navigateur</span>
                                <span id="deviceDetailBrowser" style="color: var(--text-dark); font-size: 0.9rem; font-weight: 500; text-align: right; max-width: 60%; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;"></span>
                            </div>
                            </div>
                        </div>
                        <div id="securityActionDiv" style="margin-top: 10px; display: none;">
                            <button id="securityChangePwdBtn" type="button" style="width: 100%; padding: 14px; border: none; border-radius: 10px; background: rgba(52, 152, 219, 0.15); color: #2980b9; font-weight: 600; font-size: 1rem; cursor: pointer; transition: background 0.2s;">
                                🔒 Sécuriser mon compte
                            </button>
                            <p style="text-align: center; font-size: 0.8rem; color: var(--text-gray); margin-top: 8px;">
                                Si vous ne reconnaissez pas cet appareil, modifiez votre mot de passe immédiatement.
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Modal Changement Mot de Passe Sécurité -->
            <div id="changePasswordModal" class="settings-modal" style="display: none; width: 90%; max-width: 400px; max-height: 90vh; background: var(--card-bg); position: relative;">
                <div style="display: flex; align-items: center; padding: 12px 16px; border-bottom: 1px solid var(--border-color);">
                    <button type="button" id="backToDeviceDetailBtn" style="background: none; border: none; font-size: 1.2rem; cursor: pointer; color: var(--text-dark); padding: 0;">&lt;</button>
                    <h2 style="flex: 1; text-align: center; font-size: 1rem; margin: 0; font-weight: 600; color: var(--text-dark);">Sécuriser le compte</h2>
                    <div style="width: 20px;"></div>
                </div>
                
                <div style="padding: 24px 16px; overflow-y: auto;">
                    <div style="text-align: center; margin-bottom: 24px;">
                        <div style="font-size: 3rem; margin-bottom: 12px;">🛡️</div>
                        <h3 style="margin: 0 0 8px 0; color: var(--text-dark); font-size: 1.2rem;">Changer le mot de passe</h3>
                        <p style="margin: 0; color: var(--text-gray); font-size: 0.9rem;">Si vous avez détecté une activité suspecte, modifiez votre mot de passe pour protéger votre compte.</p>
                    </div>

                    <form id="securityChangePasswordForm">
                        <div class="input-group" style="margin-bottom: 16px; position: relative;">
                            <label style="display: block; margin-bottom: 8px; font-weight: 500; font-size: 0.9rem; color: var(--text-dark);">Mot de passe actuel</label>
                            <input type="password" id="current_pwd_sec" name="current_password" required style="width: 100%; padding: 12px; border: 1px solid var(--border-color); border-radius: 8px; background: var(--bg-main); color: var(--text-dark);">
                            <span onclick="togglePasswordVisibility('current_pwd_sec', this)" style="position: absolute; right: 12px; top: 38px; cursor: pointer; color: var(--text-gray);">👁️</span>
                        </div>
                        
                        <div class="input-group" style="margin-bottom: 16px; position: relative;">
                            <label style="display: block; margin-bottom: 8px; font-weight: 500; font-size: 0.9rem; color: var(--text-dark);">Nouveau mot de passe</label>
                            <input type="password" id="new_pwd_sec" name="new_password" required style="width: 100%; padding: 12px; border: 1px solid var(--border-color); border-radius: 8px; background: var(--bg-main); color: var(--text-dark);">
                            <span onclick="togglePasswordVisibility('new_pwd_sec', this)" style="position: absolute; right: 12px; top: 38px; cursor: pointer; color: var(--text-gray);">👁️</span>
                        </div>

                        <div class="input-group" style="margin-bottom: 24px; position: relative;">
                            <label style="display: block; margin-bottom: 8px; font-weight: 500; font-size: 0.9rem; color: var(--text-dark);">Confirmer le mot de passe</label>
                            <input type="password" id="confirm_pwd_sec" name="confirm_password" required style="width: 100%; padding: 12px; border: 1px solid var(--border-color); border-radius: 8px; background: var(--bg-main); color: var(--text-dark);">
                            <span onclick="togglePasswordVisibility('confirm_pwd_sec', this)" style="position: absolute; right: 12px; top: 38px; cursor: pointer; color: var(--text-gray);">👁️</span>
                        </div>

                        <div id="secPwdError" style="color: #e74c3c; font-size: 0.85rem; margin-bottom: 16px; text-align: center; display: none;"></div>
                        <div id="secPwdSuccess" style="color: #00a046; font-size: 0.85rem; margin-bottom: 16px; text-align: center; display: none;"></div>

                        <button type="submit" id="secPwdSubmitBtn" style="width: 100%; padding: 14px; border: none; border-radius: 10px; background: var(--primary-color); color: white; font-weight: 600; font-size: 1rem; cursor: pointer; transition: opacity 0.2s;">
                            Mettre à jour le mot de passe
                        </button>
                    </form>
                </div>
            </div>
        </div>

    <script>
        const loginHistoryData = <?= json_encode($loginHistory) ?>;

        function togglePasswordVisibility(inputId, iconSpan) {
            const input = document.getElementById(inputId);
            const isPassword = input.type === 'password';
            input.type = isPassword ? 'text' : 'password';
            
            if (isPassword) {
                iconSpan.innerHTML = '<svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path><line x1="1" y1="1" x2="23" y2="23"></line></svg>';
            } else {
                iconSpan.innerHTML = '<svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>';
            }
        }

        // Logique Modal
        const openSettingsBtn = document.getElementById('openSettingsBtn');
        const modalOverlay = document.getElementById('modalOverlay');
        const paramModal = document.getElementById('paramModal');
        const historyModal = document.getElementById('historyModal');
        const deviceDetailModal = document.getElementById('deviceDetailModal');
        const closeParamBtn = document.getElementById('closeParamBtn');
        const openHistoryBtn = document.getElementById('openHistoryBtn');
        const backToParamBtn = document.getElementById('backToParamBtn');
        const backToHistoryBtn = document.getElementById('backToHistoryBtn');
        const closeHistoryBtn = document.getElementById('closeHistoryBtn');

        // Afficher le modal paramètres
        openSettingsBtn.addEventListener('click', () => {
            modalOverlay.style.display = 'flex';
            paramModal.style.display = 'flex';
            historyModal.style.display = 'none';
            updateModalButtons();
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

        // Fermer tout
        function closeAll() {
            modalOverlay.style.display = 'none';
            paramModal.style.display = 'none';
            historyModal.style.display = 'none';
            deviceDetailModal.style.display = 'none';
        }
        closeParamBtn.addEventListener('click', closeAll);
        closeHistoryBtn.addEventListener('click', closeAll);
        modalOverlay.addEventListener('click', (e) => {
            if (e.target === modalOverlay) closeAll();
        });

        // Fonctions pour les détails de l'appareil
        function getDeviceName(userAgent) {
            if (!userAgent) return 'Inconnu';
            if (userAgent.includes('iPhone')) return 'Apple iPhone';
            if (userAgent.includes('iPad')) return 'Apple iPad';
            if (userAgent.includes('Mac')) return 'Apple Mac';
            if (userAgent.includes('Android')) return 'Android Smartphone';
            if (userAgent.includes('Mobile')) return 'Smartphone';
            if (userAgent.includes('Windows')) return 'Windows PC';
            if (userAgent.includes('Linux')) return 'Linux PC';
            return 'Appareil Inconnu';
        }

        function getDeviceIcon(name) {
            if (name.includes('iPhone') || name.includes('Android') || name.includes('Smartphone') || name.includes('iPad')) return '📱';
            return '💻';
        }

        function getBrowserName(userAgent) {
            if (!userAgent) return 'Inconnu';
            if (userAgent.includes('Chrome') && !userAgent.includes('Edg')) return 'Chrome';
            if (userAgent.includes('Safari') && !userAgent.includes('Chrome')) return 'Safari';
            if (userAgent.includes('Firefox')) return 'Firefox';
            if (userAgent.includes('Edg')) return 'Edge';
            return 'Navigateur web';
        }

        function openDeviceDetail(index, isCurrent) {
            const data = loginHistoryData[index];
            if (!data) return;

            const name = getDeviceName(data.device_info);
            const icon = getDeviceIcon(name);
            const browser = getBrowserName(data.device_info);
            const loc = (data.city && data.country) ? `${data.city}, ${data.country}` : 'Lieu inconnu';
            
            document.getElementById('deviceDetailIcon').textContent = icon;
            document.getElementById('deviceDetailName').textContent = name;
            document.getElementById('deviceDetailLocation').textContent = loc;
            document.getElementById('deviceDetailIp').textContent = data.ip_address || 'Inconnue';
            document.getElementById('deviceDetailBrowser').textContent = browser;
            
            const d = new Date(data.login_time);
            document.getElementById('deviceDetailTime').textContent = d.toLocaleString('fr-FR', {
                day: '2-digit', month: '2-digit', year: 'numeric',
                hour: '2-digit', minute: '2-digit'
            });

            const statusEl = document.getElementById('deviceDetailStatus');
            
            if (isCurrent) {
                statusEl.textContent = 'Actif maintenant';
                statusEl.style.color = '#00a046';
                document.getElementById('securityActionDiv').style.display = 'none';
            } else {
                statusEl.textContent = 'Dernière connexion le ' + d.toLocaleDateString('fr-FR');
                statusEl.style.color = '#737373';
                document.getElementById('securityActionDiv').style.display = 'block';
            }

            const mapFrame = document.getElementById('deviceMap');
            if (data.latitude && data.longitude) {
                const lat = parseFloat(data.latitude);
                const lon = parseFloat(data.longitude);
                const delta = 0.05;
                mapFrame.src = `https://www.openstreetmap.org/export/embed.html?bbox=${lon - delta},${lat - delta},${lon + delta},${lat + delta}&layer=mapnik&marker=${lat},${lon}`;
            } else {
                // Par défaut, afficher la Tunisie sur OpenStreetMap
                mapFrame.src = `https://www.openstreetmap.org/export/embed.html?bbox=8.5,33.0,11.5,37.0&layer=mapnik`;
            }

            historyModal.style.display = 'none';
            deviceDetailModal.style.display = 'flex';
        }

        backToHistoryBtn.addEventListener('click', () => {
            deviceDetailModal.style.display = 'none';
            historyModal.style.display = 'flex';
        });

        // Navigation Security Password Modal
        document.getElementById('securityChangePwdBtn').addEventListener('click', () => {
            deviceDetailModal.style.display = 'none';
            document.getElementById('changePasswordModal').style.display = 'flex';
            document.getElementById('secPwdError').style.display = 'none';
            document.getElementById('secPwdSuccess').style.display = 'none';
            document.getElementById('securityChangePasswordForm').reset();
        });

        document.getElementById('backToDeviceDetailBtn').addEventListener('click', () => {
            document.getElementById('changePasswordModal').style.display = 'none';
            deviceDetailModal.style.display = 'flex';
        });

        // Soumission AJAX du changement de mot de passe
        document.getElementById('securityChangePasswordForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            const btn = document.getElementById('secPwdSubmitBtn');
            const errorDiv = document.getElementById('secPwdError');
            const successDiv = document.getElementById('secPwdSuccess');
            
            btn.textContent = 'Mise à jour...';
            btn.style.opacity = '0.7';
            errorDiv.style.display = 'none';
            successDiv.style.display = 'none';

            const payload = {
                current_password: document.getElementById('current_pwd_sec').value,
                new_password: document.getElementById('new_pwd_sec').value,
                confirm_password: document.getElementById('confirm_pwd_sec').value
            };

            try {
                const res = await fetch('change_password_api.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(payload)
                });
                const data = await res.json();
                
                if (data.success) {
                    successDiv.textContent = data.message;
                    successDiv.style.display = 'block';
                    document.getElementById('securityChangePasswordForm').reset();
                    setTimeout(() => {
                        closeAll();
                        // Rediriger ou recharger pour forcer les sessions invalides si nécessaire, 
                        // ou juste laisser le message.
                    }, 4000);
                } else {
                    errorDiv.textContent = data.error || 'Erreur inconnue.';
                    errorDiv.style.display = 'block';
                }
            } catch (err) {
                errorDiv.textContent = 'Erreur réseau. Veuillez réessayer.';
                errorDiv.style.display = 'block';
            } finally {
                btn.textContent = 'Mettre à jour le mot de passe';
                btn.style.opacity = '1';
            }
        });

        // Naviguer vers l'historique
        openHistoryBtn.addEventListener('click', () => {
            paramModal.style.display = 'none';
            historyModal.style.display = 'flex';
        });

        // Retour aux paramètres
        backToParamBtn.addEventListener('click', () => {
            historyModal.style.display = 'none';
            paramModal.style.display = 'flex';
        });
        
        const deviceRows = document.querySelectorAll('.device-row');
        deviceRows.forEach(row => {
            row.addEventListener('click', (e) => {
                openDeviceDetail(parseInt(row.dataset.index), false);
            });
        });

        // On load checker si le lien contient #historique
        if (window.location.hash === '#historique') {
            modalOverlay.style.display = 'flex';
            historyModal.style.display = 'flex';
        }

        const fileInput = document.getElementById('photo_profil');
        const choosePhotoBtn = document.getElementById('choosePhotoBtn');
        const previewImage = document.getElementById('profilePreviewImage');
        const removePhotoBtn = document.getElementById('removePhotoBtn');
        const removePhotoInput = document.getElementById('remove_photo');
        const defaultAvatar = 'https://ui-avatars.com/api/?name=<?= urlencode($userInfo['prenom'] . ' ' . $userInfo['nom']) ?>&background=d4f283&color=1a1a1a&rounded=true';

        choosePhotoBtn.addEventListener('click', function () {
            fileInput.click();
        });

        fileInput.addEventListener('change', function () {
            const file = this.files && this.files[0] ? this.files[0] : null;
            if (!file) {
                return;
            }

            removePhotoInput.value = '0';
            removePhotoBtn.style.display = 'inline-block';

            const reader = new FileReader();
            reader.onload = function (event) {
                previewImage.src = event.target.result;
            };
            reader.readAsDataURL(file);
        });

        removePhotoBtn.addEventListener('click', function () {
            fileInput.value = '';
            removePhotoInput.value = '1';
            previewImage.src = defaultAvatar;
            removePhotoBtn.style.display = 'none';
        });
    </script>
    
    <!-- La validation se fait via le fichier externe validation.js -->
    <script src="../js/validation.js"></script>

    <script>
        // Gestion du mode sombre
        function toggleDarkMode(isDark) {
            if (isDark) {
                document.body.classList.add('dark-mode');
                localStorage.setItem('theme', 'dark');
            } else {
                document.body.classList.remove('dark-mode');
                localStorage.setItem('theme', 'light');
            }
            if (typeof updateModalButtons === 'function') updateModalButtons();
        }
        
        if (localStorage.getItem('theme') === 'dark') {
            document.body.classList.add('dark-mode');
        }

        function changeLanguage(lang) {
            localStorage.setItem('smartplate_lang', lang);
            if (typeof applyLanguage === 'function') {
                applyLanguage(lang);
            } else if (typeof updateContent === 'function') {
                updateContent(lang);
            } else {
                window.location.reload();
            }
            if (typeof updateModalButtons === 'function') updateModalButtons();
        }
    </script>
    <!-- Intégration du Chatbot Assistant -->
    <?php include __DIR__ . '/chatbot.php'; ?>
    <!-- Translations engine -->
    <script src="../js/translations.js"></script>
    <!-- Modal de confirmation personnalisée -->
    <?php include __DIR__ . '/shared_confirm_modal.php'; ?>
    <div id="imageLightboxModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.85); z-index: 9999; justify-content: center; align-items: center; backdrop-filter: blur(5px); opacity: 0; transition: opacity 0.3s ease;">
        <button type="button" onclick="closeLightbox()" style="position: absolute; top: 20px; right: 20px; background: rgba(255,255,255,0.1); border: none; color: white; width: 44px; height: 44px; border-radius: 50%; font-size: 1.5rem; cursor: pointer; transition: background 0.2s;" onmouseover="this.style.background='rgba(255,255,255,0.3)'" onmouseout="this.style.background='rgba(255,255,255,0.1)'">✕</button>
        <img id="lightboxImage" src="" alt="Photo en plein écran" style="max-width: 90%; max-height: 90vh; border-radius: 12px; box-shadow: 0 10px 40px rgba(0,0,0,0.5); transform: scale(0.9); transition: transform 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);">
    </div>

    <script>
        function openLightbox(src) {
            const modal = document.getElementById('imageLightboxModal');
            const img = document.getElementById('lightboxImage');
            img.src = src;
            modal.style.display = 'flex';
            // Force reflow
            void modal.offsetWidth;
            modal.style.opacity = '1';
            img.style.transform = 'scale(1)';
        }

        function closeLightbox() {
            const modal = document.getElementById('imageLightboxModal');
            const img = document.getElementById('lightboxImage');
            modal.style.opacity = '0';
            img.style.transform = 'scale(0.9)';
            setTimeout(() => {
                modal.style.display = 'none';
            }, 300);
        }

        // Close on click outside image
        document.getElementById('imageLightboxModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeLightbox();
            }
        });
    </script>
</body>
</html>