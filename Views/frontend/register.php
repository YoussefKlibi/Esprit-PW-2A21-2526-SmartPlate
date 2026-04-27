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
        
        // Fonction pour vérifier l'existence réelle de l'email (utile surtout pour Gmail)
        function verifyEmailExists($email) {
            $domain = substr(strrchr($email, "@"), 1);
            getmxrr($domain, $mxhosts);
            if (empty($mxhosts)) return false;
            
            $connect = @fsockopen($mxhosts[0], 25, $errno, $errstr, 5);
            if (!$connect) return true; // Si la connexion échoue (ex: port 25 bloqué), on laisse passer pour ne pas bloquer l'utilisateur à tort
            
            fgets($connect, 1024);
            fputs($connect, "HELO localhost\r\n");
            fgets($connect, 1024);
            fputs($connect, "MAIL FROM: <test@google.com>\r\n");
            fgets($connect, 1024);
            fputs($connect, "RCPT TO: <$email>\r\n");
            $response = fgets($connect, 1024);
            fputs($connect, "QUIT\r\n");
            fclose($connect);
            
            // Gmail retourne "550-5.1.1" si l'utilisateur n'existe pas
            if (strpos($response, '550-5.1.1') !== false || strpos($response, '550 5.1.1') !== false) {
                return false;
            }
            return true;
        }

        if ($existingUser) {
            $erreur = "Un compte avec cette adresse e-mail existe déjà sur ce site.";
        } elseif (!verifyEmailExists($email)) {
            $erreur = "Cette adresse e-mail n'existe pas. Veuillez entrer une adresse valide.";
        } else {
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            $user = new User($prenom, $nom, $email, $hashedPassword);
            
            $userC->addUser($user);
            
            // Store user identifier in session to use on profile page
            $_SESSION['user_email'] = $email;
            header("Location: dashboard.php");
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
                    <div style="position: relative;">
                        <input type="password" id="password" name="password" class="form-control" placeholder="••••••••">
                        <span onclick="togglePasswordVisibility('password', this)" style="position: absolute; right: 15px; top: 50%; transform: translateY(-50%); cursor: pointer; color: #6b7280; display: flex; align-items: center;">
                            <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>
                        </span>
                    </div>
                    <span id="errorPasswordRegister" style="color: red; font-size: 13px; display: block; margin-top: 5px;"></span>
                </div>

                <div class="form-group">
                    <label for="confirm-password">Confirmer le mot de passe</label>
                    <div style="position: relative;">
                        <input type="password" id="confirm-password" name="confirm-password" class="form-control" placeholder="••••••••">
                        <span onclick="togglePasswordVisibility('confirm-password', this)" style="position: absolute; right: 15px; top: 50%; transform: translateY(-50%); cursor: pointer; color: #6b7280; display: flex; align-items: center;">
                            <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>
                        </span>
                    </div>
                    <span id="errorConfirmPassword" style="color: red; font-size: 13px; display: block; margin-top: 5px;"></span>
                </div>

                <button type="submit" class="btn-main">Créer mon compte</button>


            </form>
            
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

            <p class="create-acc">Vous avez déjà un compte ? <a href="login.php">Se connecter</a></p>
        </div>

        <div class="login-right">
            <h2>Votre parcours<br>commence ici</h2>
            <p>Définissez vos objectifs, suivez vos repas en détail et progressez à votre rythme grâce à notre accompagnement intelligent.</p>
        </div>
    </div>
    
    <!-- Client-side validation -->
    <script src="../js/validation.js"></script>
    <!-- Intégration du Chatbot Assistant -->
    <?php include __DIR__ . '/chatbot.php'; ?>
</body>
</html>
