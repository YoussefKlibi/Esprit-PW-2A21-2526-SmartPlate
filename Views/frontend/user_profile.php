<?php
session_start();
require_once __DIR__ . '/../../Controllers/UserController.php';

if (!isset($_SESSION['user_email'])) {
    header("Location: login.php");
    exit();
}

$userC = new UserController();
$userInfo = $userC->getUserByEmail($_SESSION['user_email']);

if (!$userInfo) {
    header("Location: login.php");
    exit();
}

$success = "";
$erreur = "";

// Handle profile update
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nom = trim($_POST['nom'] ?? '');
    $prenom = trim($_POST['prenom'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $ancien_mdp = $_POST['ancien_mdp'] ?? '';
    $nouveau_mdp = $_POST['nouveau_mdp'] ?? '';

    if (empty($nom) || empty($prenom) || empty($email)) {
        $erreur = "Le nom, le prénom et l'email sont obligatoires.";
    } else {
        $hashedPassword = null;

        // Process password change if requested
        if (!empty($nouveau_mdp)) {
            if (empty($ancien_mdp)) {
                $erreur = "Veuillez saisir votre ancien mot de passe pour le modifier.";
            } elseif (!password_verify($ancien_mdp, $userInfo['mot_de_passe'])) {
                $erreur = "L'ancien mot de passe est incorrect.";
            } elseif (strlen($nouveau_mdp) < 6) {
                $erreur = "Le nouveau mot de passe doit contenir au moins 6 caractères.";
            } else {
                $hashedPassword = password_hash($nouveau_mdp, PASSWORD_DEFAULT);
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
                // Update session email if changed
                $_SESSION['user_email'] = $email;
                $success = "Profil mis à jour avec succès !";
                // Refresh user data
                $userInfo = $userC->getUserByEmail($email);
            } else {
                $erreur = "Erreur lors de la mise à jour du profil.";
            }
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
    <link rel="stylesheet" href="../css/style.css">
    <style>
        /* ===== Override body for this page ===== */
        body {
            display: flex;
            height: 100vh;
            background-color: var(--bg-light, #f4f6f9);
            overflow-x: hidden;
            overflow-y: auto;
            margin: 0;
            padding: 0;
        }

        /* ===== SIDEBAR VERTE (même style que admin_dashboard) ===== */
        .user-sidebar {
            width: 260px;
            background: linear-gradient(180deg, #20c997 0%, #12b886 100%);
            color: #ffffff;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
            flex-shrink: 0;
        }

        .sidebar-header {
            padding: 20px;
            font-size: 1.5rem;
            font-weight: 700;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .sidebar-header .sidebar-logo-icon {
            width: 40px;
            height: 40px;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 800;
            font-size: 1.1rem;
        }

        .sidebar-menu {
            padding: 20px 0;
            flex: 1;
        }

        .menu-category {
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 1px;
            padding: 10px 20px;
            color: rgba(255, 255, 255, 0.6);
            font-weight: 600;
        }

        .menu-item {
            padding: 12px 20px 12px 30px;
            display: flex;
            align-items: center;
            gap: 10px;
            color: #ffffff;
            text-decoration: none;
            transition: 0.2s;
            font-weight: 500;
            font-size: 0.95rem;
        }

        .menu-item:hover,
        .menu-item.active {
            background-color: rgba(255, 255, 255, 0.1);
            border-left: 4px solid #ffffff;
        }

        .sidebar-footer {
            padding: 20px;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
        }

        .sidebar-footer .user-badge-sidebar {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .sidebar-footer .user-avatar-sidebar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.2);
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 0.9rem;
        }

        .sidebar-footer .user-info-sidebar {
            display: flex;
            flex-direction: column;
        }

        .sidebar-footer .user-name-sidebar {
            font-weight: 600;
            font-size: 0.95rem;
        }

        .sidebar-footer .user-role-sidebar {
            font-size: 0.75rem;
            opacity: 0.7;
        }

        /* ===== CONTENU PRINCIPAL ===== */
        .main-content {
            flex: 1;
            display: flex;
            flex-direction: column;
            overflow-y: auto;
        }

        /* Topbar Blanche */
        .topbar {
            height: 70px;
            background-color: #ffffff;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.02);
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0 30px;
        }

        .topbar-title {
            font-size: 1.1rem;
            color: #2d3436;
            font-weight: 600;
        }

        .topbar-actions {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .btn-topbar {
            padding: 8px 18px;
            border-radius: 6px;
            font-weight: 600;
            font-size: 0.85rem;
            text-decoration: none;
            transition: all 0.2s;
            display: flex;
            align-items: center;
            gap: 6px;
            border: none;
            cursor: pointer;
            font-family: inherit;
        }

        .btn-topbar-accueil {
            background: #e8f8f5;
            color: #20c997;
            border: 1px solid #c3f0e0;
        }

        .btn-topbar-accueil:hover {
            background: #c3f0e0;
        }

        .btn-topbar-logout {
            background: #feebeb;
            color: #e74c3c;
            border: 1px solid #f5c6c6;
        }

        .btn-topbar-logout:hover {
            background: #f5c6c6;
        }

        .topbar-profile {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .topbar-profile img {
            width: 38px;
            height: 38px;
            border-radius: 50%;
        }

        .topbar-profile span {
            font-weight: 600;
            color: #2d3436;
            font-size: 0.9rem;
        }

        /* ===== Profile Content ===== */
        .profile-container {
            padding: 30px;
            max-width: 700px;
        }

        .page-header-profile {
            margin-bottom: 25px;
        }

        .page-header-profile h1 {
            font-size: 1.5rem;
            color: #2d3436;
            font-weight: 700;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .page-header-profile p {
            color: #636e72;
            font-size: 0.9rem;
            margin-top: 5px;
        }

        /* ===== Profile Form Card ===== */
        .profile-card {
            background: #ffffff;
            border-radius: 8px;
            padding: 30px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
            border: 1px solid #eef0f5;
        }

        .profile-form .form-group {
            margin-bottom: 20px;
        }

        .profile-form .form-group label {
            display: block;
            font-size: 0.88rem;
            font-weight: 600;
            color: #2d3436;
            margin-bottom: 6px;
        }

        .profile-form .form-control {
            width: 100%;
            padding: 12px 16px;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            color: #2d3436;
            font-size: 0.95rem;
            font-family: inherit;
            transition: all 0.3s ease;
            outline: none;
            box-sizing: border-box;
        }

        .profile-form .form-control:focus {
            border-color: #20c997;
            box-shadow: 0 0 0 3px rgba(32, 201, 151, 0.15);
            background: #ffffff;
        }

        .profile-form .form-control::placeholder {
            color: #adb5bd;
        }

        .form-divider {
            border: none;
            height: 1px;
            background: #eef0f5;
            margin: 25px 0;
        }

        .section-label {
            font-size: 0.8rem;
            text-transform: uppercase;
            letter-spacing: 0.8px;
            color: #636e72;
            font-weight: 600;
            margin-bottom: 15px;
            display: block;
        }

        /* ===== Submit Button ===== */
        .btn-submit {
            width: 100%;
            padding: 14px;
            background: linear-gradient(180deg, #20c997 0%, #12b886 100%);
            color: #ffffff;
            border: none;
            border-radius: 6px;
            font-size: 1rem;
            font-weight: 700;
            font-family: inherit;
            cursor: pointer;
            transition: all 0.2s ease;
            margin-top: 10px;
        }

        .btn-submit:hover {
            opacity: 0.9;
            transform: translateY(-1px);
            box-shadow: 0 4px 15px rgba(32, 201, 151, 0.3);
        }

        /* ===== Messages ===== */
        .alert {
            padding: 12px 16px;
            border-radius: 6px;
            margin-bottom: 20px;
            font-size: 0.9rem;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .alert-success {
            background: #e8f8f5;
            border: 1px solid #c3f0e0;
            color: #12b886;
        }

        .alert-error {
            background: #feebeb;
            border: 1px solid #f5c6c6;
            color: #e74c3c;
        }

        /* ===== Responsive ===== */
        @media (max-width: 900px) {
            body {
                flex-direction: column;
            }

            .user-sidebar {
                width: 100%;
                min-height: auto;
            }

            .sidebar-menu {
                display: flex;
                flex-wrap: wrap;
                padding: 10px;
                gap: 5px;
            }

            .menu-category {
                width: 100%;
            }

            .menu-item {
                padding: 8px 15px;
                font-size: 0.85rem;
            }

            .profile-container {
                padding: 20px 15px;
            }
        }

        @media (max-width: 600px) {
            .topbar {
                flex-direction: column;
                height: auto;
                padding: 15px;
                gap: 10px;
            }

            .profile-card {
                padding: 20px 15px;
            }
        }
    </style>
</head>
<body>

    <!-- SIDEBAR VERTE -->
    <aside class="user-sidebar">
        <div class="sidebar-header">
            <div class="sidebar-logo-icon">SP</div>
            SmartPlate
        </div>
        <div class="sidebar-menu">
            <div class="menu-category">Navigation</div>
            <a href="dashboard.php" class="menu-item" id="nav-dashboard">🏠 Tableau de bord</a>
            <a href="#" class="menu-item" id="nav-journal">📖 Journal</a>
            <a href="#" class="menu-item" id="nav-objectifs">🎯 Objectifs</a>
            <a href="#" class="menu-item" id="nav-progression">📊 Progression</a>

            <div class="menu-category" style="margin-top: 20px;">Mon Compte</div>
            <a href="user_profile.php" class="menu-item active" id="nav-profil">👤 Mon Profil</a>
            <a href="logout.php" class="menu-item" id="nav-logout">🚪 Déconnexion</a>
        </div>
        <div class="sidebar-footer">
            <div class="user-badge-sidebar">
                <div class="user-avatar-sidebar">
                    <?= strtoupper(substr($userInfo['prenom'], 0, 1) . substr($userInfo['nom'], 0, 1)) ?>
                </div>
                <div class="user-info-sidebar">
                    <span class="user-name-sidebar"><?= htmlspecialchars($userInfo['prenom'] . ' ' . $userInfo['nom']) ?></span>
                    <span class="user-role-sidebar">Utilisateur</span>
                </div>
            </div>
        </div>
    </aside>

    <!-- CONTENU PRINCIPAL -->
    <main class="main-content">

        <!-- TOPBAR -->
        <header class="topbar">
            <div class="topbar-title">👤 Mon Profil</div>
            <div class="topbar-actions">
                <a href="dashboard.php" class="btn-topbar btn-topbar-accueil" id="btn-accueil">
                    🏠 Accueil
                </a>
                <a href="logout.php" class="btn-topbar btn-topbar-logout" id="btn-logout">
                    🚪 Déconnexion
                </a>
                <div class="topbar-profile">
                    <span><?= htmlspecialchars($userInfo['prenom']) ?></span>
                    <img src="https://ui-avatars.com/api/?name=<?= urlencode($userInfo['prenom'] . '+' . $userInfo['nom']) ?>&background=20c997&color=fff" alt="Avatar">
                </div>
            </div>
        </header>

        <!-- PROFILE CONTENT -->
        <div class="profile-container">

            <div class="page-header-profile">
                <h1>👤 Mon Profil</h1>
                <p>Gérez vos informations personnelles et modifiez votre mot de passe.</p>
            </div>

            <div class="profile-card">

                <?php if (!empty($success)): ?>
                    <div class="alert alert-success" id="alert-success">
                        ✅ <?= htmlspecialchars($success) ?>
                    </div>
                <?php endif; ?>

                <?php if (!empty($erreur)): ?>
                    <div class="alert alert-error" id="alert-error">
                        ❌ <?= htmlspecialchars($erreur) ?>
                    </div>
                <?php endif; ?>

                <form action="user_profile.php" method="POST" id="profileForm" class="profile-form">
                    
                    <span class="section-label">Informations personnelles</span>

                    <div class="form-group">
                        <label for="nom">Nom</label>
                        <input type="text" id="nom" name="nom" class="form-control" 
                               value="<?= htmlspecialchars($userInfo['nom']) ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="prenom">Prénom</label>
                        <input type="text" id="prenom" name="prenom" class="form-control" 
                               value="<?= htmlspecialchars($userInfo['prenom']) ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="email">Email</label>
                        <input type="email" id="email" name="email" class="form-control" 
                               value="<?= htmlspecialchars($userInfo['email']) ?>" required>
                    </div>

                    <hr class="form-divider">

                    <span class="section-label">Modifier le mot de passe</span>

                    <div class="form-group">
                        <label for="ancien_mdp">Ancien mot de passe</label>
                        <input type="password" id="ancien_mdp" name="ancien_mdp" class="form-control" 
                               placeholder="Votre mot de passe actuel">
                    </div>

                    <div class="form-group">
                        <label for="nouveau_mdp">Nouveau mot de passe</label>
                        <input type="password" id="nouveau_mdp" name="nouveau_mdp" class="form-control" 
                               placeholder="Laissez vide pour ne pas changer">
                    </div>

                    <button type="submit" class="btn-submit" id="btn-submit">Mettre à jour</button>
                </form>
            </div>

        </div>
    </main>

</body>
</html>
