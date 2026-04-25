<?php
require_once "../../config.php";

/* ===================== OUTILS VALIDATION PHP ===================== */

function nettoyer($val){
    return trim($val ?? '');
}

function estNomValide($val){
    return preg_match("/^[A-Za-zÀ-ÿ0-9' -]{3,100}$/u", $val);
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

function categorieValide($cat){
    $categories = ["Healthy","Salade","Pate","Dessert"];
    return in_array($cat, $categories);
}

/* ===================== TRI INGREDIENTS ===================== */

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

/* ===================== TRI RECETTES ===================== */

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

/* ===================== CRUD INGREDIENT ===================== */

/* Ajouter ingrédient */
if(isset($_POST['add_ingredient'])){

    $nom   = nettoyer($_POST['nom_ingredient']);
    $type  = nettoyer($_POST['type_ingredient']);
    $cal   = nettoyer($_POST['calories_ing']);
    $prot  = nettoyer($_POST['proteines_ing']);
    $lip   = nettoyer($_POST['lipides_ing']);
    $glu   = nettoyer($_POST['glucides_ing']);
    $debut = nettoyer($_POST['saison_debut']);
    $fin   = nettoyer($_POST['saison_fin']);

    $image = null;
    $imageOk = true;

    if(isset($_FILES['image_ing']) && !empty($_FILES['image_ing']['name'])){
        $allowedTypes = ['image/jpeg','image/png','image/jpg','image/webp'];

        if(
            in_array($_FILES['image_ing']['type'], $allowedTypes) &&
            $_FILES['image_ing']['size'] <= 2 * 1024 * 1024
        ){
            $image = basename($_FILES['image_ing']['name']);
            $tmp = $_FILES['image_ing']['tmp_name'];
            move_uploaded_file($tmp, "../../images/" . $image);
        } else {
            $imageOk = false;
        }
    }

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
        $imageOk
    ){
        $sql = "INSERT INTO ingredient
                (nom_ingredient,type_ingredient,calories,proteines,lipides,glucides,saison_debut,saison_fin,image)
                VALUES(?,?,?,?,?,?,?,?,?)";
        $pdo->prepare($sql)->execute([$nom,$type,$cal,$prot,$lip,$glu,$debut,$fin,$image]);
    }

    header("Location: backoffice.php");
    exit();
}

/* Supprimer ingrédient */
if(isset($_GET['delete_ing'])){
    $id = (int) $_GET['delete_ing'];

    $pdo->prepare("DELETE FROM recette_ingredient WHERE ingredient_id=?")->execute([$id]);
    $pdo->prepare("DELETE FROM ingredient WHERE id_ingredient=?")->execute([$id]);

    header("Location: backoffice.php");
    exit();
}

/* ===================== EDIT INGREDIENT ===================== */

