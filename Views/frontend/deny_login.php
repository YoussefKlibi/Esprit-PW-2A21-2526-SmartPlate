<?php
session_start();
require_once __DIR__ . '/../../Controllers/UserController.php';

$message = "";
$messageType = "";

if (isset($_GET['token'])) {
    $token = $_GET['token'];
    $userC = new UserController();
    $user = $userC->getUserByValidToken($token);

    if ($user) {
        // Bloquer le compte immédiatement
        $userC->updateStatus($user['id'], 'banni');
        
        // Invalider le token pour qu'il ne soit plus utilisable
        $userC->saveResetToken($user['email'], '', date('Y-m-d H:i:s', time() - 3600));

        // Si l'utilisateur est actuellement connecté sur ce navigateur, le déconnecter
        if (isset($_SESSION['user_email']) && $_SESSION['user_email'] === $user['email']) {
            session_unset();
            session_destroy();
        }

        $messageType = "success";
        $message = "Votre compte a été bloqué et déconnecté avec succès. Par mesure de sécurité, veuillez contacter un administrateur ou réinitialiser votre mot de passe pour retrouver l'accès à votre compte.";
    } else {
        $messageType = "error";
        $message = "Ce lien a expiré ou est invalide.";
    }
} else {
    header("Location: login.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SmartPlate - Sécurisation du compte</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../css/style.css">
    <style>
        body { display: flex; align-items: center; justify-content: center; min-height: 100vh; background-color: #f8fafc; margin: 0; }
        .card { background: #fff; padding: 40px; border-radius: 18px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05); max-width: 500px; text-align: center; }
        .icon-shield { font-size: 4rem; color: #dc2626; margin-bottom: 20px; }
        .msg-box { padding: 20px; border-radius: 12px; margin-bottom: 30px; font-weight: 500; line-height: 1.5; }
        .msg-success { background: #fee2e2; color: #b91c1c; border: 1px solid #fecaca; }
        .msg-error { background: #f1f5f9; color: #475569; border: 1px solid #e2e8f0; }
    </style>
</head>
<body>

    <div class="card">
        <div class="icon-shield">🛡️</div>
        <h1 style="font-size: 1.5rem; color: #0f172a; margin-bottom: 10px;">Sécurisation de votre compte</h1>
        
        <div class="msg-box <?= $messageType == 'error' ? 'msg-error' : 'msg-success' ?>">
            <?= htmlspecialchars($message) ?>
        </div>

        <div style="display: flex; gap: 15px; justify-content: center;">
            <a href="forgot_password.php" class="btn-main" style="text-decoration: none; padding: 12px 24px;">Réinitialiser mon mot de passe</a>
            <a href="login.php" class="btn-secondary" style="text-decoration: none; padding: 12px 24px; border-radius: 12px;">Retour à l'accueil</a>
        </div>
    </div>

</body>
</html>
