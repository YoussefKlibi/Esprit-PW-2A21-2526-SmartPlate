<?php
require_once __DIR__ . "/../../Models/Produit/GeocodingService.php";
require_once __DIR__ . "/../../Models/Produit/boutiques.php";


class BoutiqueController {

    private $model;

    public function __construct() {
        $this->model = new Boutiques();
    }


    public function searchByNameF($nom)
    {
        return $this->model->searchByNameF($nom);
    }


    public function list() {
        return $this->model->getAllBoutiques();
    }

    public function add($data) {

        // 🔒 VALIDATION PHP
        $errors = [];
        $fieldErrors = [];

        if (empty($data['NomB']) || strlen($data['NomB']) < 2) {
            $errors[] = "Nom invalide.";
        }

        if (!filter_var($data['EmailB'], FILTER_VALIDATE_EMAIL)) {
            $errors[] = "Email invalide.";
        }

        // ===== MODIF RECENTE: message precis sur le champ TelB =====
        if (!preg_match('/^[0-9]{8,15}$/', $data['TelB'])) {
            $errors[] = "Téléphone invalide.";
            $fieldErrors['TelB'] = "Le numéro doit contenir entre 8 et 15 chiffres.";
        } elseif (!empty($data['TelB']) && $this->model->phoneExists($data['TelB'])) {
            $errors[] = "Ce numéro de téléphone existe déjà.";
            $fieldErrors['TelB'] = "Ce numéro existe déjà, veuillez en saisir un autre.";
        }
        // ===== FIN MODIF RECENTE =====

        if (empty($data['AdresseB'])) {
            $errors[] = "Adresse obligatoire.";
        }

        if (empty($data['VilleB'])) {
            $errors[] = "Ville obligatoire.";
        }

        if (!preg_match('/^[0-9]{3,10}$/', $data['Code_postalB'])) {
            $errors[] = "Code postal invalide.";
        }

        if (!empty($errors)) {
            session_start();
            $_SESSION['errors_boutique'] = $errors;
            $_SESSION['field_errors_boutique'] = $fieldErrors;
            $_SESSION['old_boutique'] = $data;
            header("Location: ../../view/Produit/Admin_Produits.php?error=boutique");
            exit;
        }

            // 🌍 GÉOCODAGE AUTOMATIQUE
        $geo = new GeocodingService();

        $coords = $geo->getCoordinates(
            $data['AdresseB'],
            $data['VilleB'],
            $data['Code_postalB'],
            $data['PaysB']
        );

        if ($coords) {
            $data['latitude'] = $coords['lat'];
            $data['longitude'] = $coords['lon'];
        } else {
            $data['latitude'] = null;
            $data['longitude'] = null;
        }
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }
        unset($_SESSION['old_boutique'], $_SESSION['errors_boutique'], $_SESSION['field_errors_boutique']);
        return $this->model->addBoutique($data);
    }

    public function update($data) {
        if (empty($data['CodeB'])) {
            return false;
        }
        return $this->model->updateBoutique($data);
    }

    public function delete($codeb) {
        if (empty($codeb)) {
            return false;
        }
        return $this->model->deleteBoutique($codeb);
    }

    public function searchByCode($code)
    {
        return $this->model->searchByCode($code);
    }
}

//
// ================= ROUTING =================
//

$controller = new BoutiqueController();

// 🔹 POST
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $action = $_POST['action'] ?? '';

    switch ($action) {

        case 'add':
            $controller->add($_POST);
            break;

        case 'update':
            $controller->update($_POST);
            break;
    }

    header("Location: ../../view/Produit/Admin_Produits.php");
    exit;
}

// 🔹 GET
$action = $_GET['action'] ?? '';

switch ($action) {

    case 'delete':
        if (!empty($_GET['codeb'])) {
            $controller->delete($_GET['codeb']);
        }
        header("Location: ../../view/Produit/Admin_Produits.php");
        exit;

    case 'search':
        $nom = $_GET['NomB'] ?? '';
        $message = "";

        if (empty($nom)) {
            $message = "⚠️ Veuillez saisir le nom de la boutique";
            $boutiques = [];
        } else {
            $boutiques = $controller->searchByName($nom);

            if (empty($boutiques)) {
                $message = "❌ Aucune boutique trouvée";
            }
        }
            include "../../view/Produit/Admin_Produits.php";
            exit;

    case 'searchF':
        $nom = $_GET['NomB'] ?? '';
        $message = "";

        if (empty($nom)) {
            $message = "⚠️ Veuillez saisir le nom de la boutique";
            $boutiques = [];
        } else {
            $boutiques = $controller->searchByNameF($nom);

            if (empty($boutiques)) {
                $message = "❌ Aucune boutique trouvée";
            }
        }

        include "../../view/Produit/Front_boutiques.php";
        exit;

    case 'searchAjax':
        header('Content-Type: application/json');

        $nom = $_GET['NomB'] ?? '';

        if (empty($nom)) {
            echo json_encode($controller->list());
        } else {
            echo json_encode($controller->searchByNameF($nom));
        }
        exit;

    default:
        $boutiques = $controller->list();
        include "../../view/Produit/Admin_Produits.php";
        exit;
}
