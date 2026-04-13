<?php

class Recette {

private $pdo;

function __construct($pdo){
$this->pdo = $pdo;
}

/* AJOUTER RECETTE */

function ajouter($nom,$description,$ingredients,$calories,$temps,$categorie,$image){

$sql="INSERT INTO recette(nom,description,ingredients,calories,temps_preparation,categorie,image)
VALUES(?,?,?,?,?,?,?)";

$this->pdo->prepare($sql)->execute([
$nom,
$description,
$ingredients,
$calories,
$temps,
$categorie,
$image
]);

}

/* AFFICHER RECETTES */

function afficher(){

$sql="SELECT * FROM recette";
return $this->pdo->query($sql);

}

/* SUPPRIMER RECETTE */

function supprimer($id){

$sql="DELETE FROM recette WHERE id_recette=?";
$this->pdo->prepare($sql)->execute([$id]);

}

/* MODIFIER RECETTE */

function modifier($id,$nom,$description,$ingredients,$calories,$temps,$categorie){

$sql="UPDATE recette SET
nom=?,
description=?,
ingredients=?,
calories=?,
temps_preparation=?,
categorie=?
WHERE id_recette=?";

$this->pdo->prepare($sql)->execute([
$nom,
$description,
$ingredients,
$calories,
$temps,
$categorie,
$id
]);

}

}

?>