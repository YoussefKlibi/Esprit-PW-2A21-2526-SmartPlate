<?php
require_once __DIR__ . '/../../Controllers/UserController.php';

$userC = new UserController();

// Vérifier si un ID est passé en GET
$id = $_GET['id'] ?? null;
if (!$id) {
    header("Location: user_list.php");
    exit();
}

$erreur = "";
$success = "";

// Récupérer les informations actuelles de l'utilisateur (dès le début)
$user = $userC->getUserById($id);
if (!$user) {
    die("Utilisateur non trouvé.");
}

// Si le formulaire est soumis pour mise à jour
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $prenom = trim($_POST['prenom'] ?? '');
    $nom = trim($_POST['nom'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $mot_de_passe = $_POST['mot_de_passe'] ?? '';

    // Mettre à jour les données locales pour réafficher dans le formulaire si erreur
    $user['prenom'] = $prenom;
    $user['nom'] = $nom;
    $user['email'] = $email;

    if ($prenom !== '' && $nom !== '' && $email !== '') {
        $hashedPassword = null;
        if (!empty($mot_de_passe)) {
            $hashedPassword = password_hash($mot_de_passe, PASSWORD_DEFAULT);
        }
        
        $result = $userC->updateUserProfile($id, $nom, $prenom, $email, $hashedPassword);
        if ($result) {
            header("Location: user_list.php");
            exit();
        } else {
            $erreur = "Erreur lors de la mise à jour.";
        }
    } else {
        $erreur = "Veuillez remplir les champs obligatoires (Prénom, Nom, Email).";
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SmartPlate - Modifier un utilisateur</title>
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
                <h2>✏️ Modifier l'utilisateur</h2>
            </div>
            
            <?php if (!empty($erreur)): ?>
                <div class="alert-error">
                    ⚠️ <?= htmlspecialchars($erreur) ?>
                </div>
            <?php endif; ?>
            
            <form id="userUpdateForm" method="post" action="" class="modern-form">
                <div class="form-group">
                    <label>Prénom :</label>
                    <input type="text" id="prenom" name="prenom" class="form-control" value="<?= htmlspecialchars($user['prenom'] ?? '') ?>">
                    <span id="errorPrenom" style="color: red; font-size: 13px; display: block; margin-top: 5px;"></span>
                </div>
                
                <div class="form-group">
                    <label>Nom :</label>
                    <input type="text" id="nom" name="nom" class="form-control" value="<?= htmlspecialchars($user['nom'] ?? '') ?>">
                    <span id="errorNom" style="color: red; font-size: 13px; display: block; margin-top: 5px;"></span>
                </div>
                
                <div class="form-group">
                    <label>Email :</label>
                    <input type="text" id="email" name="email" class="form-control" value="<?= htmlspecialchars($user['email'] ?? '') ?>">
                    <span id="errorEmail" style="color: red; font-size: 13px; display: block; margin-top: 5px;"></span>
                </div>
                
                <div class="form-group">
                    <label>Nouveau mot de passe <small>(laisser vide pour ne pas modifier)</small> :</label>
                    <input type="password" id="mot_de_passe" name="mot_de_passe" class="form-control" placeholder="••••••••">
                    <span id="errorPassword" style="color: red; font-size: 13px; display: block; margin-top: 5px;"></span>
                </div>
                
                <div class="form-actions">
                    <button type="submit" class="btn-main">Enregistrer les modifications</button>
                    <a href="user_list.php" class="btn-secondary" style="text-decoration: none;">Annuler</a>
                </div>
            </form>
        </div>
    </div>
    <script src="../js/validation.js"></script>
    <!-- Intégration du Chatbot Assistant -->
    <?php include __DIR__ . '/../frontend/chatbot.php'; ?>
</body>
</html>
