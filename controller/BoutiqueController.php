<?php
require_once "../Model/GeocodingService.php";
require_once __DIR__ . "/../Model/boutiques.php";


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

        /*if (empty($data['CodeB']) || !preg_match('/^[A-Za-z0-9_-]{2,20}$/', $data['CodeB'])) {
            $errors[] = "Code boutique invalide.";
        }*/

        if (empty($data['NomB']) || strlen($data['NomB']) < 2) {
            $errors[] = "Nom invalide.";
        }

        if (!filter_var($data['EmailB'], FILTER_VALIDATE_EMAIL)) {
            $errors[] = "Email invalide.";
        }

        if (!preg_match('/^[0-9]{8,15}$/', $data['TelB'])) {
            $errors[] = "Téléphone invalide.";
        }

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
            header("Location: ../View/Admin_Produits.php?error=boutique");
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

    // si API échoue
    $data['latitude'] = $coords['lat'] ?? null;
    $data['longitude'] = $coords['lon'] ?? null;

    
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

    header("Location: ../View/Admin_Produits.php");
    exit;
}

// 🔹 GET
$action = $_GET['action'] ?? '';

switch ($action) {

    case 'delete':
        if (!empty($_GET['codeb'])) {
            $controller->delete($_GET['codeb']);
        }
        header("Location: ../View/Admin_Produits.php");
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
            include "../View/Admin_Produits.php";
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

        include "../View/Front_boutiques.php";
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
        include "../View/Admin_Produits.php";
        exit;
}
