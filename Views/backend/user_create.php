<?php
require_once __DIR__ . '/../../Controllers/UserController.php';

$erreur = "";
$success = "";

function verifyEmailExistence($email) {
    list($user, $domain) = explode('@', $email);
    if (!getmxrr($domain, $mxhosts)) return false;
    
    $mx = $mxhosts[0];
    $conn = @fsockopen($mx, 25, $errno, $errstr, 5);
    if (!$conn) return false;
    
    fread($conn, 1024);
    
    fwrite($conn, "HELO localhost\r\n");
    fread($conn, 1024);
    
    fwrite($conn, "MAIL FROM: <no-reply@smartplate.com>\r\n");
    fread($conn, 1024);
    
    fwrite($conn, "RCPT TO: <$email>\r\n");
    $res = fread($conn, 1024);
    
    fwrite($conn, "QUIT\r\n");
    fclose($conn);
    
    if (strpos($res, '250') === 0) {
        return true;
    }
    return false;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $prenom = $_POST['prenom'] ?? '';
    $nom = $_POST['nom'] ?? '';
    $email = $_POST['email'] ?? '';
    $password = $_POST['mot_de_passe'] ?? '';

    if (!empty($prenom) && !empty($nom) && !empty($email) && !empty($password)) {
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        
        $userC = new UserController();
        
        // Vérifier si l'email existe déjà
        $existingUser = $userC->getUserByEmail($email);
        
        if ($existingUser) {
            $erreur = "Cet email existe déjà dans la base de données. Veuillez utiliser un autre email.";
        } elseif (!verifyEmailExistence($email)) {
            $erreur = "Cette adresse e-mail n'existe pas ou est introuvable. Veuillez entrer une adresse e-mail réelle (ex: un vrai compte Gmail).";
        } else {
            $user = new User($prenom, $nom, $email, $hashedPassword);
            
            try {
                $userC->addUser($user);
                $success = "Utilisateur ajouté avec succès !";
                header("Location: user_list.php");
                exit();
            } catch (Exception $e) {
                $erreur = "Erreur lors de l'ajout : " . $e->getMessage();
            }
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
    <title>SmartPlate - Ajouter un utilisateur</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../css/Template_BackOffice.css">
    <style>
        .alert-error {
            background: #feebeb;
            color: #e74c3c;
            padding: 12px 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            border-left: 4px solid #e74c3c;
            font-weight: 500;
        }
        .alert-success {
            background: #e8f8f5;
            color: #20c997;
            padding: 12px 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            border-left: 4px solid #20c997;
            font-weight: 500;
        }
    </style>
</head>
<body style="background: #f1f5f9; display: block; padding: 2rem;">
    <div class="dashboard-container" style="max-width: 600px; margin: 0 auto;">
        <div class="card">
            <div class="card-header">
                <h2>➕ Ajouter un utilisateur</h2>
            </div>
            
            <?php if (!empty($erreur)): ?>
                <div class="alert-error">
                    ⚠️ <?= htmlspecialchars($erreur) ?>
                </div>
            <?php endif; ?>
            
            <?php if (!empty($success)): ?>
                <div class="alert-success">
                    ✅ <?= htmlspecialchars($success) ?>
                    <br><br>
                    <a href="user_list.php" class="btn-action" style="text-decoration: none; display: inline-block;">Voir la liste des utilisateurs</a>
                </div>
            <?php endif; ?>
            
            <form id="userCreateForm" method="post" action="" class="modern-form">
                <div class="form-group">
                    <label>Prénom :</label>
                    <input type="text" id="prenom" name="prenom" class="form-control" value="<?= htmlspecialchars($_POST['prenom'] ?? '') ?>">
                    <span id="errorPrenom" style="color: red; font-size: 13px; display: block; margin-top: 5px;"></span>
                </div>
                
                <div class="form-group">
                    <label>Nom :</label>
                    <input type="text" id="nom" name="nom" class="form-control" value="<?= htmlspecialchars($_POST['nom'] ?? '') ?>">
                    <span id="errorNom" style="color: red; font-size: 13px; display: block; margin-top: 5px;"></span>
                </div>
                
                <div class="form-group">
                    <label>Email :</label>
                    <input type="text" id="email" name="email" class="form-control" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
                    <span id="errorEmail" style="color: red; font-size: 13px; display: block; margin-top: 5px;"></span>
                </div>
                
                <div class="form-group">
                    <label>Mot de passe :</label>
                    <input type="password" id="mot_de_passe" name="mot_de_passe" class="form-control">
                    <span id="errorPassword" style="color: red; font-size: 13px; display: block; margin-top: 5px;"></span>
                </div>
                
                <div class="form-actions">
                    <button type="submit" class="btn-main">Créer l'utilisateur</button>
                    <a href="user_list.php" class="btn-secondary" style="text-decoration: none;">Annuler</a>
                </div>
            </form>
        </div>
    </div>
    <script src="../js/validation.js"></script>
</body>
</html>
