<?php
require_once __DIR__ . "/../Model/stock.php";

class StockController {

    private $model;

    public function __construct() {
        $this->model = new Stock();
    }

    public function getAllStocks() {
        return $this->model->getAll();
    }

    public function addStock($code, $codeB, $stock) {

        $existing = $this->model->find($code, $codeB);

        if ($existing) {
            return $this->model->updateStock($code, $codeB, $stock);
        } else {
            return $this->model->add($code, $codeB, $stock);
        }
    }

    // 🔹 ✨ NOUVEAU
    public function setStock($code, $codeB, $stock) {
        return $this->model->setStock($code, $codeB, $stock);
    }

    public function deleteStock($code, $codeB) {
        return $this->model->delete($code, $codeB);
    }
}

/* ===== TRAITEMENT ===== */

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $controller = new StockController();

    $code = $_POST['Code'] ?? null;
    $codeB = $_POST['CodeB'] ?? null;
    $stock = $_POST['Stock'] ?? 0;

    // 🔹 NOUVEAU : action add ou set
    $actionStock = $_POST['actionStock'] ?? 'add';

    if ($code && $codeB) {

        if ($stock < 0) $stock = 0;

        if ($actionStock === 'set') {
            $controller->setStock($code, $codeB, $stock);
        } else {
            $controller->addStock($code, $codeB, $stock);
        }
    }

    header("Location: ../View/Admin_Produits.php");
    exit;
}

/* DELETE */
if (isset($_GET['action']) && $_GET['action'] === 'delete') {

    $controller = new StockController();

    $code = $_GET['Code'];
    $codeB = $_GET['CodeB'];

    $controller->deleteStock($code, $codeB);

    header("Location: ../View/Admin_Produits.php");
    exit;
}
