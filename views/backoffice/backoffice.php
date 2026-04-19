<?php
require_once "../../config.php";

/* charger ingredients */

$ingredients=$pdo->query("SELECT * FROM ingredient ORDER BY nom_ingredient")->fetchAll(PDO::FETCH_ASSOC);

/* charger recettes */

$recettes=$pdo->query("SELECT * FROM recette")->fetchAll(PDO::FETCH_ASSOC);


/* ===================== CRUD INGREDIENT ===================== */

/* Ajouter ingrédient */

if(isset($_POST['add_ingredient'])){

$nom=$_POST['nom_ingredient'];
$type=$_POST['type_ingredient'];
$cal=$_POST['calories_ing'];
$debut=$_POST['saison_debut'];
$fin=$_POST['saison_fin'];

$sql="INSERT INTO ingredient(nom_ingredient,type_ingredient,calories,saison_debut,saison_fin)
VALUES(?,?,?,?,?)";

$pdo->prepare($sql)->execute([$nom,$type,$cal,$debut,$fin]);

header("Location: backoffice.php");
exit();
}

/* Supprimer ingrédient */

if(isset($_GET['delete_ing'])){

$id=$_GET['delete_ing'];

$pdo->prepare("DELETE FROM recette_ingredient WHERE ingredient_id=?")->execute([$id]);
$pdo->prepare("DELETE FROM ingredient WHERE id_ingredient=?")->execute([$id]);

header("Location: backoffice.php");
exit();
}
/* ===================== EDIT INGREDIENT ===================== */

$editIngredient=false;
$ingredientEdit=null;

if(isset($_GET['edit_ing'])){

$editIngredient=true;

$id=$_GET['edit_ing'];

$q=$pdo->prepare("SELECT * FROM ingredient WHERE id_ingredient=?");
$q->execute([$id]);

$ingredientEdit=$q->fetch(PDO::FETCH_ASSOC);

}
/* ===================== MODIFIER INGREDIENT ===================== */

if(isset($_POST['modifier_ingredient'])){

$id=$_POST['id_ingredient'];
$nom=$_POST['nom_ingredient'];
$type=$_POST['type_ingredient'];
$cal=$_POST['calories_ing'];
$debut=$_POST['saison_debut'];
$fin=$_POST['saison_fin'];

$sql="UPDATE ingredient SET
nom_ingredient=?,
type_ingredient=?,
calories=?,
saison_debut=?,
saison_fin=?
WHERE id_ingredient=?";

$pdo->prepare($sql)->execute([$nom,$type,$cal,$debut,$fin,$id]);

header("Location: backoffice.php");
exit();
}



/* ===================== AJOUTER RECETTE ===================== */

