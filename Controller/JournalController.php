<?php
// On inclut la configuration et le modèle (vérifie bien tes chemins selon ton dossier)
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../Model/Journal_Class.php';
require_once __DIR__ . '/../Model/Objectif_Class.php';

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

            // Si l'admin force un id_objectif depuis le form, on le prend. Sinon côté Front, on tente
            // de récupérer l'objectif actif de l'utilisateur et d'attacher son id.
            $id_objectif = null;
            if (isset($_POST['id_objectif']) && $_POST['id_objectif'] !== '') {
                $id_objectif = $_POST['id_objectif'];
                // Vérifier que l'objectif existe réellement pour éviter les erreurs de FK
                try {
                    $db = Config::getConnexion();
                    $q = $db->prepare('SELECT id_objectif FROM objectif WHERE id_objectif = :id');
                    $q->execute(['id' => $id_objectif]);
                    $exists = $q->fetch();
                    if (!$exists) {
                        // objectif invalide fourni par le formulaire -> ignorer
                        $id_objectif = null;
                    }
                } catch (Exception $e) {
                    $id_objectif = null;
                }
            } else {
                // Chercher un objectif en cours pour cet utilisateur
                $actif = Objectif::getActif($id_user);
                if ($actif && isset($actif['id_objectif'])) {
                    $id_objectif = $actif['id_objectif'];
                }
            }

            $journal = new Journal(
                $date,
                $poids,
                $humeur,
                $sommeil,
                $id_user,
                $id_objectif
            );
            $journal->ajouter();

            // Après ajout, on redirige vers la page d'origine mais sans les paramètres
            // de requête (ex: ?date_recherche=...) afin d'afficher le journal "le plus
            // récent" et éviter d'être coincé sur une date précédente.
            $referer = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : null;
            if ($referer) {
                // Supprimer la partie query string si elle existe
                $base = strtok($referer, '?');
                header('Location: ' . $base);
            } else {
                header('Location: ../../View/FrontOffice/Journal.php');
            }
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