<?php
require_once "../../config.php";

/* ===================== OUTILS VALIDATION PHP ===================== */

function nettoyer($val){
    return trim($val ?? '');
}

function estNomValide($val){
    return preg_match("/^[A-Za-zÀ-ÿ0-9' -]{2,100}$/u", $val);
}

function estUniteValide($val){
    return preg_match("/^[A-Za-zÀ-ÿ]{1,20}$/u", $val);
}

function estNombreDansIntervalle($val, $min, $max){
    return filter_var($val, FILTER_VALIDATE_INT) !== false && $val >= $min && $val <= $max;
}

function estNombreDecimalValide($val, $min, $max){
    return is_numeric($val) && $val >= $min && $val <= $max;
}

function typeIngredientValide($type){
    $types = ["Legume","Fruit","Viande","Poisson","Produit laitier","Epice","Autre"];
    return in_array($type, $types);
}

function traiterImageIngredient($fileInput, $ancienNom = null){
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

    $nouveauNom = 'ingredient_' . time() . '_' . mt_rand(1000,9999) . '.' . $extension;
    $destination = "../../images/" . $nouveauNom;

    if(!move_uploaded_file($tmp, $destination)){
        return ['ok' => false, 'image' => $ancienNom, 'message' => 'Impossible d enregistrer l image'];
    }

    return ['ok' => true, 'image' => $nouveauNom, 'message' => ''];
}

/* ===================== ETAT EDITION ===================== */

$editIngredient = false;
$ingredientEdit = null;

/* ===================== AJOUTER INGREDIENT ===================== */

if(isset($_POST['add_ingredient'])){

    $nom   = nettoyer($_POST['nom_ingredient']);
    $type  = nettoyer($_POST['type_ingredient']);
    $cal   = nettoyer($_POST['calories_ing']);
    $prot  = nettoyer($_POST['proteines_ing']);
    $lip   = nettoyer($_POST['lipides_ing']);
    $glu   = nettoyer($_POST['glucides_ing']);
    $debut = nettoyer($_POST['saison_debut']);
    $fin   = nettoyer($_POST['saison_fin']);
    $prix  = nettoyer($_POST['prix_unitaire']);
    $unite = nettoyer($_POST['unite']);

    $imageResult = traiterImageIngredient($_FILES['image_ing'] ?? null, null);
    $image = $imageResult['image'];
    $imageOk = $imageResult['ok'];

    if(
        estNomValide($nom) &&
        typeIngredientValide($type) &&
        estNombreDecimalValide($cal, 0, 2000) &&
        estNombreDecimalValide($prot, 0, 1000) &&
        estNombreDecimalValide($lip, 0, 1000) &&
        estNombreDecimalValide($glu, 0, 1000) &&
        estNombreDansIntervalle($debut, 1, 12) &&
        estNombreDansIntervalle($fin, 1, 12) &&
        $debut <= $fin &&
        estNombreDecimalValide($prix, 0, 100000) &&
        estUniteValide($unite) &&
        $imageOk
    ){
        $sql = "INSERT INTO ingredient
                (nom_ingredient,type_ingredient,calories,proteines,lipides,glucides,saison_debut,saison_fin,prix_unitaire,unite,image)
                VALUES(?,?,?,?,?,?,?,?,?,?,?)";

        $pdo->prepare($sql)->execute([$nom,$type,$cal,$prot,$lip,$glu,$debut,$fin,$prix,$unite,$image]);
    }

    header("Location: ingredients.php");
    exit();
}

/* ===================== SUPPRIMER INGREDIENT ===================== */

if(isset($_GET['delete_ing'])){
    $id = (int) $_GET['delete_ing'];

    $pdo->prepare("DELETE FROM recette_ingredient WHERE ingredient_id=?")->execute([$id]);
    $pdo->prepare("DELETE FROM ingredient WHERE id_ingredient=?")->execute([$id]);

    header("Location: ingredients.php");
    exit();
}

/* ===================== EDIT INGREDIENT ===================== */

if(isset($_GET['edit_ing'])){
    $editIngredient = true;
    $id = (int) $_GET['edit_ing'];

    $q = $pdo->prepare("SELECT * FROM ingredient WHERE id_ingredient=?");
    $q->execute([$id]);
    $ingredientEdit = $q->fetch(PDO::FETCH_ASSOC);
}

/* ===================== MODIFIER INGREDIENT ===================== */

