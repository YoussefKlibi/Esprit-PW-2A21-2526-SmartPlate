<?php
// On inclut la configuration et le modèle (vérifie bien tes chemins selon ton dossier)
include_once 'C:\Users\MSI\Desktop\SmartPlate\Suivi_Nutritionnel\config.php';
include_once 'C:\Users\MSI\Desktop\SmartPlate\Suivi_Nutritionnel\Model\Journal_Class.php';

class JournalController {
    
    // 1. Ajouter un nouveau journal
    public function add() {
        if (isset($_POST['date_journal']) && isset($_POST['poids_actuel'])) {
            $journal = new Journal(
                $_POST['date_journal'],
                $_POST['poids_actuel']
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
}
?>