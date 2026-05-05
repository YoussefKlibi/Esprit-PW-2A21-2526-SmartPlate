<?php

require_once __DIR__ . "/../Model/Categorie.php";

$model = new Categories();


// ➕ AJOUTER
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $data = [
        'NomC' => trim($_POST['NomC'])
    ];

    $model->addCategorie($data);

    header("Location: ../View/Admin_Produits.php#categorie");
    exit;
}


// ❌ SUPPRIMER
if (isset($_GET['delete'])) {

    $code = $_GET['delete'];

    $model->deleteCategorie($code);

    header("Location: ../View/Admin_Produits.php#categorie");
    exit;
}


// ✏️ MODIFIER
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $data = [
        'NomC' => trim($_POST['NomC'])
    ];

    // UPDATE
    if (isset($_POST['action']) && $_POST['action'] === "update" && !empty($_POST['CodeC'])) {

        $data['CodeC'] = $_POST['CodeC'];
        $model->updateCategorie($data);

    } 
    // ADD
    else {
        $model->addCategorie($data);
    }

    header("Location: ../View/Admin_Produits.php#categorie");
    exit;
}

?>
