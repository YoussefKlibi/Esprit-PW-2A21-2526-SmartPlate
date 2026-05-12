<?php

require_once __DIR__ . "/../../Models/Produit/Categorie.php";

$model = new Categories();

// ❌ SUPPRIMER
if (isset($_GET['delete'])) {

    $code = $_GET['delete'];

    $model->deleteCategorie($code);

    header("Location: ../../view/Produit/Admin_Produits.php#categorie");
    exit;
}


// ➕ AJOUTER / ✏️ MODIFIER
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $nomCategorie = isset($_POST['NomC']) ? trim($_POST['NomC']) : '';
    if ($nomCategorie === '') {
        header("Location: ../../view/Produit/Admin_Produits.php#categorie");
        exit;
    }

    $data = ['NomC' => $nomCategorie];

    // UPDATE
    if (isset($_POST['action']) && $_POST['action'] === "update" && !empty($_POST['CodeC'])) {

        $data['CodeC'] = (int) $_POST['CodeC'];
        $model->updateCategorie($data);

    }
    // ADD
    else {
        $model->addCategorie($data);
    }

    header("Location: ../../view/Produit/Admin_Produits.php#categorie");
    exit;
}

?>
