<?php
// On inclut la configuration et le modèle (vérifie bien tes chemins selon ton dossier)
include_once 'C:\Users\MSI\Desktop\SmartPlate\Suivi_Nutritionnel\config.php';
include_once 'C:\Users\MSI\Desktop\SmartPlate\Suivi_Nutritionnel\Model\Journal_Class.php';

class JournalController {
    
    // 1. Ajouter un nouveau journal
    public function add() {
        if (isset($_POST['date_journal']) && isset($_POST['poids_actuel'])) {
            // Récupérer les champs depuis le formulaire (s'assurer qu'ils existent)
            $date = $_POST['date_journal'];
            $poids = $_POST['poids_actuel'];
            $humeur = isset($_POST['humeur']) ? $_POST['humeur'] : null;
            $sommeil = isset($_POST['heures_sommeil']) ? $_POST['heures_sommeil'] : null;
            $id_user = isset($_POST['id_utilisateur']) ? $_POST['id_utilisateur'] : 1;

            $journal = new Journal(
                $date,
                $poids,
                $humeur,
                $sommeil,
                $id_user
            );
            $journal->ajouter();
            
            // Astuce : HTTP_REFERER permet de renvoyer l'utilisateur sur la page d'où il vient 
            // (que ce soit depuis le FrontOffice ou le BackOffice)
            header('Location: ' . $_SERVER['HTTP_REFERER']); 
            exit(); 
        }
    }

    // 2. Supprimer un journal
    public function delete($id) {
        Journal::supprimer($id);
        
        // On redirige vers la page précédente après la suppression
        header('Location: ' . $_SERVER['HTTP_REFERER']); 
        exit();
    }

    // 3. Éditer (rediriger vers la page contenant le journal pour l'afficher)
    public function edit($id) {
        // Redirige vers la page Journal en mode édition (pré-remplissage)
        header('Location: ../../View/FrontOffice/Journal.php?edit_id=' . urlencode($id));
        exit();
    }

    // 4. Mettre à jour un journal existant
    public function update($id) {
        if (isset($_POST['date_journal']) && isset($_POST['poids_actuel'])) {
            $date = $_POST['date_journal'];
            $poids = $_POST['poids_actuel'];
            $humeur = isset($_POST['humeur']) ? $_POST['humeur'] : null;
            $sommeil = isset($_POST['heures_sommeil']) ? $_POST['heures_sommeil'] : null;

            Journal::update($id, $date, $poids, $humeur, $sommeil);

            // Si réception invalide, retourne à la page précédente
        header('Location: ' . $_SERVER['HTTP_REFERER']);
        exit();
        }

        // Si réception invalide, retourne à la page précédente
        header('Location: ' . $_SERVER['HTTP_REFERER']);
        exit();
    }

    // 5. Renvoyer les repas d'un journal en JSON (utilisé par le BackOffice via AJAX)
    public function getRepas($id) {
        $db = Config::getConnexion();
        $sql = "SELECT * FROM repas WHERE id_journal = :id ORDER BY heure_repas ASC";
        try {
            $query = $db->prepare($sql);
            $query->execute(['id' => $id]);
            $repas = $query->fetchAll();
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode($repas);
            exit();
        } catch (Exception $e) {
            header('Content-Type: application/json; charset=utf-8', true, 500);
            echo json_encode(['error' => $e->getMessage()]);
            exit();
        }
    }
}

// =========================================================
// LE ROUTEUR (Intercepteur d'URL)
// =========================================================
if (isset($_GET['action'])) {
    $controller = new JournalController();
    
    if ($_GET['action'] == 'add') {
        $controller->add();
    } 
    elseif ($_GET['action'] == 'delete' && isset($_GET['id'])) {
        $controller->delete($_GET['id']);
    } 
    // 🔴 LA LIGNE QUI MANQUAIT POUR L'ÉDITION :
    elseif ($_GET['action'] == 'update' && isset($_GET['id'])) {
        $controller->update($_GET['id']);
    }
    // Endpoint AJAX pour récupérer les repas d'un journal
    elseif ($_GET['action'] == 'getRepas' && isset($_GET['id'])) {
        $controller->getRepas($_GET['id']);
    }
}
?>