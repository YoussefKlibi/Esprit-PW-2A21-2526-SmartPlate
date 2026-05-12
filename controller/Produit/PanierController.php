<?php
require_once __DIR__ . "/../../Models/Produit/Panier.php";
require_once __DIR__ . "/../../controller/User/UserController.php";

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

class PanierController {
    private $model;

    public function __construct() {
        $this->model = new Panier();
    }

    private function getUserId() {
        if (!empty($_SESSION['user_id'])) {
            return $_SESSION['user_id'];
        }
        
        if (!empty($_SESSION['user_email'])) {
            $userC = new UserController();
            $user = $userC->getUserByEmail($_SESSION['user_email']);
            if ($user) {
                $_SESSION['user_id'] = $user['id'];
                return $user['id'];
            }
        }
        return null;
    }

    public function add($code_produit, $quantite = 1) {
        $id_user = $this->getUserId();
        if (!$id_user) {
            return ['status' => 'error', 'message' => 'Veuillez vous connecter pour ajouter au panier'];
        }

        $id_panier = $this->model->getOrCreateActivePanier($id_user);
        $result = $this->model->addProduit($id_panier, $code_produit, $quantite);

        if ($result) {
            return ['status' => 'success', 'message' => 'Produit ajouté au panier'];
        } else {
            return ['status' => 'error', 'message' => 'Erreur lors de l\'ajout'];
        }
    }

    public function remove($code_produit) {
        $id_user = $this->getUserId();
        if (!$id_user) return ['status' => 'error', 'message' => 'Non connecté'];

        $id_panier = $this->model->getOrCreateActivePanier($id_user);
        $result = $this->model->removeProduit($id_panier, $code_produit);

        if ($result) {
            return ['status' => 'success', 'message' => 'Produit supprimé'];
        } else {
            return ['status' => 'error', 'message' => 'Erreur lors de la suppression'];
        }
    }

    public function update($code_produit, $quantite) {
        $id_user = $this->getUserId();
        if (!$id_user) return ['status' => 'error', 'message' => 'Non connecté'];

        $id_panier = $this->model->getOrCreateActivePanier($id_user);
        $result = $this->model->updateQuantite($id_panier, $code_produit, $quantite);

        if ($result) {
            return ['status' => 'success', 'message' => 'Quantité mise à jour'];
        } else {
            return ['status' => 'error', 'message' => 'Erreur lors de la mise à jour'];
        }
    }

    public function list() {
        $id_user = $this->getUserId();
        if (!$id_user) return [];

        $id_panier = $this->model->getOrCreateActivePanier($id_user);
        return $this->model->getPanierItems($id_panier);
    }
}

// ROUTING
$controller = new PanierController();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $code_produit = $_POST['code_produit'] ?? null;
    $quantite = $_POST['quantite'] ?? 1;

    $response = ['status' => 'error', 'message' => 'Action invalide'];

    if ($action === 'add' && $code_produit) {
        $response = $controller->add($code_produit, $quantite);
    } elseif ($action === 'update' && $code_produit) {
        $response = $controller->update($code_produit, $quantite);
    } elseif ($action === 'remove' && $code_produit) {
        $response = $controller->remove($code_produit);
    }

    header('Content-Type: application/json');
    echo json_encode($response);
    exit;
}

if (isset($_GET['action']) && $_GET['action'] === 'list') {
    header('Content-Type: application/json');
    echo json_encode($controller->list());
    exit;
}
?>