if(isset($_POST['modifier_ingredient'])){

    $id    = (int) $_POST['id_ingredient'];
    $nom   = nettoyer($_POST['nom_ingredient']);
    $type  = nettoyer($_POST['type_ingredient']);
    $cal   = nettoyer($_POST['calories_ing']);
    $prot  = nettoyer($_POST['proteines_ing']);
    $lip   = nettoyer($_POST['lipides_ing']);
    $glu   = nettoyer($_POST['glucides_ing']);
    $debut = nettoyer($_POST['saison_debut']);
    $fin   = nettoyer($_POST['saison_fin']);
    $prix  = nettoyer($_POST['prix_unitaire']);
    $unite = nettoyer($_POST['unite']);

    $ancienneImage = nettoyer($_POST['ancienne_image'] ?? '');
    $imageResult = traiterImageIngredient($_FILES['image_ing'] ?? null, $ancienneImage);
    $imageOk = $imageResult['ok'];
    $image = $imageResult['image'];

    $validationBase =
        estNomValide($nom) &&
        typeIngredientValide($type) &&
        estNombreDecimalValide($cal, 0, 2000) &&
        estNombreDecimalValide($prot, 0, 1000) &&
        estNombreDecimalValide($lip, 0, 1000) &&
        estNombreDecimalValide($glu, 0, 1000) &&
        estNombreDansIntervalle($debut, 1, 12) &&
        estNombreDansIntervalle($fin, 1, 12) &&
        $debut <= $fin &&
        estNombreDecimalValide($prix, 0, 100000) &&
        estUniteValide($unite) &&
        $imageOk;

    if($validationBase){
        $sql = "UPDATE ingredient SET
                nom_ingredient=?,
                type_ingredient=?,
                calories=?,
                proteines=?,
                lipides=?,
                glucides=?,
                saison_debut=?,
                saison_fin=?,
                prix_unitaire=?,
                unite=?,
                image=?
                WHERE id_ingredient=?";

        $pdo->prepare($sql)->execute([$nom,$type,$cal,$prot,$lip,$glu,$debut,$fin,$prix,$unite,$image,$id]);
    }

    header("Location: ingredients.php");
    exit();
}

/* ===================== TRI + CHARGEMENT INGREDIENTS ===================== */

$sortIngredient = nettoyer($_GET['sort_ingredient'] ?? 'nom_asc');

$sqlIngredients = "SELECT * FROM ingredient";

switch($sortIngredient){
    case 'nom_desc':
        $sqlIngredients .= " ORDER BY nom_ingredient DESC";
        break;
    case 'calories_asc':
        $sqlIngredients .= " ORDER BY calories ASC";
        break;
    case 'calories_desc':
        $sqlIngredients .= " ORDER BY calories DESC";
        break;
    default:
        $sqlIngredients .= " ORDER BY nom_ingredient ASC";
        break;
}

$ingredients = $pdo->query($sqlIngredients)->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Gestion des ingrédients</title>
<link rel="stylesheet" href="templates/templateback.css">
</head>

<body>

