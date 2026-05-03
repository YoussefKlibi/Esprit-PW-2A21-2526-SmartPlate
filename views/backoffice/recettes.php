<?php
require_once "../../config.php";

/* ===================== OUTILS VALIDATION PHP ===================== */

function nettoyer($val){
    return trim($val ?? '');
}

function estNomValide($val){
    return preg_match("/^[A-Za-zÀ-ÿ0-9' -]{2,100}$/u", $val);
}

function estNombreDansIntervalle($val, $min, $max){
    return filter_var($val, FILTER_VALIDATE_INT) !== false && $val >= $min && $val <= $max;
}

function estNombreDecimalValide($val, $min, $max){
    return is_numeric($val) && $val >= $min && $val <= $max;
}

function categorieValide($cat){
    $categories = ["Healthy","Salade","Pate","Dessert"];
    return in_array($cat, $categories);
}

function traiterImageRecette($fileInput, $ancienNom = null){
    if(!isset($fileInput) || !is_array($fileInput)){
        return ['ok' => true, 'image' => $ancienNom, 'message' => ''];
    }

    if($fileInput['error'] === UPLOAD_ERR_NO_FILE){
        return ['ok' => true, 'image' => $ancienNom, 'message' => ''];
    }

    if($fileInput['error'] !== UPLOAD_ERR_OK){
        return ['ok' => false, 'image' => $ancienNom, 'message' => 'Erreur lors du telechargement de l image'];
    }

    $extensionsAutorisees = ['jpg', 'jpeg', 'png', 'webp'];
    $mimeAutorises = ['image/jpeg', 'image/png', 'image/webp'];

    $nomOriginal = $fileInput['name'];
    $tmp = $fileInput['tmp_name'];
    $extension = strtolower(pathinfo($nomOriginal, PATHINFO_EXTENSION));

    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime = finfo_file($finfo, $tmp);
    finfo_close($finfo);

    if(!in_array($extension, $extensionsAutorisees)){
        return ['ok' => false, 'image' => $ancienNom, 'message' => 'Extension invalide. Formats autorises : jpg, jpeg, png, webp'];
    }

    if(!in_array($mime, $mimeAutorises)){
        return ['ok' => false, 'image' => $ancienNom, 'message' => 'Type de fichier invalide'];
    }

    $nouveauNom = 'recette_' . time() . '_' . mt_rand(1000,9999) . '.' . $extension;
    $destination = "../../images/" . $nouveauNom;

    if(!move_uploaded_file($tmp, $destination)){
        return ['ok' => false, 'image' => $ancienNom, 'message' => 'Impossible d enregistrer l image'];
    }

    return ['ok' => true, 'image' => $nouveauNom, 'message' => ''];
}

/* ===================== ETAT EDITION ===================== */

$editMode = false;
$recetteEdit = null;
$selectedIngredients = [];

/* ===================== CHARGER INGREDIENTS ===================== */

$ingredients = $pdo->query("SELECT * FROM ingredient ORDER BY nom_ingredient ASC")->fetchAll(PDO::FETCH_ASSOC);

/* ===================== AJOUTER RECETTE ===================== */

