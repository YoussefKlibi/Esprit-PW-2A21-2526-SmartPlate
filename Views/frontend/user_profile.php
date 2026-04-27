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
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SmartPlate - Mon Profil</title>
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
            <span class="nav-section-title">Navigation</span>
            <a href="dashboard.php" class="nav-item" id="nav-dashboard">
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
            <a href="user_profile.php" class="nav-item active" id="nav-profil">
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
                    <span class="user-status">Connecté</span>
                </div>
            </div>
        </div>
    </aside>

    <!-- CONTENU PRINCIPAL -->
    <div class="dashboard">
        
        <header class="dashboard-header">
            <h1>Mon Profil</h1>
            <div class="journal-actions-right">
                <a href="dashboard.php" class="btn-icon">🏠 Accueil</a>
                <a href="logout.php" class="btn-icon danger" onclick="return confirm('Voulez-vous vraiment vous déconnecter ?');">🚪 Déconnexion</a>
            </div>
        </header>

        <p style="color: var(--text-gray); margin-bottom: 2rem;">Gérez vos informations personnelles et modifiez votre mot de passe.</p>

        <div class="journal-grid">
            <div class="form-section">
                <div class="card">
                    <div class="card-header">
                        <h2>Informations du compte</h2>
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

                    <form action="user_profile.php" method="POST" id="profileForm" class="modern-form" novalidate>
                        
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

    <script>
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
    </script>
    
    <!-- La validation se fait via le fichier externe validation.js -->
    <script src="../js/validation.js"></script>
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
    <!-- Intégration du Chatbot Assistant -->
    <?php include __DIR__ . '/chatbot.php'; ?>
</body>
</html>