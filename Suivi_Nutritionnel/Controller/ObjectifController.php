<?php
include_once 'C:\Users\MSI\Desktop\SmartPlate\Suivi_Nutritionnel\config.php';
include_once 'C:\Users\MSI\Desktop\SmartPlate\Suivi_Nutritionnel\Model\Objectif_Class.php';

class ObjectifController {
    
    // Fonction d'ajout (que tu as déjà)
    public function add() {
        if (isset($_POST['type']) && isset($_POST['poids_cible'])) {
            $obj = new Objectif(
                $_POST['type'],
                $_POST['poids_cible'],
                $_POST['Date_Debut'],
                $_POST['Date_Fin'],
                $_POST['statut']
            );
            $obj->ajouter();
            header('Location: ../View/FrontOffice/Objectif.php'); 
            exit(); 
        }
    }

    // NOUVELLE FONCTION : Suppression
    public function delete($id) {
        Objectif::supprimer($id);
        // On redirige vers la page après avoir supprimé
        header('Location: ../View/FrontOffice/Objectif.php'); 
        exit();
    }
}

// =========================================================
// LES DÉCLENCHEURS (Le Routeur)
// =========================================================
if (isset($_GET['action'])) {
    $controller = new ObjectifController();
    
    // Si l'action est "add"
    if ($_GET['action'] == 'add') {
        $controller->add();
    } 
    // Si l'action est "delete" ET qu'on a bien reçu un ID dans l'URL
    elseif ($_GET['action'] == 'delete' && isset($_GET['id'])) {
        $controller->delete($_GET['id']);
    }
}
?>