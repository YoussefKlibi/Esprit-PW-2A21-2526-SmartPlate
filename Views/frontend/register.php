<?php
session_start();
require_once __DIR__ . '/../../Controllers/UserController.php';

$erreur = "";
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $prenom = $_POST['prenom'] ?? '';
    $nom = $_POST['nom'] ?? '';
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm-password'] ?? '';

    if ($password !== $confirm_password) {
        $erreur = "Les mots de passe ne correspondent pas.";
    } elseif (!empty($prenom) && !empty($nom) && !empty($email) && !empty($password)) {
        
        $userC = new UserController();
        $existingUser = $userC->getUserByEmail($email);
        
        if ($existingUser) {
            $erreur = "Un compte avec cette adresse e-mail existe déjà.";
        } else {
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            $user = new User($prenom, $nom, $email, $hashedPassword);
            
            $userC->addUser($user);
            
            // Store user identifier in session to use on profile page
            $_SESSION['user_email'] = $email;
            header("Location: profile.php");
            exit();
        }
    } else {
        $erreur = "Veuillez remplir tous les champs obligatoires.";
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SmartPlate - Inscription</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <div class="login-wrapper">
        <div class="login-left">
            <a href="login.php" class="brand">
                <div class="logo-icon-login">SP</div>
                <span class="brand-name">SmartPlate</span>
            </a>

            <h1>Rejoignez-nous ! 🚀</h1>
            <p>Créez votre compte en quelques secondes.</p>

            <?php if (!empty($erreur)): ?>
                <div style="color: red; margin-bottom: 15px; text-align: center; font-weight: 500; background: #ffebee; padding: 10px; border-radius: 8px;">
                    <?php echo htmlspecialchars($erreur); ?>
                </div>
            <?php endif; ?>

            <form id="registerForm" action="register.php" method="POST" class="modern-form">
                <div class="name-row">
                    <div class="form-group">
                        <label for="prenom">Prénom</label>
                        <input type="text" id="prenom" name="prenom" class="form-control" placeholder="Jean" value="<?= $_POST['prenom'] ?? '' ?>">
                        <span id="errorPrenom" style="color: red; font-size: 13px; display: block; margin-top: 5px;"></span>
                    </div>
                    <div class="form-group">
                        <label for="nom">Nom</label>
                        <input type="text" id="nom" name="nom" class="form-control" placeholder="Dupont" value="<?= $_POST['nom'] ?? '' ?>">
                        <span id="errorNom" style="color: red; font-size: 13px; display: block; margin-top: 5px;"></span>
                    </div>
                </div>

                <div class="form-group">
                    <label for="email">Adresse mail</label>
                    <input type="text" id="email" name="email" class="form-control" placeholder="exemple@mail.com" value="<?= $_POST['email'] ?? '' ?>">
                    <span id="errorEmailRegister" style="color: red; font-size: 13px; display: block; margin-top: 5px;"></span>
                </div>

                <div class="form-group">
                    <label for="password">Mot de passe</label>
                    <input type="password" id="password" name="password" class="form-control" placeholder="••••••••">
                    <span id="errorPasswordRegister" style="color: red; font-size: 13px; display: block; margin-top: 5px;"></span>
                </div>

                <div class="form-group">
                    <label for="confirm-password">Confirmer le mot de passe</label>
                    <input type="password" id="confirm-password" name="confirm-password" class="form-control" placeholder="••••••••">
                    <span id="errorConfirmPassword" style="color: red; font-size: 13px; display: block; margin-top: 5px;"></span>
                </div>

                <button type="submit" class="btn-main">Créer mon compte</button>

                <a href="google_login.php" class="btn-secondary"
                    style="width: 100%; border-radius: 14px; padding: 1rem; display: flex; align-items: center; justify-content: center; gap: 10px; margin-top: 1rem; text-decoration: none;">
                    <svg width="20" height="20" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 48 48"><path fill="#EA4335" d="M24 9.5c3.54 0 6.71 1.22 9.21 3.6l6.85-6.85C35.9 2.38 30.47 0 24 0 14.62 0 6.51 5.38 2.56 13.22l7.98 6.19C12.43 13.72 17.74 9.5 24 9.5z"/><path fill="#4285F4" d="M46.98 24.55c0-1.57-.15-3.09-.38-4.55H24v9.02h12.94c-.58 2.96-2.26 5.48-4.78 7.18l7.73 6c4.51-4.18 7.09-10.36 7.09-17.65z"/><path fill="#FBBC05" d="M10.53 28.59c-.48-1.45-.76-2.99-.76-4.59s.27-3.14.76-4.59l-7.98-6.19C.92 16.46 0 20.12 0 24c0 3.88.92 7.54 2.56 10.78l7.97-6.19z"/><path fill="#34A853" d="M24 48c6.48 0 11.93-2.13 15.89-5.81l-7.73-6c-2.15 1.45-4.92 2.3-8.16 2.3-6.26 0-11.57-4.22-13.47-9.91l-7.98 6.19C6.51 42.62 14.62 48 24 48z"/><path fill="none" d="M0 0h48v48H0z"/></svg>
                    S'inscrire avec Google
                </a>
            </form>

            <p class="create-acc">Vous avez déjà un compte ? <a href="login.php">Se connecter</a></p>
        </div>

        <div class="login-right">
            <h2>Votre parcours<br>commence ici</h2>
            <p>Définissez vos objectifs, suivez vos repas en détail et progressez à votre rythme grâce à notre accompagnement intelligent.</p>
        </div>
    </div>
    
    <!-- Client-side validation -->
    <script src="../js/validation.js"></script>
</body>
</html>