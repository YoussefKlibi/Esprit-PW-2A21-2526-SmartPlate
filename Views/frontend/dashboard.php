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
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SmartPlate - Tableau de bord</title>
    <meta name="description" content="SmartPlate - Votre espace personnel pour gérer vos objectifs nutritionnels.">
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

        .topbar-welcome {
            font-size: 1rem;
            color: #2d3436;
            font-weight: 500;
        }

        .topbar-welcome .wave-emoji {
            display: inline-block;
            animation: wave 2.5s infinite;
            transform-origin: 70% 70%;
        }

        @keyframes wave {
            0% { transform: rotate(0deg); }
            10% { transform: rotate(14deg); }
            20% { transform: rotate(-8deg); }
            30% { transform: rotate(14deg); }
            40% { transform: rotate(-4deg); }
            50% { transform: rotate(10deg); }
            60% { transform: rotate(0deg); }
            100% { transform: rotate(0deg); }
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

        .btn-topbar-profil {
            background: #e8f8f5;
            color: #20c997;
            border: 1px solid #c3f0e0;
        }

        .btn-topbar-profil:hover {
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

        /* ===== Dashboard Content ===== */
        .dashboard-container {
            padding: 30px;
        }

        .page-header {
            margin-bottom: 30px;
        }

        .page-header h1 {
            font-size: 1.5rem;
            color: #2d3436;
            font-weight: 700;
        }

        .page-header p {
            color: #636e72;
            font-size: 0.9rem;
            margin-top: 5px;
        }

        /* ===== Feature Cards Grid ===== */
        .features-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 20px;
        }

        .feature-card {
            background: #ffffff;
            border-radius: 8px;
            padding: 25px 20px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
            border: 1px solid #eef0f5;
            text-align: center;
            cursor: pointer;
            transition: all 0.25s ease;
            text-decoration: none;
            display: block;
        }

        .feature-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.1);
            border-color: #20c997;
        }

        .card-icon {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 15px;
            font-size: 1.5rem;
            transition: all 0.25s ease;
        }

        .card-icon.green {
            background: #e8f8f5;
            color: #20c997;
        }

        .card-icon.yellow {
            background: #fff8e1;
            color: #f1c40f;
        }

        .card-icon.blue {
            background: #e3f2fd;
            color: #3498db;
        }

        .card-icon.red {
            background: #feebeb;
            color: #e74c3c;
        }

        .card-icon.purple {
            background: #f3e5f5;
            color: #9b59b6;
        }

        .feature-card h3 {
            font-size: 1rem;
            font-weight: 700;
            color: #2d3436;
            margin-bottom: 6px;
        }

        .feature-card p {
            font-size: 0.85rem;
            color: #636e72;
            font-weight: 500;
            line-height: 1.4;
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

            .features-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (max-width: 600px) {
            .features-grid {
                grid-template-columns: 1fr;
            }

            .topbar {
                flex-direction: column;
                height: auto;
                padding: 15px;
                gap: 10px;
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
            <a href="dashboard.php" class="menu-item active" id="nav-dashboard">🏠 Tableau de bord</a>
            <a href="#" class="menu-item" id="nav-journal">📖 Journal</a>
            <a href="#" class="menu-item" id="nav-objectifs">🎯 Objectifs</a>
            <a href="#" class="menu-item" id="nav-progression">📊 Progression</a>

            <div class="menu-category" style="margin-top: 20px;">Mon Compte</div>
            <a href="user_profile.php" class="menu-item" id="nav-profil">👤 Mon Profil</a>
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
            <div class="topbar-welcome">
                <span class="wave-emoji">👋</span> Bienvenue, <strong><?= htmlspecialchars($userInfo['prenom']) ?></strong>
            </div>
            <div class="topbar-actions">
                <a href="user_profile.php" class="btn-topbar btn-topbar-profil" id="btn-profil">
                    👤 Profil
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

        <!-- DASHBOARD CONTENT -->
        <div class="dashboard-container">

            <div class="page-header">
                <h1>Bienvenue <?= htmlspecialchars($userInfo['prenom']) ?> 👋</h1>
                <p>Voici votre espace personnel. Accédez à toutes les fonctionnalités de SmartPlate.</p>
            </div>

            <div class="features-grid">
                <!-- Card: Journal Alimentaire -->
                <a href="#" class="feature-card" id="card-journal">
                    <div class="card-icon yellow">📖</div>
                    <h3>Journal</h3>
                    <p>Suivre vos repas quotidiens.</p>
                </a>

                <!-- Card: Objectifs -->
                <a href="#" class="feature-card" id="card-objectifs">
                    <div class="card-icon green">🎯</div>
                    <h3>Objectifs</h3>
                    <p>Définir vos objectifs nutritionnels.</p>
                </a>

                <!-- Card: Progression -->
                <a href="#" class="feature-card" id="card-progression">
                    <div class="card-icon blue">📊</div>
                    <h3>Progression</h3>
                    <p>Visualiser vos statistiques.</p>
                </a>

                <!-- Card: Profil -->
                <a href="user_profile.php" class="feature-card" id="card-profil">
                    <div class="card-icon purple">👤</div>
                    <h3>Profil</h3>
                    <p>Modifier vos informations.</p>
                </a>

                <!-- Card: Recettes -->
                <a href="#" class="feature-card" id="card-recettes">
                    <div class="card-icon red">🍽️</div>
                    <h3>Recettes</h3>
                    <p>Découvrir des idées de repas.</p>
                </a>
            </div>

        </div>
    </main>

</body>
</html>