<div class="admin-sidebar">
    <div class="sidebar-header">🍽 SmartPlate Admin</div>

    <div class="sidebar-menu">
        <div class="menu-category">Menu</div>
        <a href="recettes.php" class="menu-item">🍴 Gestion Recettes</a>
        <a href="ingredients.php" class="menu-item active">🥕 Gestion Ingrédients</a>
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
                <h1>Gestion des ingrédients</h1>
                <p>Ajoutez, modifiez, recherchez et triez les ingrédients de SmartPlate.</p>
            </div>

            <button type="button" id="toggleIngredientForm" class="btn-success">
                <?= $editIngredient ? "Modifier l’ingrédient" : "Ajouter un nouvel ingrédient" ?>
            </button>
        </div>

        <div id="ingredientFormContainer" style="<?= $editIngredient ? 'display:block;' : 'display:none;' ?>">
            <div class="card">
                <h2><?= $editIngredient ? "Modifier ingrédient" : "Ajouter ingrédient" ?></h2>

                <form method="POST" enctype="multipart/form-data" id="formIngredient" novalidate>
                    <input type="hidden" name="id_ingredient" value="<?= $ingredientEdit['id_ingredient'] ?? '' ?>">
                    <input type="hidden" name="ancienne_image" value="<?= htmlspecialchars($ingredientEdit['image'] ?? '') ?>">

                    <div class="form-grid">
                        <div class="full-width">
                            <input type="text" id="nom_ing" name="nom_ingredient" placeholder="Nom ingrédient" value="<?= htmlspecialchars($ingredientEdit['nom_ingredient'] ?? '') ?>">
                            <div id="msg_nom_ing" class="msg"></div>
                        </div>

                        <div>
                            <select name="type_ingredient" id="type_ing">
                                <option value="">Choisir type ingrédient</option>
                                <option value="Legume" <?= isset($ingredientEdit) && $ingredientEdit['type_ingredient']=="Legume" ? "selected" : "" ?>>Légume</option>
                                <option value="Fruit" <?= isset($ingredientEdit) && $ingredientEdit['type_ingredient']=="Fruit" ? "selected" : "" ?>>Fruit</option>
                                <option value="Viande" <?= isset($ingredientEdit) && $ingredientEdit['type_ingredient']=="Viande" ? "selected" : "" ?>>Viande</option>
                                <option value="Poisson" <?= isset($ingredientEdit) && $ingredientEdit['type_ingredient']=="Poisson" ? "selected" : "" ?>>Poisson</option>
                                <option value="Produit laitier" <?= isset($ingredientEdit) && $ingredientEdit['type_ingredient']=="Produit laitier" ? "selected" : "" ?>>Produit laitier</option>
                                <option value="Epice" <?= isset($ingredientEdit) && $ingredientEdit['type_ingredient']=="Epice" ? "selected" : "" ?>>Épice</option>
                                <option value="Autre" <?= isset($ingredientEdit) && $ingredientEdit['type_ingredient']=="Autre" ? "selected" : "" ?>>Autre</option>
                            </select>
                            <div id="msg_type_ing" class="msg"></div>
                        </div>

                        <div>
                            <input type="number" step="0.01" id="cal_ing" name="calories_ing" placeholder="Calories" value="<?= htmlspecialchars($ingredientEdit['calories'] ?? '') ?>">
                            <div id="msg_cal_ing" class="msg"></div>
                        </div>

                        <div>
                            <input type="number" step="0.01" id="proteines_ing" name="proteines_ing" placeholder="Protéines (g)" value="<?= htmlspecialchars($ingredientEdit['proteines'] ?? '') ?>">
                            <div id="msg_proteines_ing" class="msg"></div>
                        </div>

                        <div>
                            <input type="number" step="0.01" id="lipides_ing" name="lipides_ing" placeholder="Lipides (g)" value="<?= htmlspecialchars($ingredientEdit['lipides'] ?? '') ?>">
                            <div id="msg_lipides_ing" class="msg"></div>
                        </div>

                        <div>
                            <input type="number" step="0.01" id="glucides_ing" name="glucides_ing" placeholder="Glucides (g)" value="<?= htmlspecialchars($ingredientEdit['glucides'] ?? '') ?>">
                            <div id="msg_glucides_ing" class="msg"></div>
                        </div>

                        <div>
                            <input type="number" id="saison_debut" name="saison_debut" placeholder="Saison début (mois)" value="<?= htmlspecialchars($ingredientEdit['saison_debut'] ?? '') ?>">
                            <div id="msg_saison_debut" class="msg"></div>
                        </div>

                        <div>
                            <input type="number" id="saison_fin" name="saison_fin" placeholder="Saison fin (mois)" value="<?= htmlspecialchars($ingredientEdit['saison_fin'] ?? '') ?>">
                            <div id="msg_saison_fin" class="msg"></div>
                        </div>

                        <div>
                            <input type="number" step="0.01" id="prix_unitaire" name="prix_unitaire" placeholder="Prix unitaire (DT)" value="<?= htmlspecialchars($ingredientEdit['prix_unitaire'] ?? '') ?>">
                            <div id="msg_prix_unitaire" class="msg"></div>
                        </div>

                        <div>
                            <input type="text" id="unite" name="unite" placeholder="Unité (kg, pièce...)" value="<?= htmlspecialchars($ingredientEdit['unite'] ?? '') ?>">
                            <div id="msg_unite" class="msg"></div>
                        </div>

                        <div class="full-width">
                            <input type="file" id="image_ing" name="image_ing" accept=".jpg,.jpeg,.png,.webp">
                            <div id="msg_image_ing" class="msg"></div>

                            <?php if(!empty($ingredientEdit['image'])){ ?>
                                <div class="preview-box">
                                    <img src="../../images/<?= htmlspecialchars($ingredientEdit['image']) ?>" alt="ingredient" class="table-img-large">
                                </div>
                            <?php } ?>
                        </div>

                        <div class="full-width action-row">
                            <?php if($editIngredient){ ?>
                                <button class="btn-success" type="submit" name="modifier_ingredient">Modifier</button>
                                <a href="ingredients.php" class="btn-secondary">Annuler</a>
                            <?php } else { ?>
                                <button class="btn-success" type="submit" name="add_ingredient">Ajouter</button>
                            <?php } ?>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <div class="card">
            <h2>Recherche et tri</h2>

            <div class="filter-bar">
                <input type="text" id="liveSearchIngredient" placeholder="Tape le nom d’un ingrédient...">

                <form method="GET">
                    <select name="sort_ingredient">
                        <option value="nom_asc" <?= $sortIngredient === 'nom_asc' ? 'selected' : '' ?>>Nom A → Z</option>
                        <option value="nom_desc" <?= $sortIngredient === 'nom_desc' ? 'selected' : '' ?>>Nom Z → A</option>
                        <option value="calories_asc" <?= $sortIngredient === 'calories_asc' ? 'selected' : '' ?>>Calories croissantes</option>
                        <option value="calories_desc" <?= $sortIngredient === 'calories_desc' ? 'selected' : '' ?>>Calories décroissantes</option>
                    </select>

                    <button type="submit" class="btn-success">Trier</button>
                    <a href="ingredients.php" class="btn-secondary">Réinitialiser</a>
                </form>
            </div>
        </div>

        <div class="card-table">
            <div class="table-header-zone">
                <h2>Liste des ingrédients</h2>
                <span class="table-count"><?= count($ingredients) ?> ingrédient(s)</span>
            </div>

            <table class="modern-table" id="tableIngredients">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Image</th>
                        <th>Nom</th>
                        <th>Type</th>
                        <th>Calories</th>
                        <th>Protéines</th>
                        <th>Lipides</th>
                        <th>Glucides</th>
                        <th>Saison</th>
                        <th>Prix unitaire</th>
                        <th>Unité</th>
                        <th>Actions</th>
                    </tr>
                </thead>

                <tbody>
                    <?php if(!empty($ingredients)){ ?>
                        <?php foreach($ingredients as $ing){ ?>
                            <tr class="ingredient-row">
                                <td class="id-col">#<?= $ing['id_ingredient'] ?></td>

                                <td class="img-col">
                                    <?php if(!empty($ing['image'])){ ?>
                                        <img
                                            src="../../images/<?= htmlspecialchars($ing['image']) ?>"
                                            alt="ingredient"
                                            width="56"
                                            height="56"
                                            class="table-img"
                                        >
                                    <?php } else { ?>
                                        <div class="img-placeholder">—</div>
                                    <?php } ?>
                                </td>

                                <td class="ingredient-name">
                                    <?= htmlspecialchars($ing['nom_ingredient']) ?>
                                </td>

                                <td>
                                    <span class="badge-type">
                                        <?= htmlspecialchars($ing['type_ingredient']) ?>
                                    </span>
                                </td>

                                <td><span class="value-pill"><?= $ing['calories'] ?></span></td>
                                <td><?= $ing['proteines'] ?> g</td>
                                <td><?= $ing['lipides'] ?> g</td>
                                <td><?= $ing['glucides'] ?> g</td>

                                <td>
                                    <span class="season-badge">
                                        <?= $ing['saison_debut'] ?> - <?= $ing['saison_fin'] ?>
                                    </span>
                                </td>

                                <td><?= number_format((float)($ing['prix_unitaire'] ?? 0), 2) ?> DT</td>
                                <td><?= htmlspecialchars($ing['unite'] ?? '') ?></td>

                                <td>
                                    <div class="table-actions">
                                        <a class="btn-action" href="?edit_ing=<?= $ing['id_ingredient'] ?>">
                                            Modifier
                                        </a>
                                        <a class="btn-danger-outline"
                                           href="?delete_ing=<?= $ing['id_ingredient'] ?>"
                                           onclick="return confirm('Supprimer cet ingrédient ?')">
                                            Supprimer
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php } ?>
                    <?php } else { ?>
                        <tr>
                            <td colspan="12" class="empty-table">
                                Aucun ingrédient trouvé.
                            </td>
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

    function uniteValide(val) {
        return /^[A-Za-zÀ-ÿ]{1,20}$/u.test(val);
    }

    function nombreValide(val, min, max) {
        const n = parseInt(val, 10);
        return !isNaN(n) && n >= min && n <= max;
    }

    function nombreDecimalValide(val, min, max) {
        const n = parseFloat(val);
        return !isNaN(n) && n >= min && n <= max;
    }

    const formIngredient = document.getElementById("formIngredient");

    if (formIngredient) {
        const nomIng = document.getElementById("nom_ing");
        const typeIng = document.getElementById("type_ing");
        const calIng = document.getElementById("cal_ing");
        const protIng = document.getElementById("proteines_ing");
        const lipIng = document.getElementById("lipides_ing");
        const gluIng = document.getElementById("glucides_ing");
        const debut = document.getElementById("saison_debut");
        const fin = document.getElementById("saison_fin");
        const prixUnitaire = document.getElementById("prix_unitaire");
        const unite = document.getElementById("unite");
        const imageIng = document.getElementById("image_ing");

        function verifierNomIngredient() {
            const val = nomIng.value.trim();
            if (val === "") {
                msg("msg_nom_ing", "❌ nom obligatoire", "red");
                bordure(nomIng, false);
                return false;
            }
            if (!texteNomValide(val)) {
                msg("msg_nom_ing", "❌ 2 à 100 caractères autorisés", "red");
                bordure(nomIng, false);
                return false;
            }
            msg("msg_nom_ing", "✔ nom valide", "green");
            bordure(nomIng, true);
            return true;
        }

        function verifierTypeIngredient() {
            if (typeIng.value === "") {
                msg("msg_type_ing", "❌ choisir un type", "red");
                bordure(typeIng, false);
                return false;
            }
            msg("msg_type_ing", "✔ type valide", "green");
            bordure(typeIng, true);
            return true;
        }

        function verifierCaloriesIngredient() {
            const val = calIng.value.trim();
            if (val === "") {
                msg("msg_cal_ing", "❌ calories obligatoires", "red");
                bordure(calIng, false);
                return false;
            }
            if (!nombreDecimalValide(val, 0, 2000)) {
                msg("msg_cal_ing", "❌ valeur entre 0 et 2000", "red");
                bordure(calIng, false);
                return false;
            }
            msg("msg_cal_ing", "✔ calories valides", "green");
            bordure(calIng, true);
            return true;
        }

        function verifierProteinesIngredient() {
            const val = protIng.value.trim();
            if (val === "") {
                msg("msg_proteines_ing", "❌ protéines obligatoires", "red");
                bordure(protIng, false);
                return false;
            }
            if (!nombreDecimalValide(val, 0, 1000)) {
                msg("msg_proteines_ing", "❌ valeur entre 0 et 1000", "red");
                bordure(protIng, false);
                return false;
            }
            msg("msg_proteines_ing", "✔ protéines valides", "green");
            bordure(protIng, true);
            return true;
        }

        function verifierLipidesIngredient() {
            const val = lipIng.value.trim();
            if (val === "") {
                msg("msg_lipides_ing", "❌ lipides obligatoires", "red");
                bordure(lipIng, false);
                return false;
            }
            if (!nombreDecimalValide(val, 0, 1000)) {
                msg("msg_lipides_ing", "❌ valeur entre 0 et 1000", "red");
                bordure(lipIng, false);
                return false;
            }
            msg("msg_lipides_ing", "✔ lipides valides", "green");
            bordure(lipIng, true);
            return true;
        }

        function verifierGlucidesIngredient() {
            const val = gluIng.value.trim();
            if (val === "") {
                msg("msg_glucides_ing", "❌ glucides obligatoires", "red");
                bordure(gluIng, false);
                return false;
            }
            if (!nombreDecimalValide(val, 0, 1000)) {
                msg("msg_glucides_ing", "❌ valeur entre 0 et 1000", "red");
                bordure(gluIng, false);
                return false;
            }
            msg("msg_glucides_ing", "✔ glucides valides", "green");
            bordure(gluIng, true);
            return true;
        }

        function verifierSaisonDebut() {
            const val = debut.value.trim();
            if (val === "") {
                msg("msg_saison_debut", "❌ mois début obligatoire", "red");
                bordure(debut, false);
                return false;
            }
            if (!nombreValide(val, 1, 12)) {
                msg("msg_saison_debut", "❌ mois entre 1 et 12", "red");
                bordure(debut, false);
                return false;
            }
            msg("msg_saison_debut", "✔ mois valide", "green");
            bordure(debut, true);
            return true;
        }

        function verifierSaisonFin() {
            const val = fin.value.trim();
            if (val === "") {
                msg("msg_saison_fin", "❌ mois fin obligatoire", "red");
                bordure(fin, false);
                return false;
            }
            if (!nombreValide(val, 1, 12)) {
                msg("msg_saison_fin", "❌ mois entre 1 et 12", "red");
                bordure(fin, false);
                return false;
            }
            const vDebut = parseInt(debut.value, 10);
            const vFin = parseInt(fin.value, 10);

            if (!isNaN(vDebut) && !isNaN(vFin) && vFin < vDebut) {
                msg("msg_saison_fin", "❌ saison fin doit être ≥ saison début", "red");
                bordure(fin, false);
                return false;
            }

            msg("msg_saison_fin", "✔ mois valide", "green");
            bordure(fin, true);
            return true;
        }

        function verifierPrixUnitaire() {
            const val = prixUnitaire.value.trim();
            if (val === "") {
                msg("msg_prix_unitaire", "❌ prix obligatoire", "red");
                bordure(prixUnitaire, false);
                return false;
            }
            if (!nombreDecimalValide(val, 0, 100000)) {
                msg("msg_prix_unitaire", "❌ valeur invalide", "red");
                bordure(prixUnitaire, false);
                return false;
            }
            msg("msg_prix_unitaire", "✔ prix valide", "green");
            bordure(prixUnitaire, true);
            return true;
        }

        function verifierUnite() {
            const val = unite.value.trim();
            if (val === "") {
                msg("msg_unite", "❌ unité obligatoire", "red");
                bordure(unite, false);
                return false;
            }
            if (!uniteValide(val)) {
                msg("msg_unite", "❌ unité invalide", "red");
                bordure(unite, false);
                return false;
            }
            msg("msg_unite", "✔ unité valide", "green");
            bordure(unite, true);
            return true;
        }

        function verifierImageIngredient() {
            const file = imageIng.files[0];

            if (!file) {
                msg("msg_image_ing", "", "");
                imageIng.classList.remove("input-error", "input-valid");
                return true;
            }

            const types = ["image/jpeg", "image/png", "image/webp"];
            const extensions = ["jpg", "jpeg", "png", "webp"];

            const nom = file.name.toLowerCase();
            const extension = nom.split(".").pop();

            if (!extensions.includes(extension)) {
                msg("msg_image_ing", "❌ extensions autorisees : jpg, jpeg, png, webp", "red");
                bordure(imageIng, false);
                return false;
            }

            if (!types.includes(file.type)) {
                msg("msg_image_ing", "❌ type image invalide", "red");
                bordure(imageIng, false);
                return false;
            }

            msg("msg_image_ing", "✔ image valide", "green");
            bordure(imageIng, true);
            return true;
        }

        nomIng.addEventListener("input", verifierNomIngredient);
        typeIng.addEventListener("change", verifierTypeIngredient);
        calIng.addEventListener("input", verifierCaloriesIngredient);
        protIng.addEventListener("input", verifierProteinesIngredient);
        lipIng.addEventListener("input", verifierLipidesIngredient);
        gluIng.addEventListener("input", verifierGlucidesIngredient);
        debut.addEventListener("input", verifierSaisonDebut);
        fin.addEventListener("input", verifierSaisonFin);
        prixUnitaire.addEventListener("input", verifierPrixUnitaire);
        unite.addEventListener("input", verifierUnite);
        imageIng.addEventListener("change", verifierImageIngredient);

        formIngredient.addEventListener("submit", function(e){
            const ok =
                verifierNomIngredient() &&
                verifierTypeIngredient() &&
                verifierCaloriesIngredient() &&
                verifierProteinesIngredient() &&
                verifierLipidesIngredient() &&
                verifierGlucidesIngredient() &&
                verifierSaisonDebut() &&
                verifierSaisonFin() &&
                verifierPrixUnitaire() &&
                verifierUnite() &&
                verifierImageIngredient();

            if (!ok) e.preventDefault();
        });
    }

    const liveSearchIngredient = document.getElementById("liveSearchIngredient");
    const ingredientRows = document.querySelectorAll("#tableIngredients .ingredient-row");

    if (liveSearchIngredient) {
        liveSearchIngredient.addEventListener("input", function () {
            const valeur = this.value.trim().toLowerCase();

            ingredientRows.forEach(function(row){
                const nomCell = row.querySelector(".ingredient-name");
                const nom = nomCell ? nomCell.textContent.toLowerCase() : "";
                row.style.display = (valeur === "" || nom.includes(valeur)) ? "" : "none";
            });
        });
    }

    const toggleBtn = document.getElementById("toggleIngredientForm");
    const formContainer = document.getElementById("ingredientFormContainer");

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