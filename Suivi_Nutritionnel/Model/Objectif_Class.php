<?php
class Objectif {
    private ?int $id_objectif = null;
    private ?string $type_objectif = null;
    private ?float $poids_cible = null;
    private ?string $date_debut = null;
    private ?string $date_fin = null;
    private ?string $statut = null;

    // Constructeur
    public function __construct($type, $poids, $debut, $fin, $statut, $id_user = 1) {
        $this->id_utilisateur = $id_user;
        $this->type_objectif = $type;
        $this->poids_cible = $poids;
        $this->date_debut = $debut;
        $this->date_fin = $fin;
        $this->statut = $statut;
    }

    // --- METHODE : AJOUTER (Create) ---
   public function ajouter() {
        $db = Config::getConnexion();
        // NOUVEAU : On ajoute id_utilisateur dans la requête
        $sql = "INSERT INTO objectif (type_objectif, poids_cible, date_debut, date_fin, statut, id_utilisateur) 
                VALUES (:type, :poids, :debut, :fin, :statut, :id_user)";
        try {
            $query = $db->prepare($sql);
            $query->execute([
                'type' => $this->type_objectif,
                'poids' => $this->poids_cible,
                'debut' => $this->date_debut,
                'fin' => $this->date_fin,
                'statut' => $this->statut,
                'id_user' => $this->id_utilisateur // <-- NOUVEAU : On l'envoie à MySQL
            ]);
        } catch (Exception $e) {
            die('Erreur d\'ajout : ' . $e->getMessage());
        }
    }

    // --- METHODE : AFFICHER (Read) ---
    public static function liste() {
        $sql = "SELECT * FROM objectif";
        $db = Config::getConnexion();
        try {
            return $db->query($sql)->fetchAll();
        } catch (Exception $e) {
            die('Erreur: ' . $e->getMessage());
        }
    }

    // --- METHODE : SUPPRIMER (Delete) ---
    public static function supprimer($id) {
        $db = Config::getConnexion();
        $sql = "DELETE FROM objectif WHERE id_objectif = :id";
        
        try {
            $query = $db->prepare($sql);
            $query->execute(['id' => $id]);
        } catch (Exception $e) {
            die('Erreur lors de la suppression: ' . $e->getMessage());
        }
    }

    // --- METHODE : MODIFIER (Update) ---
    public function modifier($id) {
        $db = Config::getConnexion();
        $sql = "UPDATE objectif SET 
                type_objectif = :type, 
                poids_cible = :poids, 
                date_debut = :debut, 
                date_fin = :fin, 
                statut = :statut, 
                id_utilisateur = :id_user 
                WHERE id_objectif = :id";
        try {
            $query = $db->prepare($sql);
            $query->execute([
                'type' => $this->type_objectif,
                'poids' => $this->poids_cible,
                'debut' => $this->date_debut,
                'fin' => $this->date_fin,
                'statut' => $this->statut,
                'id_user' => $this->id_utilisateur,
                'id' => $id
            ]);
        } catch (Exception $e) {
            die('Erreur de modification : ' . $e->getMessage());
        }
    }

    // --- METHODE : RÉCUPÉRER L'OBJECTIF ACTIF ---
    public static function getActif($id_user) {
        $db = Config::getConnexion();
        // On cherche uniquement un objectif "En cours" pour cet utilisateur
        $sql = "SELECT * FROM objectif WHERE id_utilisateur = :id_user AND statut = 'En cours' LIMIT 1";
        try {
            $query = $db->prepare($sql);
            $query->execute(['id_user' => $id_user]);
            return $query->fetch(); // Retourne les données de l'objectif, ou 'false' s'il n'y en a pas
        } catch (Exception $e) {
            die('Erreur : ' . $e->getMessage());
        }
    }
}
?>