if(isset($_POST['ajouter'])){

    $nom         = nettoyer($_POST['nom_recette']);
    $description = nettoyer($_POST['description']);
    $calories    = nettoyer($_POST['calories']);
    $proteines   = nettoyer($_POST['proteines']);
    $lipides     = nettoyer($_POST['lipides']);
    $glucides    = nettoyer($_POST['glucides']);
    $temps       = nettoyer($_POST['temps_preparation']);
    $categorie   = nettoyer($_POST['categorie']);
    $ingredientsPost = $_POST['ingredients'] ?? [];

    $imageResult = traiterImageRecette($_FILES['image'] ?? null, null);
    $image = $imageResult['image'];
    $imageOk = $imageResult['ok'];

    if(
        estNomValide($nom) &&
        strlen($description) >= 10 &&
        strlen($description) <= 500 &&
        estNombreDecimalValide($calories, 0, 10000) &&
        estNombreDecimalValide($proteines, 0, 5000) &&
        estNombreDecimalValide($lipides, 0, 5000) &&
        estNombreDecimalValide($glucides, 0, 5000) &&
        estNombreDansIntervalle($temps, 1, 600) &&
        categorieValide($categorie) &&
        !empty($ingredientsPost) &&
        $imageOk
    ){
        $sql = "INSERT INTO recette
                (nom_recette, description, calories, proteines, lipides, glucides, temps_preparation, categorie, image)
                VALUES(?,?,?,?,?,?,?,?,?)";

        $pdo->prepare($sql)->execute([
            $nom, $description, $calories, $proteines, $lipides, $glucides, $temps, $categorie, $image
        ]);

        $recette_id = $pdo->lastInsertId();

        foreach($ingredientsPost as $ing){
            $pdo->prepare("INSERT INTO recette_ingredient(recette_id, ingredient_id, quantite) VALUES(?,?,?)")
                ->execute([$recette_id, (int)$ing, 1]);
        }
    }

    header("Location: recettes.php");
    exit();
}

/* ===================== SUPPRIMER RECETTE ===================== */

if(isset($_GET['delete'])){
    $id = (int) $_GET['delete'];

    $pdo->prepare("DELETE FROM recette_ingredient WHERE recette_id=?")->execute([$id]);
    $pdo->prepare("DELETE FROM recette WHERE id_recette=?")->execute([$id]);

    header("Location: recettes.php");
    exit();
}

/* ===================== EDIT RECETTE ===================== */

if(isset($_GET['edit'])){
    $editMode = true;
    $id = (int) $_GET['edit'];

    $q = $pdo->prepare("SELECT * FROM recette WHERE id_recette=?");
    $q->execute([$id]);
    $recetteEdit = $q->fetch(PDO::FETCH_ASSOC);

    $q2 = $pdo->prepare("SELECT ingredient_id FROM recette_ingredient WHERE recette_id=?");
    $q2->execute([$id]);

    foreach($q2 as $r){
        $selectedIngredients[] = $r['ingredient_id'];
    }
}

/* ===================== MODIFIER RECETTE ===================== */

if(isset($_POST['modifier'])){

    $id          = (int) $_POST['id_recette'];
    $nom         = nettoyer($_POST['nom_recette']);
    $description = nettoyer($_POST['description']);
    $calories    = nettoyer($_POST['calories']);
    $proteines   = nettoyer($_POST['proteines']);
    $lipides     = nettoyer($_POST['lipides']);
    $glucides    = nettoyer($_POST['glucides']);
    $temps       = nettoyer($_POST['temps_preparation']);
    $categorie   = nettoyer($_POST['categorie']);
    $ingredientsPost = $_POST['ingredients'] ?? [];

    $ancienneImage = nettoyer($_POST['ancienne_image'] ?? '');
    $imageResult = traiterImageRecette($_FILES['image'] ?? null, $ancienneImage);
    $image = $imageResult['image'];
    $imageOk = $imageResult['ok'];

    $validationBase =
        estNomValide($nom) &&
        strlen($description) >= 10 &&
        strlen($description) <= 500 &&
        estNombreDecimalValide($calories, 0, 10000) &&
        estNombreDecimalValide($proteines, 0, 5000) &&
        estNombreDecimalValide($lipides, 0, 5000) &&
        estNombreDecimalValide($glucides, 0, 5000) &&
        estNombreDansIntervalle($temps, 1, 600) &&
        categorieValide($categorie) &&
        !empty($ingredientsPost) &&
        $imageOk;

    if($validationBase){

        $sql = "UPDATE recette SET
                nom_recette=?,
                description=?,
                calories=?,
                proteines=?,
                lipides=?,
                glucides=?,
                temps_preparation=?,
                categorie=?,
                image=?
                WHERE id_recette=?";

        $pdo->prepare($sql)->execute([
            $nom, $description, $calories, $proteines, $lipides, $glucides, $temps, $categorie, $image, $id
        ]);

        $pdo->prepare("DELETE FROM recette_ingredient WHERE recette_id=?")->execute([$id]);

        foreach($ingredientsPost as $ing){
            $pdo->prepare("INSERT INTO recette_ingredient(recette_id, ingredient_id, quantite) VALUES(?,?,?)")
                ->execute([$id, (int)$ing, 1]);
        }
    }

    header("Location: recettes.php");
    exit();
}

