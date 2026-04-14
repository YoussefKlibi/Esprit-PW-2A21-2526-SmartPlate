<?php
require_once __DIR__ . '/../config/auth.php';

startSessionIfNeeded();

$currentUser = getCurrentUser();
if ($currentUser !== null) {
    if (($currentUser['role'] ?? '') === 'admin') {
        header('Location: back/admin_dashboard.php');
    } else {
        header('Location: index.php');
    }
    exit;
}

$pageTitle = 'SmartPlate - Connexion';
$errorMessage = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = (string) ($_POST['password'] ?? '');

    if ($email === '' || $password === '') {
        $errorMessage = 'Veuillez renseigner votre e-mail et votre mot de passe.';
    } else {
        $user = loginByCredentials($email, $password);

        if ($user === null) {
            $errorMessage = 'Identifiants invalides. Veuillez reessayer.';
        } else {
            if (($user['role'] ?? '') === 'admin') {
                header('Location: back/admin_dashboard.php');
            } else {
                header('Location: index.php');
            }
            exit;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($pageTitle, ENT_QUOTES, 'UTF-8'); ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&family=Sora:wght@600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="Template.css">
</head>
<body>
    <main>
        <section class="section section-narrow" id="connexion">
            <div class="section-head section-head-inline">
                <div>
                    <p class="section-tag">Authentification</p>
                    <h2>Connexion a SmartPlate</h2>
                    <p class="form-intro">Connectez-vous pour acceder a vos reclamations.</p>
                </div>
                <a href="index.php" class="btn btn-light">Retour a l'accueil</a>
            </div>

            <?php if ($errorMessage !== null): ?>
                <p class="alert alert-error"><?php echo htmlspecialchars($errorMessage, ENT_QUOTES, 'UTF-8'); ?></p>
            <?php endif; ?>

            <form action="login.php" method="post" class="reclamation-form login-form" novalidate>
                <label class="field">
                    <span>Adresse e-mail</span>
                    <input
                        type="email"
                        name="email"
                        value="<?php echo htmlspecialchars($_POST['email'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
                        required
                    >
                </label>

                <label class="field">
                    <span>Mot de passe</span>
                    <input
                        type="password"
                        name="password"
                        required
                    >
                </label>

                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">Se connecter</button>
                </div>
            </form>
        </section>
    </main>
</body>
</html>
