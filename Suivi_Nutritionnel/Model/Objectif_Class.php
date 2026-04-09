<?php
class Objectif {
    private ?int $id_objectif = null;
    private ?string $type_objectif = null;
    private ?float $poids_cible = null;
    private ?string $date_debut = null;
    private ?string $date_fin = null;
    private ?string $statut = null;

    // Constructeur
    public function __construct($type, $poids, $debut, $fin, $statut) {
        $this->type_objectif = $type;
        $this->poids_cible = $poids;
        $this->date_debut = $debut;
        $this->date_fin = $fin;
        $this->statut = $statut;
    }

    // --- METHODE : AJOUTER (Create) ---
    public function ajouter() {
        $sql = "INSERT INTO objectif (type_objectif, poids_cible, date_debut, date_fin, statut) 
                VALUES (:type, :poids, :debut, :fin, :statut)";
        $db = Config::getConnexion();
        try {
            $query = $db->prepare($sql);
            $query->execute([
                'type' => $this->type_objectif,
                'poids' => $this->poids_cible,
                'debut' => $this->date_debut,
                'fin' => $this->date_fin,
                'statut' => $this->statut
            ]);
        } catch (Exception $e) {
            echo 'Erreur: ' . $e->getMessage();
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
}
?>