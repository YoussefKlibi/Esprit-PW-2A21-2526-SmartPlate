<?php
session_start();
require_once __DIR__ . '/../../Controllers/UserController.php';

$message = "";
$messageType = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'] ?? '';

    if (!empty($email)) {
        $userC = new UserController();
        $user = $userC->getUserByEmail($email);
        
        if ($user) {
            // Générer un token unique
            $token = bin2hex(random_bytes(32));
            // Date limite : 1 heure
            $expires = date("Y-m-d H:i:s", time() + 3600);
            
            $userC->saveResetToken($email, $token, $expires);
            
            // 1. Envoi par vrai email (XAMPP va le stocker dans C:\xampp\mailoutput)
            $resetLink = "http://" . $_SERVER['HTTP_HOST'] . str_replace("forgot_password.php", "reset_password.php", $_SERVER['REQUEST_URI']) . "?token=" . $token;
            
            $sujet = "Réinitialisation de votre mot de passe - SmartPlate";
            $messageMail = "Bonjour,\n\nVous avez demandé la réinitialisation de votre mot de passe.\n\n";
            $messageMail .= "Veuillez cliquer sur ce lien sécurisé pour choisir un nouveau mot de passe :\n";
            $messageMail .= $resetLink . "\n\n";
            
            $headers = "From: noreply@smartplate.local";
            @mail($email, $sujet, $messageMail, $headers);
            
            // Affichage strictement professionnel : aucun bouton local
            $messageType = "success";
            $message = "Un lien de réinitialisation sécurisé vient de vous être envoyé.<br>Veuillez vérifier votre boîte de réception (ainsi que vos spams).";
        } else {
            // Sécurité anti-divulgation : même message si l'email n'existe pas dans un cadre PRO
            $messageType = "error";
            $message = "Aucun compte n'est associé à cette adresse e-mail dans la base de données.";
        }
    } else {
        $messageType = "error";
        $message = "Veuillez entrer une adresse e-mail valide.";
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SmartPlate - Mot de passe oublié</title>
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

            <h1>Mot de passe oublié ? 🔒</h1>
            <p>Entrez votre adresse e-mail ci-dessous et nous vous enverrons un lien d'accès sécurisé pour réinitialiser votre mot de passe.</p>

            <?php if (!empty($message)): ?>
                <div class="msg-box <?= $messageType == 'error' ? 'msg-error' : 'msg-success' ?>">
                    <?= $message ?>
                </div>
            <?php endif; ?>

            <?php if ($messageType != 'success'): ?>
            <form id="forgotForm" action="forgot_password.php" method="POST" class="modern-form">
                <div class="form-group">
                    <label for="email">Adresse e-mail</label>
                    <input type="text" id="emailForgot" name="email" class="form-control" placeholder="exemple@mail.com">
                    <span id="errorEmailForgot" class="error-text"></span>
                </div>

                <button type="submit" class="btn-main" style="margin-top: 20px;">Envoyer le lien de réinitialisation</button>
            </form>
            <?php endif; ?>

            <div style="margin-top: 2rem; text-align: center; padding-top: 1rem; border-top: 1px solid #e2e8f0;">
                <p style="color: var(--text-gray); font-size: 0.95rem; margin-bottom: 0.8rem;">Vous vous souvenez de votre mot de passe ?</p>
                <a href="login.php" class="btn-secondary" style="display: block; text-decoration: none; border-radius: 14px; padding: 1rem; width: 100%; box-sizing: border-box;">Retourner à la connexion</a>
            </div>
        </div>

        <div class="login-right">
            <h2>Sécurité avant tout</h2>
            <p>Vos données sont protégées. Ne partagez jamais votre lien de réinitialisation de mot de passe avec une tierce personne.</p>
        </div>
    </div>

    <!-- Client-side validation -->
    <script src="../js/validation.js"></script>
</body>
</html>
