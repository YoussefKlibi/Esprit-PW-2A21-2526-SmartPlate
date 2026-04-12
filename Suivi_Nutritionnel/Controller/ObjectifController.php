<?php
include_once 'C:\Users\MSI\Desktop\SmartPlate\Suivi_Nutritionnel\config.php';
include_once 'C:\Users\MSI\Desktop\SmartPlate\Suivi_Nutritionnel\Model\Objectif_Class.php';

class ObjectifController {
    
    // Fonction d'ajout (que tu as déjà)
   public function add() {
    if (isset($_POST['type']) && isset($_POST['poids_cible'])) {
        
        // On récupère l'ID utilisateur s'il existe (Admin), sinon on met 1 (Utilisateur actuel)
        $id_user = isset($_POST['id_utilisateur']) ? $_POST['id_utilisateur'] : 1;

        $obj = new Objectif(
            $_POST['type'],
            $_POST['poids_cible'],
            $_POST['Date_Debut'],
            $_POST['Date_Fin'],
            $statut = "en_cours",
            $id_user
        );
        
        $obj->ajouter();
        
        // --- LA CORRECTION EST ICI ---
        // Au lieu de mettre un chemin fixe, on renvoie vers la page d'origine
        if (isset($_SERVER['HTTP_REFERER'])) {
            header('Location: ' . $_SERVER['HTTP_REFERER']);
        } else {
            // Sécurité au cas où le referer est vide
            header('Location: ../View/FrontOffice/Objectif.php');
        }
        exit(); 
    }
}

    // NOUVELLE FONCTION : Suppression
    public function delete($id) {
        Objectif::supprimer($id);
         if (isset($_SERVER['HTTP_REFERER'])) {
            header('Location: ' . $_SERVER['HTTP_REFERER']);
        } else {
            // Sécurité au cas où le referer est vide
            header('Location: ../View/FrontOffice/Objectif.php');
        }
        exit();
    }
    // Dans la classe ObjectifController
    public function update($id) {
        if (isset($_POST['type']) && isset($_POST['poids_cible'])) {
            $id_user = isset($_POST['id_utilisateur']) ? $_POST['id_utilisateur'] : 1;
            
            $obj = new Objectif(
                $_POST['type'],
                $_POST['poids_cible'],
                $_POST['Date_Debut'],
                $_POST['Date_Fin'],
                $_POST['statut'],
                $id_user
            );
            
            $obj->modifier($id);
            header('Location: ' . $_SERVER['HTTP_REFERER']);
            exit();
        }
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
     elseif ($_GET['action'] == 'update' && isset($_GET['id'])) {
        $controller->update($_GET['id']);
    }
}
?>