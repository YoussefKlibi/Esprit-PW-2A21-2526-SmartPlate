
<?php
require_once "../../config.php";

/* ===================== AJOUTER RECETTE ===================== */

if(isset($_POST['ajouter'])){

    $nom = $_POST['nom_recette'];
    $description = $_POST['description'];
    $ingredients = $_POST['ingredients'];
    $calories = $_POST['calories'];
    $temps = $_POST['temps_preparation'];
    $categorie = $_POST['categorie'];

    $image = $_FILES['image']['name'];
    $tmp = $_FILES['image']['tmp_name'];

    move_uploaded_file($tmp,"../../images/".$image);

    $sql = "INSERT INTO recette 
    (nom_recette, description, ingredients, calories, temps_preparation, categorie, image)
    VALUES (:nom, :description, :ingredients, :calories, :temps, :categorie, :image)";

    $query = $pdo->prepare($sql);

    $query->execute([
        'nom'=>$nom,
        'description'=>$description,
        'ingredients'=>$ingredients,
        'calories'=>$calories,
        'temps'=>$temps,
        'categorie'=>$categorie,
        'image'=>$image
    ]);

    header("Location: backoffice.php");
    exit();
}

/* ===================== SUPPRIMER ===================== */

if(isset($_GET['delete'])){

    $id = $_GET['delete'];

    $sql = "DELETE FROM recette WHERE id_recette = :id";
    $query = $pdo->prepare($sql);
    $query->execute(['id'=>$id]);

    header("Location: backoffice.php");
    exit();
}

/* ===================== MODE EDIT ===================== */

$editMode = false;
$recetteEdit = null;

if(isset($_GET['edit'])){

    $editMode = true;
    $id = $_GET['edit'];

    $sql = "SELECT * FROM recette WHERE id_recette = :id";
    $query = $pdo->prepare($sql);
    $query->execute(['id'=>$id]);

    $recetteEdit = $query->fetch();
}

/* ===================== MODIFIER ===================== */

if(isset($_POST['modifier'])){

    $id = $_POST['id_recette'];

    $nom = $_POST['nom_recette'];
    $description = $_POST['description'];
    $ingredients = $_POST['ingredients'];
    $calories = $_POST['calories'];
    $temps = $_POST['temps_preparation'];
    $categorie = $_POST['categorie'];

    if(!empty($_FILES['image']['name'])){

        $image = $_FILES['image']['name'];
        $tmp = $_FILES['image']['tmp_name'];
        move_uploaded_file($tmp,"../../images/".$image);

        $sql = "UPDATE recette SET 
        nom_recette=:nom,
        description=:description,
        ingredients=:ingredients,
        calories=:calories,
        temps_preparation=:temps,
        categorie=:categorie,
        image=:image
        WHERE id_recette=:id";

        $params = [
            'nom'=>$nom,
            'description'=>$description,
            'ingredients'=>$ingredients,
            'calories'=>$calories,
            'temps'=>$temps,
            'categorie'=>$categorie,
            'image'=>$image,
            'id'=>$id
        ];

    } else {

        $sql = "UPDATE recette SET 
        nom_recette=:nom,
        description=:description,
        ingredients=:ingredients,
        calories=:calories,
        temps_preparation=:temps,
        categorie=:categorie
        WHERE id_recette=:id";

        $params = [
            'nom'=>$nom,
            'description'=>$description,
            'ingredients'=>$ingredients,
            'calories'=>$calories,
            'temps'=>$temps,
            'categorie'=>$categorie,
            'id'=>$id
        ];
    }

    $query = $pdo->prepare($sql);
    $query->execute($params);

    header("Location: backoffice.php");
    exit();
}

/* ===================== AFFICHER ===================== */

$sql = "SELECT * FROM recette";
$recettes = $pdo->query($sql);

?>

<!DOCTYPE html>
<html>

<head>
<meta charset="UTF-8">
<title>Admin Recettes</title>
<link rel="stylesheet" href="templates/templateback.css">
</head>

<body>

<!-- SIDEBAR -->
<aside class="admin-sidebar">

<div class="sidebar-header">
<img src="../../Logo/logo.png" height="30"> SmartPlate Admin
</div>

<div class="sidebar-menu">
<div class="menu-category">Menu</div>

<a href="#" class="menu-item active">🍽 Gestion Recettes</a>
<a href="#" class="menu-item">📊 Dashboard</a>
<a href="#" class="menu-item">👤 Utilisateurs</a>