$editIngredient = false;
$ingredientEdit = null;

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

    $validationBase =
        estNomValide($nom) &&
        typeIngredientValide($type) &&
        estNombreDecimalValide($cal, 0, 2000) &&
        estNombreDecimalValide($prot, 0, 1000) &&
        estNombreDecimalValide($lip, 0, 1000) &&
        estNombreDecimalValide($glu, 0, 1000) &&
        estNombreDansIntervalle($debut, 1, 12) &&
        estNombreDansIntervalle($fin, 1, 12) &&
        $debut <= $fin;

    if($validationBase){

        if(isset($_FILES['image_ing']) && !empty($_FILES['image_ing']['name'])){

            $allowedTypes = ['image/jpeg','image/png','image/jpg','image/webp'];

            if(
                in_array($_FILES['image_ing']['type'], $allowedTypes) &&
                $_FILES['image_ing']['size'] <= 2 * 1024 * 1024
            ){
                $image = basename($_FILES['image_ing']['name']);
                $tmp = $_FILES['image_ing']['tmp_name'];
                move_uploaded_file($tmp, "../../images/" . $image);

                $sql = "UPDATE ingredient SET
                        nom_ingredient=?,
                        type_ingredient=?,
                        calories=?,
                        proteines=?,
                        lipides=?,
                        glucides=?,
                        saison_debut=?,
                        saison_fin=?,
                        image=?
                        WHERE id_ingredient=?";

                $pdo->prepare($sql)->execute([$nom,$type,$cal,$prot,$lip,$glu,$debut,$fin,$image,$id]);
            }

        } else {

            $sql = "UPDATE ingredient SET
                    nom_ingredient=?,
                    type_ingredient=?,
                    calories=?,
                    proteines=?,
                    lipides=?,
                    glucides=?,
                    saison_debut=?,
                    saison_fin=?
                    WHERE id_ingredient=?";

            $pdo->prepare($sql)->execute([$nom,$type,$cal,$prot,$lip,$glu,$debut,$fin,$id]);
        }
    }

    header("Location: backoffice.php");
    exit();
}

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

    $image = '';
    $imageOk = false;

    if(isset($_FILES['image']) && !empty($_FILES['image']['name'])){
        $allowedTypes = ['image/jpeg','image/png','image/jpg','image/webp'];

        if(
            in_array($_FILES['image']['type'], $allowedTypes) &&
            $_FILES['image']['size'] <= 2 * 1024 * 1024
        ){
            $image = basename($_FILES['image']['name']);
            $tmp = $_FILES['image']['tmp_name'];
            move_uploaded_file($tmp, "../../images/" . $image);
            $imageOk = true;
        }
    }

    if(
        estNomValide($nom) &&
        strlen($description) >= 10 && strlen($description) <= 500 &&
        estNombreDecimalValide($calories, 0, 5000) &&
        estNombreDecimalValide($proteines, 0, 10000) &&
        estNombreDecimalValide($lipides, 0, 10000) &&
        estNombreDecimalValide($glucides, 0, 10000) &&
        estNombreDansIntervalle($temps, 1, 600) &&
        categorieValide($categorie) &&
        !empty($ingredientsPost) &&
        $imageOk
    ){
        $sql = "INSERT INTO recette
                (nom_recette,description,calories,proteines,lipides,glucides,temps_preparation,categorie,image)
                VALUES(?,?,?,?,?,?,?,?,?)";

        $pdo->prepare($sql)->execute([
            $nom,
            $description,
            $calories,
            $proteines,
            $lipides,
            $glucides,
            $temps,
            $categorie,
            $image
        ]);

        $recette_id = $pdo->lastInsertId();

        foreach($ingredientsPost as $ing){
            $pdo->prepare("INSERT INTO recette_ingredient(recette_id,ingredient_id) VALUES(?,?)")
                ->execute([$recette_id, (int)$ing]);
        }
    }

    header("Location: backoffice.php");
    exit();
}

/* ===================== SUPPRIMER RECETTE ===================== */

if(isset($_GET['delete'])){
    $id = (int) $_GET['delete'];

    $pdo->prepare("DELETE FROM recette_ingredient WHERE recette_id=?")->execute([$id]);
    $pdo->prepare("DELETE FROM recette WHERE id_recette=?")->execute([$id]);

    header("Location: backoffice.php");
    exit();
}

/* ===================== EDIT RECETTE ===================== */

$editMode = false;
$recetteEdit = null;
$selectedIngredients = [];

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

    $validationBase =
        estNomValide($nom) &&
        strlen($description) >= 10 && strlen($description) <= 500 &&
        estNombreDecimalValide($calories, 0, 5000) &&
        estNombreDecimalValide($proteines, 0, 10000) &&
        estNombreDecimalValide($lipides, 0, 10000) &&
        estNombreDecimalValide($glucides, 0, 10000) &&
        estNombreDansIntervalle($temps, 1, 600) &&
        categorieValide($categorie) &&
        !empty($ingredientsPost);

    if($validationBase){

        if(!empty($_FILES['image']['name'])){

            $allowedTypes = ['image/jpeg','image/png','image/jpg','image/webp'];

            if(
                in_array($_FILES['image']['type'], $allowedTypes) &&
                $_FILES['image']['size'] <= 2 * 1024 * 1024
            ){
                $image = basename($_FILES['image']['name']);
                $tmp = $_FILES['image']['tmp_name'];
                move_uploaded_file($tmp, "../../images/" . $image);

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

                $pdo->prepare($sql)->execute([$nom,$description,$calories,$proteines,$lipides,$glucides,$temps,$categorie,$image,$id]);
            }

        } else {

            $sql = "UPDATE recette SET
                    nom_recette=?,
                    description=?,
                    calories=?,
                    proteines=?,
                    lipides=?,
                    glucides=?,
                    temps_preparation=?,
                    categorie=?
                    WHERE id_recette=?";

            $pdo->prepare($sql)->execute([$nom,$description,$calories,$proteines,$lipides,$glucides,$temps,$categorie,$id]);
        }

        $pdo->prepare("DELETE FROM recette_ingredient WHERE recette_id=?")->execute([$id]);

        foreach($ingredientsPost as $ing){
            $pdo->prepare("INSERT INTO recette_ingredient(recette_id,ingredient_id) VALUES(?,?)")
                ->execute([$id,(int)$ing]);
        }
    }

    header("Location: backoffice.php");
    exit();
}