/* ===================== TRI + CHARGEMENT RECETTES ===================== */

$sortRecette = nettoyer($_GET['sort_recette'] ?? 'recent');

$sqlRecettes = "SELECT * FROM recette";

switch($sortRecette){
    case 'nom_asc':
        $sqlRecettes .= " ORDER BY nom_recette ASC";
        break;
    case 'nom_desc':
        $sqlRecettes .= " ORDER BY nom_recette DESC";
        break;
    case 'calories_asc':
        $sqlRecettes .= " ORDER BY calories ASC";
        break;
    case 'calories_desc':
        $sqlRecettes .= " ORDER BY calories DESC";
        break;
    case 'temps_asc':
        $sqlRecettes .= " ORDER BY temps_preparation ASC";
        break;
    case 'temps_desc':
        $sqlRecettes .= " ORDER BY temps_preparation DESC";
        break;
    default:
        $sqlRecettes .= " ORDER BY id_recette DESC";
        break;
}

$recettes = $pdo->query($sqlRecettes)->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Gestion des recettes</title>
<link rel="stylesheet" href="templates/templateback.css">
</head>

<body>

<div class="admin-sidebar">
    <div class="sidebar-header">🍽 SmartPlate Admin</div>

    <div class="sidebar-menu">
        <div class="menu-category">Menu</div>
        <a href="recettes.php" class="menu-item active">🍴 Gestion Recettes</a>
        <a href="ingredients.php" class="menu-item">🥕 Gestion Ingrédients</a>
        <a href="dashboard.php" class="menu-item">📊 Dashboard</a>
        <a href="utilisateurs.php" class="menu-item">👤 Utilisateurs</a>
    </div>
</div>

