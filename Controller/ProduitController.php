<?php
require_once __DIR__ . "/../Model/Produits.php";

class ProduitController {

    private $model;

    public function __construct() {
        $this->model = new Produits();
    }



    // 📄 LIST
    public function list() {
        return $this->model->getAllProduits();
    }

    // ❌ DELETE
    public function delete() {
        if (isset($_GET['code'])) {
            $code = $_GET['code'];
            $this->model->deleteProduit($code);

            header("Location: ../View/Admin_Produits.php");
            exit;
        }
    }

    // ✏️ UPDATE
    public function update($data) {
        $this->model->updateProduit($data);
    }

            // ➕ ADD
    public function add($data) {
        $this->model->addProduit($data);
    }

    // SEARCH
    public function search($code) {
    return $this->model->getProduitByCode($code);
}

}

// 🔥 ROUTING SIMPLE
$controller = new ProduitController();

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    if ($_POST['action'] === 'add') {
        $controller->add($_POST);
    }

    if ($_POST['action'] === 'update') {
        $controller->update($_POST);
    }

    header("Location: ../View/Admin_Produits.php");
    exit;
}

// DELETE ROUTE
if (isset($_GET['action']) && $_GET['action'] === 'delete') {
    $controller->delete();
}


// 🔍 SEARCH ROUTE
if (isset($_GET['action']) && $_GET['action'] === 'search') {

    $code = $_GET['code'] ?? '';

    if (!empty($code)) {
        $produits = $controller->search($code); // ✅ directement tableau
    } else {
        $produits = $controller->list();
    }

    include "../View/Admin_Produits.php";
    exit;
}

?>