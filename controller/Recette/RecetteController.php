<?php

require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../Models/Recette/Recette.php';

$pdo = Config::getConnexion();
$recette = new Recette($pdo);

/* AJOUTER RECETTE */

if(isset($_POST['ajouter'])){

$image = $_FILES['image']['name'];
$tmp = $_FILES['image']['tmp_name'];

@mkdir(__DIR__ . '/../../images', 0777, true);
move_uploaded_file($tmp, __DIR__ . "/../../images/" . $image);

$recette->ajouter(
$_POST['nom'],
$_POST['description'],
$_POST['ingredients'],
$_POST['calories'],
$_POST['temps'],
$_POST['categorie'],
$image
);

header("Location: ../../view/Recette/backoffice/recettes.php");
exit();

}


/* SUPPRIMER RECETTE */

if(isset($_GET['supprimer'])){

$id = $_GET['supprimer'];

$recette->supprimer($id);

header("Location: ../../view/Recette/backoffice/recettes.php");
exit();

}


/* MODIFIER RECETTE */

if(isset($_POST['modifier'])){

$recette->modifier(
$_POST['id'],
$_POST['nom'],
$_POST['description'],
$_POST['ingredients'],
$_POST['calories'],
$_POST['temps'],
$_POST['categorie']
);

header("Location: ../../view/Recette/backoffice/recettes.php");
exit();

}

?>