</div>

</aside>


<!-- MAIN CONTENT -->

<div class="main-content">

<!-- TOPBAR -->

<div class="topbar">

<h3>Gestion des Recettes</h3>

<div class="admin-profile">
<img src="https://i.pravatar.cc/40">
Admin
</div>

</div>


<!-- DASHBOARD -->

<div class="dashboard-container">

<div class="page-header">

<div>
<h1>Recettes</h1>
<p>Ajouter, modifier ou supprimer des recettes</p>
</div>

</div>


<!-- FORMULAIRE -->

<div class="card">

<h2>Ajouter / Modifier Recette</h2>

<form method="POST" enctype="multipart/form-data">
    <input type="hidden" name="id_recette" value="<?= $recetteEdit['id_recette'] ?? '' ?>">

    <div class="form-group">
        <label>Nom de la recette</label>
        <input type="text" name="nom_recette" value="<?= $recetteEdit['nom_recette'] ?? '' ?>" placeholder="Ex: Salade César" required>
    </div>

    <div class="form-group">
        <label>Catégorie</label>
        <select name="categorie" required>
            <option value="">Choisir catégorie</option>
            <option value="Healthy" <?= (isset($recetteEdit) && $recetteEdit['categorie']=='Healthy')?'selected':'' ?>>Healthy</option>
            <option value="Salade" <?= (isset($recetteEdit) && $recetteEdit['categorie']=='Salade')?'selected':'' ?>>Salade</option>
            <option value="Pate" <?= (isset($recetteEdit) && $recetteEdit['categorie']=='Pate')?'selected':'' ?>>Pate</option>
            <option value="Dessert" <?= (isset($recetteEdit) && $recetteEdit['categorie']=='Dessert')?'selected':'' ?>>Dessert</option>
        </select>
    </div>

    <div class="form-group">
        <label>Calories (kcal)</label>
        <input type="number" name="calories" value="<?= $recetteEdit['calories'] ?? '' ?>" placeholder="Ex: 450">
    </div>

    <div class="form-group">
        <label>Temps de préparation (min)</label>
        <input type="number" name="temps_preparation" value="<?= $recetteEdit['temps_preparation'] ?? '' ?>" placeholder="Ex: 15">
    </div>

    <div class="form-group full-width">
        <label>Description de la recette</label>
        <textarea name="description" placeholder="Courte description..."><?= $recetteEdit['description'] ?? '' ?></textarea>
    </div>

    <div class="form-group full-width">
        <label>Ingrédients</label>
        <textarea name="ingredients" placeholder="Liste des ingrédients..."><?= $recetteEdit['ingredients'] ?? '' ?></textarea>
    </div>

    <div class="form-group full-width">
        <label>Image de la recette</label>
        <input type="file" name="image">
    </div>

    <div class="form-group full-width">
        <?php if($editMode){ ?>
            <button class="btn-success" type="submit" name="modifier">Modifier la recette</button>
        <?php } else { ?>
            <button class="btn-success" type="submit" name="ajouter">Ajouter la recette</button>
        <?php } ?>
    </div>
</form>

</div>


<br>


<!-- TABLEAU -->

<div class="card">

<h2>Liste des Recettes</h2>

<table style="width:100%; text-align:center; border-collapse:collapse;">

<tr>
<th>ID</th>
<th>Image</th>
<th>Nom</th>
<th>Description</th>
<th>Calories</th>
<th>Temps</th>
<th>Catégorie</th>
<th>Actions</th>
</tr>

<?php foreach($recettes as $r){ ?>

<tr>

<td><?= $r['id_recette']; ?></td>

<td>
<img src="../../images/<?= $r['image']; ?>" width="60">
</td>

<td><?= $r['nom_recette']; ?></td>

<td><?= $r['description']; ?></td>

<td><?= $r['calories']; ?></td>

<td><?= $r['temps_preparation']; ?> min</td>

<td><?= $r['categorie']; ?></td>

<td>

<a class="btn-action" href="?edit=<?= $r['id_recette']; ?>">Modifier</a>

<a class="btn-action btn-danger-outline" href="?delete=<?= $r['id_recette']; ?>">Supprimer</a>

</td>

</tr>

<?php } ?>

</table>

</div>


</div>

</div>

</body>
</html>

