<?php
session_start();
require_once __DIR__ . '/../../Controllers/UserController.php';

$erreur = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';

    if (!empty($email) && !empty($password)) {
        $userC = new UserController();
        $userInfo = $userC->getUserByEmail($email);

        if ($userInfo && password_verify($password, $userInfo['mot_de_passe'])) {
            $_SESSION['user_email'] = $userInfo['email'];
            header("Location: profile.php");
            exit();
        } else {
            $erreur = "Adresse e-mail ou mot de passe incorrect.";
        }
    } else {
        $erreur = "Veuillez remplir tous les champs.";
    }
}
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SmartPlate - Connexion</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../css/style.css">
    <style>
        .error-text { color: red; font-size: 13px; margin-top: 5px; display: block; }
    </style>
</head>

<body>

    <div class="login-wrapper">
        <div class="login-left">
            <a href="#" class="brand">
                <div class="logo-icon-login">SP</div>
                <span class="brand-name">SmartPlate</span>
            </a>

            <h1>Bienvenue</h1>
            <p>Connectez-vous pour continuer vers votre espace personnel.</p>

            <?php if (!empty($erreur)): ?>
                <div style="color: red; margin-bottom: 15px; text-align: center; font-weight: 500; background: #ffebee; padding: 10px; border-radius: 8px;">
                    <?php echo htmlspecialchars($erreur); ?>
                </div>
            <?php endif; ?>

            <form id="loginForm" action="login.php" method="POST" class="modern-form">
                <div class="form-group">
                    <label for="email">Adresse mail</label>
                    <input type="text" id="email" name="email" class="form-control" placeholder="exemple@mail.com">
                    <span id="errorEmail" class="error-text"></span>
                </div>

                <div class="form-group" style="margin-top: 1rem;">
                    <label for="password">Mot de passe</label>
                    <input type="password" id="password" name="password" class="form-control" placeholder="..................">
                    <span id="errorPassword" class="error-text"></span>
                    <a href="forgot_password.php" class="forgot-pass">Mot de passe oublié ?</a>
                </div>

                <button type="submit" class="btn-main">Se connecter</button>
            </form>

            <div class="divider">ou</div>

            <a href="google_login.php" class="btn-secondary"
                style="width: 100%; border-radius: 14px; padding: 1rem; display: flex; align-items: center; justify-content: center; gap: 10px; text-decoration: none;">
                <svg width="20" height="20" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 48 48"><path fill="#EA4335" d="M24 9.5c3.54 0 6.71 1.22 9.21 3.6l6.85-6.85C35.9 2.38 30.47 0 24 0 14.62 0 6.51 5.38 2.56 13.22l7.98 6.19C12.43 13.72 17.74 9.5 24 9.5z"/><path fill="#4285F4" d="M46.98 24.55c0-1.57-.15-3.09-.38-4.55H24v9.02h12.94c-.58 2.96-2.26 5.48-4.78 7.18l7.73 6c4.51-4.18 7.09-10.36 7.09-17.65z"/><path fill="#FBBC05" d="M10.53 28.59c-.48-1.45-.76-2.99-.76-4.59s.27-3.14.76-4.59l-7.98-6.19C.92 16.46 0 20.12 0 24c0 3.88.92 7.54 2.56 10.78l7.97-6.19z"/><path fill="#34A853" d="M24 48c6.48 0 11.93-2.13 15.89-5.81l-7.73-6c-2.15 1.45-4.92 2.3-8.16 2.3-6.26 0-11.57-4.22-13.47-9.91l-7.98 6.19C6.51 42.62 14.62 48 24 48z"/><path fill="none" d="M0 0h48v48H0z"/></svg>
                Continuer avec Google
            </a>

            <div style="margin-top: 2rem; text-align: center; padding-top: 1rem; border-top: 1px solid #e2e8f0;">
                <p style="color: var(--text-gray); font-size: 0.95rem; margin-bottom: 0.8rem;">Nouveau sur SmartPlate ?
                </p>
                <a href="register.php" class="btn-secondary"
                    style="display: block; text-decoration: none; border-radius: 14px; padding: 1rem; width: 100%; box-sizing: border-box;">Créer
                    un compte maintenant</a>
            </div>
        </div>

        <div class="login-right">
            <h2>Mangez bien, <br>Vivez mieux</h2>
            <p>La première plateforme intelligente qui vous accompagne dans l'atteinte de vos objectifs nutritionnels
                jour après jour.</p>
        </div>
    </div>

    <!-- Client-side validation -->
    <script src="../js/validation.js"></script>
</body>

</html>