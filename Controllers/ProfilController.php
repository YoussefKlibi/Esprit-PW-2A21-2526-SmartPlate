<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/../Models/Profil.php';

class ProfilController {
    
    public function addProfil($profil) {
        $db = Config::getConnexion();
        try {
            $req = $db->prepare('INSERT INTO profils (titre, description, id_utilisateur) VALUES(:titre, :description, :id_utilisateur)');
            $req->execute([
                'titre' => $profil->getTitre(),
                'description' => $profil->getDescription(),
                'id_utilisateur' => $profil->getIdUtilisateur()
            ]);
            return true;
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }

    public function getProfilById($id) {
        $db = Config::getConnexion();
        try {
            $req = $db->prepare('SELECT * FROM profils WHERE id = :id LIMIT 1');
            $req->execute(['id' => $id]);
            return $req->fetch(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            die('Error: ' . $e->getMessage());
        }
    }

    public function listeProfils() {
        $db = Config::getConnexion();
        try {
            $liste = $db->query('SELECT p.*, u.nom, u.prenom FROM profils p INNER JOIN users u ON p.id_utilisateur = u.id'); 
            return $liste->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            die('Error: ' . $e->getMessage());
        }
    }

    public function updateProfil($id, $titre, $description, $id_utilisateur) {
        $db = Config::getConnexion();
        try {
            $req = $db->prepare('UPDATE profils SET titre=:titre, description=:description, id_utilisateur=:id_utilisateur WHERE id=:id');
            $req->execute([
                'id' => $id,
                'titre' => $titre,
                'description' => $description,
                'id_utilisateur' => $id_utilisateur
            ]);
        } catch (Exception $e) {
            die('Error: ' . $e->getMessage());
        }
    }

    public function deleteProfil($id) {
        $db = Config::getConnexion();
        try {
            $req = $db->prepare("DELETE FROM profils WHERE id=:id");
            $req->execute(['id' => $id]);
        } catch (Exception $e) {
            die('Error: ' . $e->getMessage());
        }
    }

    // WORKSHOP : Jointure : Liste des profils par rapport à un d'utilisateur donné
    public function afficherProfilsParUtilisateur($idUtilisateur) {
        $db = Config::getConnexion();
        try {
            $req = $db->prepare('SELECT p.*, u.nom, u.prenom 
                                 FROM profils p 
                                 INNER JOIN users u ON p.id_utilisateur = u.id 
                                 WHERE p.id_utilisateur = :id');
            $req->execute(['id' => $idUtilisateur]);
            return $req->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            die('Error: ' . $e->getMessage());
        }
    }

    // BONUS : Recherche au clavier via LIKE
    public function rechercherProfilsParNomUtilisateur($recherche) {
        $db = Config::getConnexion();
        try {
            $req = $db->prepare('SELECT p.*, u.nom, u.prenom 
                                 FROM profils p 
                                 INNER JOIN users u ON p.id_utilisateur = u.id 
                                 WHERE u.nom LIKE :recherche OR u.prenom LIKE :recherche');
            $req->execute(['recherche' => '%' . $recherche . '%']);
            return $req->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            die('Error: ' . $e->getMessage());
        }
    }
}
?>
