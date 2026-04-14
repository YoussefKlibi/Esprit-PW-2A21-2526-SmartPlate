<?php
include '../../Controllers/UserController.php';

$userC = new UserController();

// Vérifier si un ID est passé en GET
$id = $_GET['id'] ?? null;
if (!$id) {
    header("Location: user_list.php");
    exit();
}

// Si le formulaire est soumis pour mise à jour
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nom = $_POST['nom'] ?? '';
    $email = $_POST['email'] ?? '';

    if ($nom !== '' && $email !== '') {
        $userC->updateUser($id, $nom, $email);
        header("Location: user_list.php");
        exit();
    } else {
        $erreur = "Erreur lors de la modification.";
    }
} else {
    // Récupérer les informations actuelles de l'utilisateur
    $user = $userC->getUserById($id);
    if (!$user) {
        die("Utilisateur non trouvé.");
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Modifier un utilisateur</title>
</head>
<body>
    <h2>Modifier l'Utilisateur</h2>
    <?php if (isset($erreur)) echo "<p style='color:red;'>$erreur</p>"; ?>
    <form id="userUpdateForm" method="post" action="">
        <label>Nom :</label><br>
        <input type="text" id="update_nom" name="nom" value="<?= htmlspecialchars($user['nom']) ?>"><br>
        <span id="errorUpdateNom" style="color:red; font-size:12px;"></span><br>
        
        <label>Email :</label><br>
        <input type="text" id="update_email" name="email" value="<?= htmlspecialchars($user['email']) ?>"><br>
        <span id="errorUpdateEmail" style="color:red; font-size:12px;"></span><br>
        
        <button type="submit">Enregistrer les modifications</button>
    </form>
    <br>
    <a href="user_list.php">Retour à la liste</a>
    <script src="../js/validation.js"></script>
</body>
</html>
