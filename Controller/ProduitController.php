<?php
require_once __DIR__ . "/../Model/Produits.php";

class ProduitController {

    private $model;

    public function __construct() {
        $this->model = new Produits();
    }

    // ================= LIST =================
    public function list() {
        return $this->model->getAllProduits();
    }

    // ================= DELETE =================
    public function delete() {
        if (isset($_GET['code'])) {

            $code = $_GET['code'];

            $this->model->deleteProduit($code);

            header("Location: ../View/Admin_Produits.php");
            exit;
        }
    }

    // ================= ADD =================
    public function add($data, $file) {
        $data['categorie'] = $data['CodeC'];

        $data['Image'] = "";

        if (!empty($file['image']['name'])) {

            $imageName = time() . "_" . basename($file['image']['name']);
            $tmpName = $file['image']['tmp_name'];

            $uploadDir = __DIR__ . "/../View/Images/";

            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }

            move_uploaded_file($tmpName, $uploadDir . $imageName);

            $data['Image'] = "View/Images/" . $imageName;
        }

        $this->model->addProduit($data);
    }

    // ================= UPDATE =================
    public function update($data, $file) {
         $data['categorie'] = $data['CodeC'];

        // IMPORTANT: récupérer un seul produit
        $existing = $this->model->getProduitByCode($data['code']);
        $existing = $existing[0] ?? null;

        if (!$existing) {
            return;
        }

        if (!empty($file['image']['name'])) {

            $imageName = time() . "_" . basename($file['image']['name']);
            $tmpName = $file['image']['tmp_name'];

            $uploadDir = __DIR__ . "/../View/Images/";

            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }

            move_uploaded_file($tmpName, $uploadDir . $imageName);

            $data['Image'] = "View/Images/" . $imageName;

        } else {
            $data['Image'] = $existing['Image'];
        }

        $this->model->updateProduit($data);
    }

    // ================= SEARCH =================
    public function search($code) {
        return $this->model->getProduitByCode($code);
    }
}


// ================= ROUTING =================
$controller = new ProduitController();

// POST (ADD / UPDATE)
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    if ($_POST['action'] === 'add') {
        $controller->add($_POST, $_FILES);
    }

    if ($_POST['action'] === 'update') {
        $controller->update($_POST, $_FILES);
    }

    header("Location: ../View/Admin_Produits.php");
    exit;
}

// DELETE
if (isset($_GET['action']) && $_GET['action'] === 'delete') {
    $controller->delete();
}

// SEARCH + LIST
if (isset($_GET['action']) && $_GET['action'] === 'search') {

    $code = $_GET['code'] ?? '';
    $produits = !empty($code)
        ? $controller->search($code)
        : $controller->list();

} else {
    $produits = $controller->list();
}

/*👉 TOUJOURS charger la view ici
include "../View/Admin_Produits.php";
exit;*/

?>