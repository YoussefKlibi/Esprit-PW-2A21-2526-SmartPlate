<?php
session_start();
require_once __DIR__ . '/../../Controllers/UserController.php';

$message = "";
$messageType = "";
$tokenValid = false;

$token = $_GET['token'] ?? ($_POST['token'] ?? '');

if (empty($token)) {
    $messageType = "error";
    $message = "Lien de sécurité manquant ou invalide. Veuillez réessayer.";
} else {
    $userC = new UserController();
    $user = $userC->getUserByToken($token);
    
    if (!$user) {
        $messageType = "error";
        $message = "Ce lien de réinitialisation est introuvable, déjà utilisé, ou a expiré. Veuillez refaire une requête.";
    } else {
        $tokenValid = true;
        
        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            $password = $_POST['password'] ?? '';
            $confirm_password = $_POST['confirm-password'] ?? '';
            
            if (empty($password) || empty($confirm_password)) {
                $messageType = "error";
                $message = "Veuillez remplir tous les champs.";
            } elseif ($password !== $confirm_password) {
                $messageType = "error";
                $message = "Les mots de passe ne correspondent pas.";
            } elseif (strlen($password) < 6) {
                $messageType = "error";
                $message = "Le mot de passe doit faire au moins 6 caractères.";
            } else {
                $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                $userC->updatePassword($user['id'], $hashedPassword);
                
                $messageType = "success";
                $message = "Votre mot de passe a été réinitialisé avec succès !<br><br>";
                $message .= "<a href='login.php' class='btn-main' style='display:inline-block; margin-top:10px; width:100%'>Se connecter avec le nouveau mot de passe</a>";
                $tokenValid = false; // Hide form
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
    <title>SmartPlate - Réinitialiser le mot de passe</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../css/style.css">
    <style>
        .error-text { color: red; font-size: 13px; margin-top: 5px; display: block; }
        .msg-box { padding: 15px; border-radius: 8px; margin-bottom: 20px; text-align: center; font-weight: 500; }
        .msg-error { background: #ffebee; color: red; }
        .msg-success { background: #e8f5e9; color: #2e7d32; }
    </style>
</head>
<body>

    <div class="login-wrapper">
        <div class="login-left">
            <a href="login.php" class="brand">
                <div class="logo-icon-login">SP</div>
                <span class="brand-name">SmartPlate</span>
            </a>

            <h1>Nouveau mot de passe 🔒</h1>
            <p>Veuillez entrer votre nouveau mot de passe ci-dessous.</p>

            <?php if (!empty($message)): ?>
                <div class="msg-box <?= $messageType == 'error' ? 'msg-error' : 'msg-success' ?>">
                    <?= $message ?>
                </div>
            <?php endif; ?>

            <?php if ($tokenValid): ?>
            <form id="resetForm" action="reset_password.php" method="POST" class="modern-form">
                <input type="hidden" name="token" value="<?= htmlspecialchars($token) ?>">
                
                <div class="form-group">
                    <label for="password">Nouveau mot de passe</label>
                    <input type="password" id="passwordReset" name="password" class="form-control" placeholder="••••••••">
                    <span id="errorPasswordReset" class="error-text"></span>
                </div>

                <div class="form-group">
                    <label for="confirm-password">Confirmer le nouveau mot de passe</label>
                    <input type="password" id="confirmPasswordReset" name="confirm-password" class="form-control" placeholder="••••••••">
                    <span id="errorConfirmReset" class="error-text"></span>
                </div>

                <button type="submit" class="btn-main" style="margin-top: 20px;">Sauvegarder le mot de passe</button>
            </form>
            <?php endif; ?>

            <?php if (!$tokenValid && $messageType == 'error'): ?>
            <div style="margin-top: 2rem; text-align: center; padding-top: 1rem; border-top: 1px solid #e2e8f0;">
                <a href="forgot_password.php" class="btn-secondary" style="display: block; text-decoration: none; border-radius: 14px; padding: 1rem; width: 100%; box-sizing: border-box;">Demander un nouveau lien</a>
            </div>
            <?php endif; ?>
        </div>

        <div class="login-right">
            <h2>Ne l'oubliez plus !</h2>
            <p>Utilisez un gestionnaire de mots de passe pour sécuriser vos accès et ne plus jamais perdre vos informations sensibles.</p>
        </div>
    </div>

    <!-- Client-side validation -->
    <script src="../js/validation.js"></script>
</body>
</html>
