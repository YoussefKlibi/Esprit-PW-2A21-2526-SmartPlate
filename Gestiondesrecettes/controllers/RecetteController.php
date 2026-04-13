<?php

require_once "../config.php";
require_once "../Model/Recette.php";

$recette = new Recette($pdo);

/* AJOUTER RECETTE */

if(isset($_POST['ajouter'])){

$image = $_FILES['image']['name'];
$tmp = $_FILES['image']['tmp_name'];

move_uploaded_file($tmp,"../images/".$image);

$recette->ajouter(
$_POST['nom'],
$_POST['description'],
$_POST['ingredients'],
$_POST['calories'],
$_POST['temps'],
$_POST['categorie'],
$image
);

header("Location: Views/backoffice/backoffice.php");

}


/* SUPPRIMER RECETTE */

if(isset($_GET['supprimer'])){

$id = $_GET['supprimer'];

$recette->supprimer($id);

header("Location: Views/backoffice/backoffice.php");

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

header("Location: ../View/backoffice/backoffice.php");

}

?>