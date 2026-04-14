<?php
require_once __DIR__ . '/../../Controllers/ProfilController.php';
require_once __DIR__ . '/../../Controllers/UserController.php';

$erreur = "";
$success = "";

$userC = new UserController();
$listeUsers = $userC->listeUsers();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $titre = $_POST['titre'] ?? '';
    $description = $_POST['description'] ?? '';
    $id_utilisateur = $_POST['id_utilisateur'] ?? '';

    if (!empty($titre) && !empty($description) && !empty($id_utilisateur)) {
        
        $profilC = new ProfilController();
        $profil = new Profil($titre, $description, (int)$id_utilisateur);
        
        try {
            $profilC->addProfil($profil);
            $success = "Profil ajouté avec succès !";
            header("Location: profil_list.php");
            exit();
        } catch (Exception $e) {
            $erreur = "Erreur lors de l'ajout : " . $e->getMessage();
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
    <title>SmartPlate - Ajouter un profil</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../css/style.css">
</head>
<body style="background: #f1f5f9; display: block; padding: 2rem;">
    <div class="dashboard-container" style="max-width: 600px; margin: 0 auto;">
        <div class="card">
            <div class="card-header">
                <h2>➕ Ajouter un profil</h2>
            </div>
            
            <?php if (!empty($erreur)): ?>
                <div style="background: #feebeb; color: #e74c3c; padding: 12px; border-radius: 8px; margin-bottom: 20px;">
                    ⚠️ <?= htmlspecialchars($erreur) ?>
                </div>
            <?php endif; ?>
            
            <form id="profilCreateForm" method="post" action="" class="modern-form">
                <div class="form-group">
                    <label>Titre :</label>
                    <input type="text" id="profil_titre" name="titre" class="form-control" value="<?= htmlspecialchars($_POST['titre'] ?? '') ?>">
                    <span id="errorProfilTitre" style="color: red; font-size: 13px; display: block; margin-top: 5px;"></span>
                </div>
                
                <div class="form-group">
                    <label>Description :</label>
                    <textarea id="profil_description" name="description" class="form-control" rows="4"><?= htmlspecialchars($_POST['description'] ?? '') ?></textarea>
                    <span id="errorProfilDesc" style="color: red; font-size: 13px; display: block; margin-top: 5px;"></span>
                </div>
                
                <div class="form-group">
                    <label>Utilisateur rattaché :</label>
                    <select id="profil_utilisateur" name="id_utilisateur" class="form-control">
                        <option value="">Sélectionnez un utilisateur...</option>
                        <?php foreach($listeUsers as $unUser): ?>
                            <option value="<?= $unUser['id'] ?>" <?= (isset($_POST['id_utilisateur']) && $_POST['id_utilisateur'] == $unUser['id']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($unUser['prenom'] . ' ' . $unUser['nom']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <span id="errorProfilUser" style="color: red; font-size: 13px; display: block; margin-top: 5px;"></span>
                </div>
                
                <div class="form-actions">
                    <button type="submit" class="btn-main">Créer le profil</button>
                    <a href="profil_list.php" class="btn-secondary" style="text-decoration: none;">Annuler</a>
                </div>
            </form>
        </div>
    </div>
    
    <script>
    // JS Validation strict (sans HTML5)
    document.addEventListener('DOMContentLoaded', function () {
        const profilCreateForm = document.getElementById('profilCreateForm');
        if (profilCreateForm) {
            profilCreateForm.addEventListener('submit', function (e) {
                let isValid = true;
                
                const titleInfo = { el: document.getElementById('profil_titre'), err: document.getElementById('errorProfilTitre'), msg: 'Le titre est obligatoire.' };
                const descInfo = { el: document.getElementById('profil_description'), err: document.getElementById('errorProfilDesc'), msg: 'La description est obligatoire.' };
                const userInfo = { el: document.getElementById('profil_utilisateur'), err: document.getElementById('errorProfilUser'), msg: 'Veuillez sélectionner un utilisateur.' };

                [titleInfo, descInfo, userInfo].forEach(function (info) {
                    info.err.textContent = '';
                    if (info.el.value.trim() === '') {
                        info.err.textContent = info.msg;
                        isValid = false;
                    }
                });

                if (!isValid) e.preventDefault();
            });
        }
    });
    </script>
</body>
</html>
