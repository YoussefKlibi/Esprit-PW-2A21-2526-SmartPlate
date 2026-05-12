<?php
require_once __DIR__ . "/../../Models/Produit/Produits.php";

class FrontController {

    public function index() {

        $model = new Produits();
        $produits = $model->getAllProduits() ?? [];

        // on charge la view avec les données
        require __DIR__ . "/../../view/Produit/Front_produits.php";
    }
}

$controller = new FrontController();
$controller->index();