if(isset($_POST['ajouter'])){

$nom=$_POST['nom_recette'];
$description=$_POST['description'];
$calories=$_POST['calories'];
$temps=$_POST['temps_preparation'];
$categorie=$_POST['categorie'];

$image=$_FILES['image']['name'];
$tmp=$_FILES['image']['tmp_name'];

move_uploaded_file($tmp,"../../images/".$image);

$sql="INSERT INTO recette
(nom_recette,description,calories,temps_preparation,categorie,image)
VALUES(?,?,?,?,?,?)";

$pdo->prepare($sql)->execute([$nom,$description,$calories,$temps,$categorie,$image]);

$recette_id=$pdo->lastInsertId();

/* ingrédients recette */

if(!empty($_POST['ingredients'])){

foreach($_POST['ingredients'] as $ing){

$pdo->prepare("INSERT INTO recette_ingredient(recette_id,ingredient_id)
VALUES(?,?)")->execute([$recette_id,$ing]);

}

}

header("Location: backoffice.php");
exit();
}

/* ===================== SUPPRIMER RECETTE ===================== */

if(isset($_GET['delete'])){

$id=$_GET['delete'];

$pdo->prepare("DELETE FROM recette_ingredient WHERE recette_id=?")->execute([$id]);
$pdo->prepare("DELETE FROM recette WHERE id_recette=?")->execute([$id]);

header("Location: backoffice.php");
exit();
}

/* ===================== EDIT RECETTE ===================== */

$editMode=false;
$recetteEdit=null;
$selectedIngredients=[];

if(isset($_GET['edit'])){

$editMode=true;
$id=$_GET['edit'];

$q=$pdo->prepare("SELECT * FROM recette WHERE id_recette=?");
$q->execute([$id]);

$recetteEdit=$q->fetch();

$q2=$pdo->prepare("SELECT ingredient_id FROM recette_ingredient WHERE recette_id=?");
$q2->execute([$id]);

foreach($q2 as $r){
$selectedIngredients[]=$r['ingredient_id'];
}

}

/* ===================== MODIFIER RECETTE ===================== */

if(isset($_POST['modifier'])){

$id=$_POST['id_recette'];

$nom=$_POST['nom_recette'];
$description=$_POST['description'];
$calories=$_POST['calories'];
$temps=$_POST['temps_preparation'];
$categorie=$_POST['categorie'];

if(!empty($_FILES['image']['name'])){

$image=$_FILES['image']['name'];
$tmp=$_FILES['image']['tmp_name'];

move_uploaded_file($tmp,"../../images/".$image);

$sql="UPDATE recette SET
nom_recette=?,description=?,calories=?,temps_preparation=?,categorie=?,image=?
WHERE id_recette=?";

$pdo->prepare($sql)->execute([$nom,$description,$calories,$temps,$categorie,$image,$id]);

}else{

$sql="UPDATE recette SET
nom_recette=?,description=?,calories=?,temps_preparation=?,categorie=?
WHERE id_recette=?";

$pdo->prepare($sql)->execute([$nom,$description,$calories,$temps,$categorie,$id]);

}

/* update ingrédients */

$pdo->prepare("DELETE FROM recette_ingredient WHERE recette_id=?")->execute([$id]);

if(!empty($_POST['ingredients'])){

foreach($_POST['ingredients'] as $ing){

$pdo->prepare("INSERT INTO recette_ingredient(recette_id,ingredient_id)
VALUES(?,?)")->execute([$id,$ing]);

}

}

header("Location: backoffice.php");
exit();
}

/* ===================== LISTE ===================== */

$recettes=$pdo->query("SELECT * FROM recette");

?>

<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>Admin SmartPlate</title>
<link rel="stylesheet" href="templates/templateback.css">
</head>

<body>

<!-- ===== SIDEBAR ===== -->

<div class="admin-sidebar">

<div class="sidebar-header">
🍽 SmartPlate Admin
</div>

<div class="sidebar-menu">

<div class="menu-category">Menu</div>

<a href="backoffice.php" class="menu-item active">
🍴 Gestion Recettes
</a>

<a href="dashboard.php" class="menu-item">
📊 Dashboard
</a>

<a href="utilisateurs.php" class="menu-item">
👤 Utilisateurs
</a>

</div>

</div>


<!-- ===== CONTENU PRINCIPAL ===== -->

<div class="main-content">

<!-- Topbar -->

<div class="topbar">

<div>Administration</div>

<div class="admin-profile">
<img src="https://i.pravatar.cc/40">
Admin
</div>

</div>


<!-- ===== PAGE CONTENT ===== -->

<div class="dashboard-container">

<!-- ===================== INGREDIENT ===================== -->

<div class="card">

<h2><?= $editIngredient ? "Modifier ingrédient" : "Ajouter ingrédient" ?></h2>

<form method="POST">

<input type="hidden" name="id_ingredient"
value="<?= $ingredientEdit['id_ingredient'] ?? '' ?>">

<input type="text" id="nom_ing" name="nom_ingredient"
placeholder="Nom ingrédient"
value="<?= $ingredientEdit['nom_ingredient'] ?? '' ?>">

<div id="msg_nom_ing" class="msg"></div>

<div class="form-group">

<select name="type_ingredient" id="type_ing">

<option value="">Choisir type ingrédient</option>

<option value="Legume"
<?= isset($ingredientEdit) && $ingredientEdit['type_ingredient']=="Legume" ? "selected":"" ?>>
Légume
</option>

<option value="Fruit"
<?= isset($ingredientEdit) && $ingredientEdit['type_ingredient']=="Fruit" ? "selected":"" ?>>
Fruit
</option>

<option value="Viande"
<?= isset($ingredientEdit) && $ingredientEdit['type_ingredient']=="Viande" ? "selected":"" ?>>
Viande
</option>

<option value="Poisson"
<?= isset($ingredientEdit) && $ingredientEdit['type_ingredient']=="Poisson" ? "selected":"" ?>>
Poisson
</option>

<option value="Produit laitier"
<?= isset($ingredientEdit) && $ingredientEdit['type_ingredient']=="Produit laitier" ? "selected":"" ?>>
Produit laitier
</option>

<option value="Epice"
<?= isset($ingredientEdit) && $ingredientEdit['type_ingredient']=="Epice" ? "selected":"" ?>>
Épice
</option>

<option value="Autre"
<?= isset($ingredientEdit) && $ingredientEdit['type_ingredient']=="Autre" ? "selected":"" ?>>
Autre
</option>

</select>

<div id="msg_type_ing" class="msg"></div>

</div>

<input type="number" id="cal_ing" name="calories_ing"
placeholder="Calories"
value="<?= $ingredientEdit['calories'] ?? '' ?>">

<div id="msg_cal_ing" class="msg"></div>

<input type="number" id="saison_debut" name="saison_debut"
placeholder="Saison début (mois)"
value="<?= $ingredientEdit['saison_debut'] ?? '' ?>">

<div id="msg_saison_debut" class="msg"></div>

<input type="number" id="saison_fin" name="saison_fin"
placeholder="Saison fin (mois)"
value="<?= $ingredientEdit['saison_fin'] ?? '' ?>">

<div id="msg_saison_fin" class="msg"></div>

<?php if($editIngredient){ ?>

<button class="btn-success" name="modifier_ingredient">
Modifier
</button>

<?php } else { ?>

<button class="btn-success" name="add_ingredient">
Ajouter
</button>

<?php } ?>

</form>

</div>


<!-- ===================== LISTE INGREDIENT ===================== -->

<div class="card">

<h2>Liste ingrédients</h2>

<table border="1" width="100%">

<tr>
<th>ID</th>
<th>Nom</th>
<th>Type</th>
<th>Calories</th>
<th>Saison</th>
<th>Action</th>
</tr>

<?php foreach($ingredients as $ing){ ?>

<tr>

<td><?= $ing['id_ingredient'] ?></td>
<td><?= $ing['nom_ingredient'] ?></td>
<td><?= $ing['type_ingredient'] ?></td>
<td><?= $ing['calories'] ?></td>
<td><?= $ing['saison_debut'] ?> - <?= $ing['saison_fin'] ?></td>

<td>
<a class="btn-action btn-danger-outline" href="?delete_ing=<?= $ing['id_ingredient'] ?>">
Supprimer
</a>
<a class="btn-action" href="?edit_ing=<?= $ing['id_ingredient'] ?>">
Modifier
</a>
</td>

</tr>

<?php } ?>

</table>

</div>


<!-- ===================== RECETTE ===================== -->

<div class="card">

<h2>Ajouter / Modifier Recette</h2>

<form method="POST" enctype="multipart/form-data">

<input type="hidden" name="id_recette" value="<?= $recetteEdit['id_recette'] ?? '' ?>">

<input type="text" id="nom_recette" name="nom_recette" placeholder="Nom recette"
value="<?= $recetteEdit['nom_recette'] ?? '' ?>">

<div id="msg_nom_recette" class="msg"></div>

<textarea id="description" name="description" placeholder="Description"><?= $recetteEdit['description'] ?? '' ?></textarea>

<div id="msg_description" class="msg"></div>
<div id="compteur_desc"></div>

<input type="number" id="calories_recette" name="calories" placeholder="Calories"
value="<?= $recetteEdit['calories'] ?? '' ?>">

<div id="msg_calories_recette" class="msg"></div>

<input type="number" id="temps" name="temps_preparation" placeholder="Temps"
value="<?= $recetteEdit['temps_preparation'] ?? '' ?>">

<div id="msg_temps" class="msg"></div>

<select id="categorie" name="categorie">

<option value="">Catégorie</option>
<option value="Healthy">Healthy</option>
<option value="Salade">Salade</option>
<option value="Pate">Pate</option>
<option value="Dessert">Dessert</option>

</select>

<div id="msg_categorie" class="msg"></div>

<h3>Ingrédients</h3>

<div id="msg_ingredients" class="msg"></div>

<select name="ingredients[]" multiple style="height:120px">

<?php foreach($ingredients as $ing){ ?>

<option value="<?= $ing['id_ingredient'] ?>">
<?= $ing['nom_ingredient'] ?>
</option>

<?php } ?>

</select>

<input type="file" id="image" name="image">

<div id="msg_image" class="msg"></div>

<?php if($editMode){ ?>

<button class="btn-success" name="modifier">Modifier</button>

<?php } else { ?>

<button class="btn-success" name="ajouter">Ajouter</button>

<?php } ?>

</form>

</div>


<!-- ===================== LISTE RECETTES ===================== -->

<div class="card">

<h2>Liste Recettes</h2>

<table border="1" width="100%">

<tr>
<th>ID</th>
<th>Image</th>
<th>Nom</th>
<th>Description</th>
<th>Ingrédients</th>
<th>Calories</th>
<th>Temps</th>
<th>Catégorie</th>
<th>Actions</th>
</tr>

<?php foreach($recettes as $r){ ?>

<tr>

<td><?= $r['id_recette'] ?></td>

<td>
<img src="../../images/<?= $r['image'] ?>" width="60">
</td>

<td><?= $r['nom_recette'] ?></td>

<td><?= $r['description'] ?></td>

<td>

<?php

$q=$pdo->prepare("
SELECT nom_ingredient
FROM ingredient
JOIN recette_ingredient
ON ingredient.id_ingredient=recette_ingredient.ingredient_id
WHERE recette_ingredient.recette_id=?");

$q->execute([$r['id_recette']]);

while($i=$q->fetch()){
echo $i['nom_ingredient']." ";
}

?>

</td>

<td><?= $r['calories'] ?></td>

<td><?= $r['temps_preparation'] ?> min</td>

<td><?= $r['categorie'] ?></td>

<td>

<a class="btn-action" href="?edit=<?= $r['id_recette'] ?>">Modifier</a>

<a class="btn-action btn-danger-outline" href="?delete=<?= $r['id_recette'] ?>">
Supprimer
</a>

</td>

</tr>

<?php } ?>

</table>

</div>

</div>

</div>
<script>

document.addEventListener("DOMContentLoaded",function(){

let form=document.querySelector("form");
let bouton=document.querySelector("button[type=submit]");

/* fonction message */

function msg(id,texte,couleur){

let zone=document.getElementById(id);

if(zone){
zone.innerHTML=texte;
zone.style.color=couleur;
}

}

/* fonction bordure */

function bordure(champ,valide){

champ.classList.remove("input-error","input-valid");

if(valide){
champ.classList.add("input-valid");
}
else{
champ.classList.add("input-error");
}

}

/* ================= NOM RECETTE ================= */

let nom=document.getElementById("nom_recette");

nom.addEventListener("keyup",function(){

let val=this.value.trim();

if(val.length<3){

msg("msg_nom_recette","❌ minimum 3 caractères","red");
bordure(this,false);

}else{

msg("msg_nom_recette","✔ valide","green");
bordure(this,true);

}

verifierForm();

});

/* ================= DESCRIPTION ================= */

let description=document.getElementById("description");
let compteur=document.getElementById("compteur_desc");

description.addEventListener("keyup",function(){

let val=this.value.trim();

compteur.innerHTML=val.length+" caractères";

if(val.length<10){

msg("msg_description","❌ minimum 10 caractères","red");
bordure(this,false);

}else{

msg("msg_description","✔ description correcte","green");
bordure(this,true);

}

verifierForm();

});

/* ================= CALORIES ================= */

let calories=document.getElementById("calories_recette");

calories.addEventListener("keyup",function(){

let val=parseInt(this.value);

if(isNaN(val)||val<=0){

msg("msg_calories_recette","❌ nombre invalide","red");
bordure(this,false);

}else{

msg("msg_calories_recette","✔ valide","green");
bordure(this,true);

}

verifierForm();

});

/* ================= TEMPS ================= */

let temps=document.getElementById("temps");

temps.addEventListener("keyup",function(){

let val=parseInt(this.value);

if(isNaN(val)||val<=0){

msg("msg_temps","❌ temps invalide","red");
bordure(this,false);

}else{

msg("msg_temps","✔ valide","green");
bordure(this,true);

}

verifierForm();

});

/* ================= CATEGORIE ================= */

let categorie=document.getElementById("categorie");

categorie.addEventListener("change",function(){

if(this.value===""){

msg("msg_categorie","⚠ choisir une catégorie","red");
bordure(this,false);

}else{

msg("msg_categorie","✔ sélectionnée","green");
bordure(this,true);

}

verifierForm();

});

/* ================= INGREDIENTS ================= */

let ingredients=document.querySelector("select[name='ingredients[]']");

ingredients.forEach(function(box){

box.addEventListener("change",verifierIngredients);

});

function verifierIngredients(){

let selected = Array.from(ingredients.options).filter(o => o.selected);

if(selected.length===0){

msg("msg_ingredients","❌ sélectionner au moins 1 ingrédient","red");
return false;

}else{

msg("msg_ingredients","✔ ingrédient sélectionné","green");
return true;

}

}

/* ================= IMAGE ================= */

let image=document.getElementById("image");

image.addEventListener("change",function(){

let file=this.files[0];

if(!file) return;

let types=["image/jpeg","image/png","image/jpg"];

if(!types.includes(file.type)){

msg("msg_image","❌ format jpg ou png","red");
bordure(this,false);

}else if(file.size>2000000){

msg("msg_image","❌ taille max 2MB","red");
bordure(this,false);

}else{

msg("msg_image","✔ image valide","green");
bordure(this,true);

}

verifierForm();

});

/* ================= VERIFIER FORM ================= */

function verifierForm(){

let valide=true;

document.querySelectorAll(".input-error").forEach(function(){
valide=false;
});

if(!verifierIngredients()){
valide=false;
}

bouton.disabled=!valide;

}

});
/* ================= NOM INGREDIENT ================= */

let nomIng=document.getElementById("nom_ing");

if(nomIng){

nomIng.addEventListener("keyup",function(){

let val=this.value.trim();

if(val.length<3){

msg("msg_nom_ing","❌ minimum 3 lettres","red");
bordure(this,false);

}else{

msg("msg_nom_ing","✔ nom valide","green");
bordure(this,true);

}

});

}

/* ================= TYPE INGREDIENT ================= */

let typeIng=document.getElementById("type_ing");

if(typeIng){

typeIng.addEventListener("change",function(){

if(this.value===""){

msg("msg_type_ing","❌ choisir un type","red");
bordure(this,false);

}else{

msg("msg_type_ing","✔ type valide","green");
bordure(this,true);

}

});

}

/* ================= CALORIES INGREDIENT ================= */

let calIng=document.getElementById("cal_ing");

if(calIng){

calIng.addEventListener("keyup",function(){

let val=parseInt(this.value);

if(isNaN(val)||val<=0){

msg("msg_cal_ing","❌ calories invalide","red");
bordure(this,false);

}else{

msg("msg_cal_ing","✔ valide","green");
bordure(this,true);

}

});

}

/* ================= SAISON DEBUT ================= */

let debut=document.getElementById("saison_debut");

if(debut){

debut.addEventListener("keyup",function(){

let val=parseInt(this.value);

if(isNaN(val)||val<1||val>12){

msg("msg_saison_debut","❌ mois 1 à 12","red");
bordure(this,false);

}else{

msg("msg_saison_debut","✔ valide","green");
bordure(this,true);

}

});

}

/* ================= SAISON FIN ================= */

let fin=document.getElementById("saison_fin");

if(fin){

fin.addEventListener("keyup",function(){

let val=parseInt(this.value);

if(isNaN(val)||val<1||val>12){

msg("msg_saison_fin","❌ mois 1 à 12","red");
bordure(this,false);

}else{

msg("msg_saison_fin","✔ valide","green");
bordure(this,true);

}

});

}

</script>

</body>
</html>

