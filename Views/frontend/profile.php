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
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SmartPlate - Mon Profil</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../css/Template_FrontOffice.css">
    <style>
        body {
            background-color: #f8fafc;
            font-family: 'Inter', sans-serif;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
        }

        .profile-container {
            background: #ffffff;
            border-radius: 24px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.08);
            width: 100%;
            max-width: 500px;
            overflow: hidden;
            animation: fadeIn 0.6s ease-out;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .profile-header {
            background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%);
            padding: 40px 20px 60px;
            text-align: center;
            color: #ffffff;
            position: relative;
        }

        .profile-header h1 {
            margin: 0;
            font-size: 28px;
            font-weight: 700;
        }

        .profile-header p {
            margin: 10px 0 0;
            opacity: 0.8;
            font-size: 15px;
        }

        .profile-avatar {
            width: 100px;
            height: 100px;
            background: #ffffff;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 36px;
            font-weight: 700;
            color: #4f46e5;
            position: absolute;
            bottom: -50px;
            left: 50%;
            transform: translateX(-50%);
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            border: 4px solid #ffffff;
        }

        .profile-body {
            padding: 70px 40px 40px;
        }

        .info-group {
            margin-bottom: 25px;
        }

        .info-group label {
            display: block;
            font-size: 13px;
            text-transform: uppercase;
            font-weight: 600;
            color: #94a3b8;
            margin-bottom: 8px;
            letter-spacing: 0.5px;
        }

        .info-value {
            font-size: 18px;
            color: #1e293b;
            font-weight: 500;
            padding-bottom: 8px;
            border-bottom: 1px solid #e2e8f0;
        }

        .btn-logout {
            display: block;
            width: 100%;
            padding: 14px;
            background: #f1f5f9;
            color: #475569;
            text-align: center;
            border-radius: 12px;
            text-decoration: none;
            font-weight: 600;
            font-size: 16px;
            transition: all 0.2s;
            margin-top: 10px;
        }

        .btn-logout:hover {
            background: #e2e8f0;
            color: #0f172a;
        }

        .btn-primary-action {
            display: block;
            width: 100%;
            padding: 14px;
            background: #4f46e5;
            color: #ffffff;
            text-align: center;
            border-radius: 12px;
            text-decoration: none;
            font-weight: 600;
            font-size: 16px;
            transition: all 0.2s;
            margin-top: 30px;
            border: none;
            cursor: pointer;
            box-shadow: 0 4px 14px rgba(79, 70, 229, 0.4);
        }

        .btn-primary-action:hover {
            background: #4338ca;
            transform: translateY(-2px);
        }
    </style>
</head>
<body>

    <div class="profile-container">
        <div class="profile-header">
            <h1>Bienvenue, <?= htmlspecialchars($userInfo['prenom']) ?> !</h1>
            <p>Gérez vos informations personnelles ici.</p>
            <div class="profile-avatar">
                <?= strtoupper(substr($userInfo['prenom'], 0, 1) . substr($userInfo['nom'], 0, 1)) ?>
            </div>
        </div>

        <div class="profile-body">
            <div class="info-group">
                <label>Prénom</label>
                <div class="info-value"><?= htmlspecialchars($userInfo['prenom']) ?></div>
            </div>

            <div class="info-group">
                <label>Nom</label>
                <div class="info-value"><?= htmlspecialchars($userInfo['nom']) ?></div>
            </div>

            <div class="info-group">
                <label>Adresse e-mail</label>
                <div class="info-value"><?= htmlspecialchars($userInfo['email']) ?></div>
            </div>

            <a href="../backend/admin_welcome.php" class="btn-primary-action">Aller au Tableau de bord</a>
            <a href="logout.php" class="btn-logout">Se déconnecter</a>
        </div>
    </div>

    <!-- Intégration du Chatbot Assistant -->
    <?php include __DIR__ . '/chatbot.php'; ?>
</body>
</html>