?>

<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>Admin SmartPlate</title>
<link rel="stylesheet" href="templates/templateback.css">

<style>
.msg{
    font-size:13px;
    margin:4px 0 8px;
    font-weight:500;
}
.input-error{
    border:2px solid #e74c3c !important;
    outline:none;
}
.input-valid{
    border:2px solid #2ecc71 !important;
    outline:none;
}
.filter-form{
    display:flex;
    flex-wrap:wrap;
    gap:10px;
    align-items:center;
    margin-top:10px;
    margin-bottom:10px;
}
.filter-form input,
.filter-form select,
.filter-form button,
.filter-form a{
    padding:10px 12px;
    border-radius:8px;
}
.filter-form a{
    text-decoration:none;
    background:#eee;
    color:#222;
}
</style>
</head>

<body>

<div class="admin-sidebar">
    <div class="sidebar-header">🍽 SmartPlate Admin</div>

    <div class="sidebar-menu">
        <div class="menu-category">Menu</div>

        <a href="backoffice.php" class="menu-item active">🍴 Gestion Recettes</a>
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

        <div class="card">
            <h2><?= $editIngredient ? "Modifier ingrédient" : "Ajouter ingrédient" ?></h2>

            <form method="POST" enctype="multipart/form-data" id="formIngredient" novalidate>

                <input type="hidden" name="id_ingredient" value="<?= $ingredientEdit['id_ingredient'] ?? '' ?>">

                <input type="text" id="nom_ing" name="nom_ingredient"
                placeholder="Nom ingrédient"
                value="<?= htmlspecialchars($ingredientEdit['nom_ingredient'] ?? '') ?>">

                <div id="msg_nom_ing" class="msg"></div>

                <div class="form-group">
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

                <input type="number" step="0.01" id="cal_ing" name="calories_ing"
                placeholder="Calories"
                value="<?= htmlspecialchars($ingredientEdit['calories'] ?? '') ?>">
                <div id="msg_cal_ing" class="msg"></div>

                <input type="number" step="0.01" id="proteines_ing" name="proteines_ing"
                placeholder="Protéines (g)"
                value="<?= htmlspecialchars($ingredientEdit['proteines'] ?? '') ?>">
                <div id="msg_proteines_ing" class="msg"></div>

                <input type="number" step="0.01" id="lipides_ing" name="lipides_ing"
                placeholder="Lipides (g)"
                value="<?= htmlspecialchars($ingredientEdit['lipides'] ?? '') ?>">
                <div id="msg_lipides_ing" class="msg"></div>

                <input type="number" step="0.01" id="glucides_ing" name="glucides_ing"
                placeholder="Glucides (g)"
                value="<?= htmlspecialchars($ingredientEdit['glucides'] ?? '') ?>">
                <div id="msg_glucides_ing" class="msg"></div>

                <input type="number" id="saison_debut" name="saison_debut"
                placeholder="Saison début (mois)"
                value="<?= htmlspecialchars($ingredientEdit['saison_debut'] ?? '') ?>">
                <div id="msg_saison_debut" class="msg"></div>

                <input type="number" id="saison_fin" name="saison_fin"
                placeholder="Saison fin (mois)"
                value="<?= htmlspecialchars($ingredientEdit['saison_fin'] ?? '') ?>">
                <div id="msg_saison_fin" class="msg"></div>

                <input type="file" id="image_ing" name="image_ing" accept=".jpg,.jpeg,.png,.webp">
                <div id="msg_image_ing" class="msg"></div>

                <?php if(!empty($ingredientEdit['image'])){ ?>
                    <div style="margin:8px 0;">
                        <img src="../../images/<?= htmlspecialchars($ingredientEdit['image']) ?>" width="70" alt="ingredient">
                    </div>
                <?php } ?>

                <?php if($editIngredient){ ?>
                    <button class="btn-success" type="submit" name="modifier_ingredient">Modifier</button>
                <?php } else { ?>
                    <button class="btn-success" type="submit" name="add_ingredient">Ajouter</button>
                <?php } ?>

            </form>
        </div>

        <div class="card">
            <h2>Rechercher un ingrédient</h2>
            <div class="filter-form">
                <input type="text" id="liveSearchIngredient" placeholder="Tape le nom d’un ingrédient...">

                <form method="GET" style="display:flex; gap:10px; align-items:center;">
                    <select name="sort_ingredient">
                        <option value="nom_asc" <?= $sortIngredient === 'nom_asc' ? 'selected' : '' ?>>Nom A → Z</option>
                        <option value="nom_desc" <?= $sortIngredient === 'nom_desc' ? 'selected' : '' ?>>Nom Z → A</option>
                        <option value="calories_asc" <?= $sortIngredient === 'calories_asc' ? 'selected' : '' ?>>Calories croissantes</option>
                        <option value="calories_desc" <?= $sortIngredient === 'calories_desc' ? 'selected' : '' ?>>Calories décroissantes</option>
                    </select>

                    <input type="hidden" name="sort_recette" value="<?= htmlspecialchars($sortRecette) ?>">
                    <button type="submit" class="btn-success">Trier</button>
                    <a href="backoffice.php">Réinitialiser</a>
                </form>
            </div>
        </div>

        <div class="card">
            <h2>Liste ingrédients</h2>

            <table border="1" width="100%" id="tableIngredients">
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
                    <th>Action</th>
                </tr>

                <?php foreach($ingredients as $ing){ ?>
                <tr class="ingredient-row">
                    <td><?= $ing['id_ingredient'] ?></td>
                    <td>
                        <?php if(!empty($ing['image'])){ ?>
                            <img src="../../images/<?= htmlspecialchars($ing['image']) ?>" width="50" alt="ingredient">
                        <?php } ?>
                    </td>
                    <td class="ingredient-name"><?= htmlspecialchars($ing['nom_ingredient']) ?></td>
                    <td><?= htmlspecialchars($ing['type_ingredient']) ?></td>
                    <td><?= $ing['calories'] ?></td>
                    <td><?= $ing['proteines'] ?></td>
                    <td><?= $ing['lipides'] ?></td>
                    <td><?= $ing['glucides'] ?></td>
                    <td><?= $ing['saison_debut'] ?> - <?= $ing['saison_fin'] ?></td>
                    <td>
                        <a class="btn-action btn-danger-outline" href="?delete_ing=<?= $ing['id_ingredient'] ?>" onclick="return confirm('Supprimer cet ingrédient ?')">Supprimer</a>
                        <a class="btn-action" href="?edit_ing=<?= $ing['id_ingredient'] ?>">Modifier</a>
                    </td>
                </tr>
                <?php } ?>
            </table>
        </div>

        <div class="card">
            <h2>Ajouter / Modifier Recette</h2>

            <form method="POST" enctype="multipart/form-data" id="formRecette" novalidate>

                <input type="hidden" name="id_recette" value="<?= $recetteEdit['id_recette'] ?? '' ?>">

                <input type="text" id="nom_recette" name="nom_recette" placeholder="Nom recette"
                value="<?= htmlspecialchars($recetteEdit['nom_recette'] ?? '') ?>">
                <div id="msg_nom_recette" class="msg"></div>

                <textarea id="description" name="description" placeholder="Description"><?= htmlspecialchars($recetteEdit['description'] ?? '') ?></textarea>
                <div id="msg_description" class="msg"></div>
                <div id="compteur_desc"></div>

                <input type="number" step="0.01" id="calories_recette" name="calories" placeholder="Calories totales"
                value="<?= htmlspecialchars($recetteEdit['calories'] ?? '0') ?>" readonly>
                <div id="msg_calories_recette" class="msg"></div>

                <input type="number" step="0.01" id="proteines_recette" name="proteines" placeholder="Protéines totales"
                value="<?= htmlspecialchars($recetteEdit['proteines'] ?? '0') ?>" readonly>
                <div id="msg_proteines_recette" class="msg"></div>

                <input type="number" step="0.01" id="lipides_recette" name="lipides" placeholder="Lipides totaux"
                value="<?= htmlspecialchars($recetteEdit['lipides'] ?? '0') ?>" readonly>
                <div id="msg_lipides_recette" class="msg"></div>

                <input type="number" step="0.01" id="glucides_recette" name="glucides" placeholder="Glucides totaux"
                value="<?= htmlspecialchars($recetteEdit['glucides'] ?? '0') ?>" readonly>
                <div id="msg_glucides_recette" class="msg"></div>

                <input type="number" id="temps" name="temps_preparation" placeholder="Temps"
                value="<?= htmlspecialchars($recetteEdit['temps_preparation'] ?? '') ?>">
                <div id="msg_temps" class="msg"></div>

                <select id="categorie" name="categorie">
                    <option value="">Catégorie</option>
                    <option value="Healthy" <?= isset($recetteEdit) && $recetteEdit['categorie']=="Healthy" ? "selected" : "" ?>>Healthy</option>
                    <option value="Salade" <?= isset($recetteEdit) && $recetteEdit['categorie']=="Salade" ? "selected" : "" ?>>Salade</option>
                    <option value="Pate" <?= isset($recetteEdit) && $recetteEdit['categorie']=="Pate" ? "selected" : "" ?>>Pate</option>
                    <option value="Dessert" <?= isset($recetteEdit) && $recetteEdit['categorie']=="Dessert" ? "selected" : "" ?>>Dessert</option>
                </select>
                <div id="msg_categorie" class="msg"></div>

                <h3>Ingrédients</h3>
                <div id="msg_ingredients" class="msg"></div>

                <select name="ingredients[]" id="ingredients_recette" multiple style="height:120px">
                    <?php foreach($ingredients as $ing){ ?>
                        <option
                            value="<?= $ing['id_ingredient'] ?>"
                            data-calories="<?= htmlspecialchars($ing['calories']) ?>"
                            data-proteines="<?= htmlspecialchars($ing['proteines'] ?? 0) ?>"
                            data-lipides="<?= htmlspecialchars($ing['lipides'] ?? 0) ?>"
                            data-glucides="<?= htmlspecialchars($ing['glucides'] ?? 0) ?>"
                            <?= in_array($ing['id_ingredient'], $selectedIngredients) ? "selected" : "" ?>>
                            <?= htmlspecialchars($ing['nom_ingredient']) ?>
                        </option>
                    <?php } ?>
                </select>

                <input type="file" id="image" name="image" accept=".jpg,.jpeg,.png,.webp">
                <div id="msg_image" class="msg"></div>

                <?php if($editMode){ ?>
                    <button class="btn-success" type="submit" name="modifier">Modifier</button>
                <?php } else { ?>
                    <button class="btn-success" type="submit" name="ajouter">Ajouter</button>
                <?php } ?>

            </form>
        </div>

        <div class="card">
            <h2>Rechercher une recette</h2>
            <div class="filter-form">
                <input type="text" id="liveSearchRecette" placeholder="Tape le nom d’une recette...">

                <form method="GET" style="display:flex; gap:10px; align-items:center;">
                    <select name="sort_recette">
                        <option value="recent" <?= $sortRecette === 'recent' ? 'selected' : '' ?>>Plus récentes</option>
                        <option value="nom_asc" <?= $sortRecette === 'nom_asc' ? 'selected' : '' ?>>Nom A → Z</option>
                        <option value="nom_desc" <?= $sortRecette === 'nom_desc' ? 'selected' : '' ?>>Nom Z → A</option>
                        <option value="calories_asc" <?= $sortRecette === 'calories_asc' ? 'selected' : '' ?>>Calories croissantes</option>
                        <option value="calories_desc" <?= $sortRecette === 'calories_desc' ? 'selected' : '' ?>>Calories décroissantes</option>
                        <option value="temps_asc" <?= $sortRecette === 'temps_asc' ? 'selected' : '' ?>>Temps croissant</option>
                        <option value="temps_desc" <?= $sortRecette === 'temps_desc' ? 'selected' : '' ?>>Temps décroissant</option>
                    </select>

                    <input type="hidden" name="sort_ingredient" value="<?= htmlspecialchars($sortIngredient) ?>">
                    <button type="submit" class="btn-success">Trier</button>
                    <a href="backoffice.php">Réinitialiser</a>
                </form>
            </div>
        </div>

        <div class="card">
            <h2>Liste Recettes</h2>

            <table border="1" width="100%" id="tableRecettes">
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
                    <th>Actions</th>
                </tr>

                <?php foreach($recettes as $r){ ?>
                <tr class="recette-row">
                    <td><?= $r['id_recette'] ?></td>

                    <td>
                        <?php if(!empty($r['image'])){ ?>
                            <img src="../../images/<?= htmlspecialchars($r['image']) ?>" width="60" alt="image recette">
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
                            WHERE recette_ingredient.recette_id=?");
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
                        <a class="btn-action" href="?edit=<?= $r['id_recette'] ?>">Modifier</a>
                        <a class="btn-action btn-danger-outline" href="?delete=<?= $r['id_recette'] ?>" onclick="return confirm('Supprimer cette recette ?')">Supprimer</a>
                    </td>
                </tr>
                <?php } ?>
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
        return /^[A-Za-zÀ-ÿ0-9' -]{3,100}$/u.test(val);
    }

    function nombreValide(val, min, max) {
        const n = parseInt(val, 10);
        return !isNaN(n) && n >= min && n <= max;
    }

    function nombreDecimalValide(val, min, max) {
        const n = parseFloat(val);
        return !isNaN(n) && n >= min && n <= max;
    }

    function imageValide(input, obligatoire = false) {
        const file = input.files[0];

        if (!file) {
            if (obligatoire) {
                msg("msg_image", "❌ image obligatoire", "red");
                bordure(input, false);
                return false;
            } else {
                msg("msg_image", "", "");
                input.classList.remove("input-error", "input-valid");
                return true;
            }
        }

        const types = ["image/jpeg", "image/png", "image/jpg", "image/webp"];

        if (!types.includes(file.type)) {
            msg("msg_image", "❌ formats autorisés : jpg, jpeg, png, webp", "red");
            bordure(input, false);
            return false;
        }

        if (file.size > 2 * 1024 * 1024) {
            msg("msg_image", "❌ taille max 2 Mo", "red");
            bordure(input, false);
            return false;
        }

        msg("msg_image", "✔ image valide", "green");
        bordure(input, true);
        return true;
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
        const imageIng = document.getElementById("image_ing");

        function verifierNomIngredient() {
            const val = nomIng.value.trim();
            if (val === "") {
                msg("msg_nom_ing", "❌ nom obligatoire", "red");
                bordure(nomIng, false);
                return false;
            }
            if (!texteNomValide(val)) {
                msg("msg_nom_ing", "❌ 3 à 100 caractères autorisés", "red");
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

        function verifierImageIngredient() {
            const file = imageIng.files[0];
            if (!file) {
                msg("msg_image_ing", "", "");
                imageIng.classList.remove("input-error", "input-valid");
                return true;
            }
            const types = ["image/jpeg", "image/png", "image/jpg", "image/webp"];
            if (!types.includes(file.type)) {
                msg("msg_image_ing", "❌ formats autorisés : jpg, jpeg, png, webp", "red");
                bordure(imageIng, false);
                return false;
            }
            if (file.size > 2 * 1024 * 1024) {
                msg("msg_image_ing", "❌ taille max 2 Mo", "red");
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
                verifierImageIngredient();

            if (!ok) e.preventDefault();
        });
    }

    const formRecette = document.getElementById("formRecette");

    if (formRecette) {
        const nom = document.getElementById("nom_recette");
        const description = document.getElementById("description");
        const compteur = document.getElementById("compteur_desc");
        const calories = document.getElementById("calories_recette");
        const proteinesRecette = document.getElementById("proteines_recette");
        const lipidesRecette = document.getElementById("lipides_recette");
        const glucidesRecette = document.getElementById("glucides_recette");
        const temps = document.getElementById("temps");
        const categorie = document.getElementById("categorie");
        const ingredients = document.getElementById("ingredients_recette");
        const image = document.getElementById("image");
        const modeModification = <?= $editMode ? 'true' : 'false' ?>;

        function calculerValeursNutritionnellesRecette() {
            let totalCalories = 0;
            let totalProteines = 0;
            let totalLipides = 0;
            let totalGlucides = 0;

            const selected = Array.from(ingredients.options).filter(option => option.selected);

            selected.forEach(option => {
                totalCalories += parseFloat(option.dataset.calories || 0);
                totalProteines += parseFloat(option.dataset.proteines || 0);
                totalLipides += parseFloat(option.dataset.lipides || 0);
                totalGlucides += parseFloat(option.dataset.glucides || 0);
            });

            calories.value = totalCalories.toFixed(2);
            proteinesRecette.value = totalProteines.toFixed(2);
            lipidesRecette.value = totalLipides.toFixed(2);
            glucidesRecette.value = totalGlucides.toFixed(2);
        }

        function verifierNomRecette() {
            const val = nom.value.trim();
            if (val === "") {
                msg("msg_nom_recette", "❌ nom recette obligatoire", "red");
                bordure(nom, false);
                return false;
            }
            if (!texteNomValide(val)) {
                msg("msg_nom_recette", "❌ 3 à 100 caractères autorisés", "red");
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

            if (val === "") {
                msg("msg_description", "❌ description obligatoire", "red");
                bordure(description, false);
                return false;
            }

            if (val.length < 10 || val.length > 500) {
                msg("msg_description", "❌ entre 10 et 500 caractères", "red");
                bordure(description, false);
                return false;
            }

            msg("msg_description", "✔ description correcte", "green");
            bordure(description, true);
            return true;
        }

        function verifierCaloriesRecette() {
            const val = calories.value.trim();
            if (val === "") {
                msg("msg_calories_recette", "❌ calories obligatoires", "red");
                bordure(calories, false);
                return false;
            }
            if (!nombreDecimalValide(val, 0, 5000)) {
                msg("msg_calories_recette", "❌ valeur entre 0 et 5000", "red");
                bordure(calories, false);
                return false;
            }
            msg("msg_calories_recette", "✔ calories valides", "green");
            bordure(calories, true);
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
                msg("msg_temps", "❌ temps entre 1 et 600 minutes", "red");
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
            const selected = Array.from(ingredients.options).filter(option => option.selected);

            calculerValeursNutritionnellesRecette();

            if (selected.length === 0) {
                msg("msg_ingredients", "❌ sélectionner au moins un ingrédient", "red");
                bordure(ingredients, false);
                calories.value = "0.00";
                proteinesRecette.value = "0.00";
                lipidesRecette.value = "0.00";
                glucidesRecette.value = "0.00";
                return false;
            }

            msg("msg_ingredients", "✔ ingrédients sélectionnés", "green");
            bordure(ingredients, true);
            return true;
        }

        function verifierImage() {
            return imageValide(image, !modeModification);
        }

        nom.addEventListener("input", verifierNomRecette);
        description.addEventListener("input", verifierDescription);
        temps.addEventListener("input", verifierTemps);
        categorie.addEventListener("change", verifierCategorie);
        ingredients.addEventListener("change", verifierIngredients);
        image.addEventListener("change", verifierImage);

        calculerValeursNutritionnellesRecette();
        verifierDescription();

        formRecette.addEventListener("submit", function(e){
            const ok =
                verifierNomRecette() &&
                verifierDescription() &&
                verifierCaloriesRecette() &&
                verifierTemps() &&
                verifierCategorie() &&
                verifierIngredients() &&
                verifierImage();

            if (!ok) e.preventDefault();
        });
    }

    /* ===================== RECHERCHE INSTANTANEE INGREDIENTS ===================== */

    const liveSearchIngredient = document.getElementById("liveSearchIngredient");
    const ingredientRows = document.querySelectorAll("#tableIngredients .ingredient-row");

    if (liveSearchIngredient) {
        liveSearchIngredient.addEventListener("input", function () {
            const valeur = this.value.trim().toLowerCase();

            ingredientRows.forEach(function(row){
                const nomCell = row.querySelector(".ingredient-name");
                const nom = nomCell ? nomCell.textContent.toLowerCase() : "";

                if (valeur === "" || nom.includes(valeur)) {
                    row.style.display = "";
                } else {
                    row.style.display = "none";
                }
            });
        });
    }

    /* ===================== RECHERCHE INSTANTANEE RECETTES ===================== */

    const liveSearchRecette = document.getElementById("liveSearchRecette");
    const recetteRows = document.querySelectorAll("#tableRecettes .recette-row");

    if (liveSearchRecette) {
        liveSearchRecette.addEventListener("input", function () {
            const valeur = this.value.trim().toLowerCase();

            recetteRows.forEach(function(row){
                const nomCell = row.querySelector(".recette-name");
                const nom = nomCell ? nomCell.textContent.toLowerCase() : "";

                if (valeur === "" || nom.includes(valeur)) {
                    row.style.display = "";
                } else {
                    row.style.display = "none";
                }
            });
        });
    }

});
</script>

</body>
</html>