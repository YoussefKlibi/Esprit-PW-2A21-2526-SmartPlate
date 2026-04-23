<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../Model/Objectif_Class.php';

class ObjectifController {
    
    // Fonction d'ajout (que tu as déjà)
  public function add() {
        if (isset($_POST['type']) && isset($_POST['poids_cible'])) {
            $id_user = isset($_POST['id_utilisateur']) ? $_POST['id_utilisateur'] : 1;
            
            // 🔴 CAPTURE DES NOUVELLES DONNÉES DU FORMULAIRE
            $is_notif = isset($_POST['is_notif_enabled']) ? 1 : 0;
            $heure = !empty($_POST['heure_notification']) ? $_POST['heure_notification'] : '08:00:00';

            // 🔴 AJOUT DANS LE CONSTRUCTEUR
            $obj = new Objectif(
                $_POST['type'],
                $_POST['poids_cible'],
                $_POST['Date_Debut'],
                $_POST['Date_Fin'],
                "en_cours", // Statut par défaut
                $id_user,
                $is_notif,
                $heure
            );
            
            $obj->ajouter();
            
            if (isset($_SERVER['HTTP_REFERER'])) {
                header('Location: ' . $_SERVER['HTTP_REFERER']);
            } else {
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

            // 🔴 CAPTURE DES DONNÉES LORS DE LA MODIFICATION
        $is_notif = isset($_POST['is_notif_enabled']) ? 1 : 0;
        $heure = !empty($_POST['heure_notification']) ? $_POST['heure_notification'] : '08:00:00';
            
            $obj = new Objectif(
                $_POST['type'],
                $_POST['poids_cible'],
                $_POST['Date_Debut'],
                $_POST['Date_Fin'],
                $_POST['statut'],
                $id_user,
                $is_notif,
                $heure
            );
            
            
            $obj->modifier($id);
            header('Location: ' . $_SERVER['HTTP_REFERER']);
            exit();
        }
    }
    // NOUVELLE FONCTION : Recherche dynamique
    public function search() {
        if (isset($_GET['term'])) {
            $term = $_GET['term'];
            $resultats = Objectif::rechercheDynamique($term);
            
            // On indique qu'on renvoie du JSON et on encode le résultat
            header('Content-Type: application/json');
            echo json_encode($resultats);
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
    // Si l'action est "search"
    elseif ($_GET['action'] == 'search') {
        $controller->search();
    }
}
?>