<div class="main-content">

    <div class="topbar">
        <div>Administration</div>
        <div class="admin-profile">
            <img src="https://i.pravatar.cc/40" alt="admin">
            Admin
        </div>
    </div>

    <div class="dashboard-container">

        <div class="page-header">
            <div>
                <h1>Gestion des recettes</h1>
                <p>Ajoutez, modifiez, recherchez et triez les recettes de SmartPlate.</p>
            </div>

            <button type="button" id="toggleRecetteForm" class="btn-success">
                <?= $editMode ? "Modifier la recette" : "Ajouter une nouvelle recette" ?>
            </button>
        </div>

        <div id="recetteFormContainer" style="<?= $editMode ? 'display:block;' : 'display:none;' ?>">
            <div class="card">
                <h2><?= $editMode ? "Modifier recette" : "Ajouter recette" ?></h2>

                <form method="POST" enctype="multipart/form-data" id="formRecette" novalidate>
                    <input type="hidden" name="id_recette" value="<?= $recetteEdit['id_recette'] ?? '' ?>">
                    <input type="hidden" name="ancienne_image" value="<?= htmlspecialchars($recetteEdit['image'] ?? '') ?>">

                    <div class="form-grid">
                        <div class="full-width">
                            <input type="text" id="nom_recette" name="nom_recette" placeholder="Nom recette" value="<?= htmlspecialchars($recetteEdit['nom_recette'] ?? '') ?>">
                            <div id="msg_nom_recette" class="msg"></div>
                        </div>

                        <div class="full-width">
                            <textarea id="description" name="description" placeholder="Description"><?= htmlspecialchars($recetteEdit['description'] ?? '') ?></textarea>
                            <div id="msg_description" class="msg"></div>
                            <div id="compteur_desc" style="margin-top:-6px; margin-bottom:12px; color:#6b7280;"></div>
                        </div>

                        <div>
                            <input type="number" step="0.01" id="calories_recette" name="calories" placeholder="Calories totales" readonly value="<?= htmlspecialchars($recetteEdit['calories'] ?? '') ?>">
                            <div id="msg_calories_recette" class="msg"></div>
                        </div>

                        <div>
                            <input type="number" step="0.01" id="proteines_recette" name="proteines" placeholder="Protéines totales" readonly value="<?= htmlspecialchars($recetteEdit['proteines'] ?? '') ?>">
                        </div>

                        <div>
                            <input type="number" step="0.01" id="lipides_recette" name="lipides" placeholder="Lipides totaux" readonly value="<?= htmlspecialchars($recetteEdit['lipides'] ?? '') ?>">
                        </div>

                        <div>
                            <input type="number" step="0.01" id="glucides_recette" name="glucides" placeholder="Glucides totaux" readonly value="<?= htmlspecialchars($recetteEdit['glucides'] ?? '') ?>">
                        </div>

                        <div>
                            <input type="number" id="temps" name="temps_preparation" placeholder="Temps (min)" value="<?= htmlspecialchars($recetteEdit['temps_preparation'] ?? '') ?>">
                            <div id="msg_temps" class="msg"></div>
                        </div>

                        <div>
                            <select id="categorie" name="categorie">
                                <option value="">Catégorie</option>
                                <option value="Healthy" <?= isset($recetteEdit) && $recetteEdit['categorie']=="Healthy" ? "selected" : "" ?>>Healthy</option>
                                <option value="Salade" <?= isset($recetteEdit) && $recetteEdit['categorie']=="Salade" ? "selected" : "" ?>>Salade</option>
                                <option value="Pate" <?= isset($recetteEdit) && $recetteEdit['categorie']=="Pate" ? "selected" : "" ?>>Pate</option>
                                <option value="Dessert" <?= isset($recetteEdit) && $recetteEdit['categorie']=="Dessert" ? "selected" : "" ?>>Dessert</option>
                            </select>
                            <div id="msg_categorie" class="msg"></div>
                        </div>

                        <div class="full-width">
                            <label style="font-weight:700; display:block; margin-bottom:8px;">Choisir les ingrédients</label>
                            <div class="ingredients-checkbox-grid">
                                <?php foreach($ingredients as $ing){ ?>
                                    <label class="ingredient-check-item">
                                        <input
                                            type="checkbox"
                                            name="ingredients[]"
                                            value="<?= $ing['id_ingredient'] ?>"
                                            data-calories="<?= $ing['calories'] ?>"
                                            data-proteines="<?= $ing['proteines'] ?>"
                                            data-lipides="<?= $ing['lipides'] ?>"
                                            data-glucides="<?= $ing['glucides'] ?>"
                                            <?= in_array($ing['id_ingredient'], $selectedIngredients) ? 'checked' : '' ?>
                                        >
                                        <?= htmlspecialchars($ing['nom_ingredient']) ?>
                                    </label>
                                <?php } ?>
                            </div>
                            <div id="msg_ingredients" class="msg"></div>
                        </div>

                        <div class="full-width">
                            <input type="file" id="image" name="image" accept=".jpg,.jpeg,.png,.webp">
                            <div id="msg_image" class="msg"></div>

                            <?php if(!empty($recetteEdit['image'])){ ?>
                                <div class="preview-box">
                                    <img
                                        src="../../images/<?= htmlspecialchars($recetteEdit['image']) ?>"
                                        alt="recette"
                                        width="80"
                                        height="80"
                                        style="width:80px !important; height:80px !important; max-width:80px !important; max-height:80px !important; min-width:80px !important; min-height:80px !important; object-fit:cover !important; border-radius:8px !important; border:1px solid #d1d5db !important; display:block !important;"
                                    >
                                </div>
                            <?php } ?>
                        </div>

                        <div class="full-width action-row">
                            <?php if($editMode){ ?>
                                <button class="btn-success" type="submit" name="modifier">Modifier</button>
                                <a href="recettes.php" class="btn-secondary">Annuler</a>
                            <?php } else { ?>
                                <button class="btn-success" type="submit" name="ajouter">Ajouter</button>
                            <?php } ?>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <div class="card">
            <h2>Recherche et tri</h2>

            <div class="filter-bar">
                <input type="text" id="liveSearchRecette" placeholder="Tape le nom d’une recette...">

                <form method="GET">
                    <select name="sort_recette">
                        <option value="recent" <?= $sortRecette === 'recent' ? 'selected' : '' ?>>Plus récentes</option>
                        <option value="nom_asc" <?= $sortRecette === 'nom_asc' ? 'selected' : '' ?>>Nom A → Z</option>
                        <option value="nom_desc" <?= $sortRecette === 'nom_desc' ? 'selected' : '' ?>>Nom Z → A</option>
                        <option value="calories_asc" <?= $sortRecette === 'calories_asc' ? 'selected' : '' ?>>Calories croissantes</option>
                        <option value="calories_desc" <?= $sortRecette === 'calories_desc' ? 'selected' : '' ?>>Calories décroissantes</option>
                        <option value="temps_asc" <?= $sortRecette === 'temps_asc' ? 'selected' : '' ?>>Temps croissant</option>
                        <option value="temps_desc" <?= $sortRecette === 'temps_desc' ? 'selected' : '' ?>>Temps décroissant</option>
                    </select>

                    <button type="submit" class="btn-success">Trier</button>
                    <a href="recettes.php" class="btn-secondary">Réinitialiser</a>
                </form>
            </div>
        </div>

        <div class="card-table">
            <h2>Liste recettes</h2>

            <table class="simple-admin-table" id="tableRecettes">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Image</th>
                        <th>Nom</th>
                        <th>Description</th>
                        <th>Ingrédients</th>
                        <th>Calories</th>
                        <th>Protéines</th>
                        <th>Lipides</th>
                        <th>Glucides</th>
                        <th>Temps</th>
                        <th>Catégorie</th>
                        <th>Action</th>
                    </tr>
                </thead>

                <tbody>
                    <?php if(!empty($recettes)){ ?>
                        <?php foreach($recettes as $r){ ?>
                            <tr class="recette-row">
                                <td><?= $r['id_recette'] ?></td>

                                <td class="table-image-cell">
                                    <?php if(!empty($r['image'])){ ?>
                                        <img
                                            src="../../images/<?= htmlspecialchars($r['image']) ?>"
                                            alt="recette"
                                            class="table-small-image"
                                            width="55"
                                            height="55"
                                            style="width:55px !important; height:55px !important; max-width:55px !important; max-height:55px !important; min-width:55px !important; min-height:55px !important; object-fit:cover !important; border-radius:8px !important; border:1px solid #d1d5db !important; display:block !important;"
                                        >
                                    <?php } else { ?>
                                        <span class="no-image-text">Aucune</span>
                                    <?php } ?>
                                </td>

                                <td class="recette-name"><?= htmlspecialchars($r['nom_recette']) ?></td>
                                <td><?= htmlspecialchars($r['description']) ?></td>

                                <td>
                                    <?php
                                    $q = $pdo->prepare("
                                        SELECT nom_ingredient
                                        FROM ingredient
                                        JOIN recette_ingredient
                                        ON ingredient.id_ingredient = recette_ingredient.ingredient_id
                                        WHERE recette_ingredient.recette_id = ?
                                    ");
                                    $q->execute([$r['id_recette']]);

                                    $listeIng = [];
                                    while($i = $q->fetch(PDO::FETCH_ASSOC)){
                                        $listeIng[] = $i['nom_ingredient'];
                                    }

                                    echo htmlspecialchars(implode(", ", $listeIng));
                                    ?>
                                </td>

                                <td><?= $r['calories'] ?></td>
                                <td><?= $r['proteines'] ?></td>
                                <td><?= $r['lipides'] ?></td>
                                <td><?= $r['glucides'] ?></td>
                                <td><?= $r['temps_preparation'] ?> min</td>
                                <td><?= htmlspecialchars($r['categorie']) ?></td>

                                <td>
                                    <div class="table-btns">
                                        <a class="btn-danger-outline"
                                           href="?delete=<?= $r['id_recette'] ?>"
                                           onclick="return confirm('Supprimer cette recette ?')">
                                            Supprimer
                                        </a>

                                        <a class="btn-action"
                                           href="?edit=<?= $r['id_recette'] ?>">
                                            Modifier
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php } ?>
                    <?php } else { ?>
                        <tr>
                            <td colspan="12" class="empty-table">Aucune recette trouvée.</td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>

    </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", function () {

    function msg(id, texte, couleur) {
        const zone = document.getElementById(id);
        if (zone) {
            zone.innerHTML = texte;
            zone.style.color = couleur;
        }
    }

    function bordure(champ, valide) {
        if (!champ) return;
        champ.classList.remove("input-error", "input-valid");
        champ.classList.add(valide ? "input-valid" : "input-error");
    }

    function texteNomValide(val) {
        return /^[A-Za-zÀ-ÿ0-9' -]{2,100}$/u.test(val);
    }

    function nombreValide(val, min, max) {
        const n = parseInt(val, 10);
        return !isNaN(n) && n >= min && n <= max;
    }

    function nombreDecimalValide(val, min, max) {
        const n = parseFloat(val);
        return !isNaN(n) && n >= min && n <= max;
    }

    const formRecette = document.getElementById("formRecette");

    if (formRecette) {
        const nom = document.getElementById("nom_recette");
        const description = document.getElementById("description");
        const compteur = document.getElementById("compteur_desc");
        const calories = document.getElementById("calories_recette");
        const temps = document.getElementById("temps");
        const categorie = document.getElementById("categorie");
        const image = document.getElementById("image");

        const proteines = document.getElementById("proteines_recette");
        const lipides = document.getElementById("lipides_recette");
        const glucides = document.getElementById("glucides_recette");

        function getSelectedIngredients() {
            return document.querySelectorAll('input[name="ingredients[]"]:checked');
        }

        function calculerValeursNutritionnellesRecette() {
            let totalCalories = 0;
            let totalProteines = 0;
            let totalLipides = 0;
            let totalGlucides = 0;

            const selected = getSelectedIngredients();

            selected.forEach(input => {
                totalCalories += parseFloat(input.dataset.calories || 0);
                totalProteines += parseFloat(input.dataset.proteines || 0);
                totalLipides += parseFloat(input.dataset.lipides || 0);
                totalGlucides += parseFloat(input.dataset.glucides || 0);
            });

            calories.value = totalCalories.toFixed(2);
            proteines.value = totalProteines.toFixed(2);
            lipides.value = totalLipides.toFixed(2);
            glucides.value = totalGlucides.toFixed(2);
        }

        function verifierNomRecette() {
            const val = nom.value.trim();
            if (val === "") {
                msg("msg_nom_recette", "❌ nom obligatoire", "red");
                bordure(nom, false);
                return false;
            }
            if (!texteNomValide(val)) {
                msg("msg_nom_recette", "❌ 2 à 100 caractères autorisés", "red");
                bordure(nom, false);
                return false;
            }
            msg("msg_nom_recette", "✔ nom valide", "green");
            bordure(nom, true);
            return true;
        }

        function verifierDescription() {
            const val = description.value.trim();
            compteur.innerHTML = val.length + " caractères";

            if (val.length < 10) {
                msg("msg_description", "❌ minimum 10 caractères", "red");
                bordure(description, false);
                return false;
            }

            if (val.length > 500) {
                msg("msg_description", "❌ maximum 500 caractères", "red");
                bordure(description, false);
                return false;
            }

            msg("msg_description", "✔ description valide", "green");
            bordure(description, true);
            return true;
        }

        function verifierTemps() {
            const val = temps.value.trim();
            if (val === "") {
                msg("msg_temps", "❌ temps obligatoire", "red");
                bordure(temps, false);
                return false;
            }
            if (!nombreValide(val, 1, 600)) {
                msg("msg_temps", "❌ valeur entre 1 et 600", "red");
                bordure(temps, false);
                return false;
            }
            msg("msg_temps", "✔ temps valide", "green");
            bordure(temps, true);
            return true;
        }

        function verifierCategorie() {
            if (categorie.value === "") {
                msg("msg_categorie", "❌ choisir une catégorie", "red");
                bordure(categorie, false);
                return false;
            }
            msg("msg_categorie", "✔ catégorie valide", "green");
            bordure(categorie, true);
            return true;
        }

        function verifierIngredients() {
            const selected = getSelectedIngredients();

            if (selected.length === 0) {
                msg("msg_ingredients", "❌ choisir au moins un ingredient", "red");
                calories.value = "";
                proteines.value = "";
                lipides.value = "";
                glucides.value = "";
                return false;
            }

            calculerValeursNutritionnellesRecette();
            msg("msg_ingredients", "✔ ingredients selectionnes", "green");
            return true;
        }

        function verifierImage() {
            const file = image.files[0];

            if (!file) {
                msg("msg_image", "", "");
                image.classList.remove("input-error", "input-valid");
                return true;
            }

            const types = ["image/jpeg", "image/png", "image/webp"];
            const extensions = ["jpg", "jpeg", "png", "webp"];

            const nomFichier = file.name.toLowerCase();
            const extension = nomFichier.split(".").pop();

            if (!extensions.includes(extension)) {
                msg("msg_image", "❌ extensions autorisees : jpg, jpeg, png, webp", "red");
                bordure(image, false);
                return false;
            }

            if (!types.includes(file.type)) {
                msg("msg_image", "❌ type image invalide", "red");
                bordure(image, false);
                return false;
            }

            msg("msg_image", "✔ image valide", "green");
            bordure(image, true);
            return true;
        }

        nom.addEventListener("input", verifierNomRecette);
        description.addEventListener("input", verifierDescription);
        temps.addEventListener("input", verifierTemps);
        categorie.addEventListener("change", verifierCategorie);
        image.addEventListener("change", verifierImage);

        document.querySelectorAll('input[name="ingredients[]"]').forEach(input => {
            input.addEventListener("change", verifierIngredients);
        });

        calculerValeursNutritionnellesRecette();
        verifierDescription();

        formRecette.addEventListener("submit", function(e){
            const ok =
                verifierNomRecette() &&
                verifierDescription() &&
                verifierTemps() &&
                verifierCategorie() &&
                verifierIngredients() &&
                verifierImage();

            if (!ok) e.preventDefault();
        });
    }

    const liveSearchRecette = document.getElementById("liveSearchRecette");
    const recetteRows = document.querySelectorAll("#tableRecettes .recette-row");

    if (liveSearchRecette) {
        liveSearchRecette.addEventListener("input", function () {
            const valeur = this.value.trim().toLowerCase();

            recetteRows.forEach(function(row){
                const nomCell = row.querySelector(".recette-name");
                const nom = nomCell ? nomCell.textContent.toLowerCase() : "";
                row.style.display = (valeur === "" || nom.includes(valeur)) ? "" : "none";
            });
        });
    }

    const toggleBtn = document.getElementById("toggleRecetteForm");
    const formContainer = document.getElementById("recetteFormContainer");

    if (toggleBtn && formContainer) {
        toggleBtn.addEventListener("click", function () {
            if (formContainer.style.display === "none" || formContainer.style.display === "") {
                formContainer.style.display = "block";
            } else {
                formContainer.style.display = "none";
            }
        });
    }
});
</script>

</body>
</html>