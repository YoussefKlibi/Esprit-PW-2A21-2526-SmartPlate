<?php
if (!class_exists('Config')) {
    require_once __DIR__ . '/../config.php';
}

class Repas {
    private ?int $id_repas = null;
    private ?int $id_journal = null;
    private ?string $type_repas = null;
    private ?string $heure_repas = null;
    private ?string $nom = null;
    private $qte = null;
    private ?string $image = null;
    private $nbre_calories = null;
    private $proteine = null;
    private $glucide = null;
    private $lipide = null;

    public function __construct($id_journal, $type, $heure, $nom, $qte, $image = null, $calories = null, $proteine = null, $glucide = null, $lipide = null) {
        $this->id_journal = $id_journal;
        $this->type_repas = $type;
        $this->heure_repas = $heure;
        $this->nom = $nom;
        $this->qte = $qte;
        $this->image = $image;
        $this->nbre_calories = $calories;
        $this->proteine = $proteine;
        $this->glucide = $glucide;
        $this->lipide = $lipide;
    }

    public function ajouter() {
        $db = Config::getConnexion();
        $sql = "INSERT INTO repas (id_journal, type_repas, heure_repas, nom, quantite, image_repas, nbre_calories, proteine, glucide, lipide)
                VALUES (:id_journal, :type, :heure, :nom, :quantite, :image_repas, :calories, :proteine, :glucide, :lipide)";
        $query = $db->prepare($sql);
        $query->execute([
            'id_journal' => $this->id_journal,
            'type' => $this->type_repas,
            'heure' => $this->heure_repas,
            'nom' => $this->nom,
            'quantite' => $this->qte,
            'image_repas' => $this->image,
            'calories' => $this->nbre_calories,
            'proteine' => $this->proteine,
            'glucide' => $this->glucide,
            'lipide' => $this->lipide,
        ]);
    }

    public static function listeParJournal($id_journal) {
        $db = Config::getConnexion();
        $sql = "SELECT * FROM repas WHERE id_journal = :id_journal ORDER BY heure_repas ASC";
        $query = $db->prepare($sql);
        $query->execute(['id_journal' => $id_journal]);
        return $query->fetchAll();
    }

    public static function getById($id) {
        $db = Config::getConnexion();
        $sql = "SELECT * FROM repas WHERE id_repas = :id LIMIT 1";
        $query = $db->prepare($sql);
        $query->execute(['id' => $id]);
        return $query->fetch();
    }

    public static function supprimer($id) {
        $db = Config::getConnexion();
        $query = $db->prepare('DELETE FROM repas WHERE id_repas = :id');
        $query->execute(['id' => $id]);
    }

    public static function update($id, $data) {
        $db = Config::getConnexion();
        $sql = "UPDATE repas SET 
                id_journal = :id_journal,
                nom = :nom, 
                type_repas = :type_repas, 
                heure_repas = :heure_repas,
                quantite = :quantite,
                image_repas = :image_repas,
                nbre_calories = :nbre_calories, 
                proteine = :proteine, 
                glucide = :glucide, 
                lipide = :lipide 
                WHERE id_repas = :id";
        $query = $db->prepare($sql);
        $query->bindValue(':id', $id, PDO::PARAM_INT);
        $query->bindValue(':id_journal', $data['id_journal'], PDO::PARAM_INT);
        $query->bindValue(':nom', $data['nom']);
        $query->bindValue(':type_repas', $data['type_repas']);
        $query->bindValue(':heure_repas', $data['heure_repas']);
        $query->bindValue(':quantite', $data['quantite']);
        $query->bindValue(':image_repas', $data['image_repas']);
        $query->bindValue(':nbre_calories', $data['nbre_calories']);
        $query->bindValue(':proteine', !empty($data['proteine']) ? $data['proteine'] : 0);
        $query->bindValue(':glucide', !empty($data['glucide']) ? $data['glucide'] : 0);
        $query->bindValue(':lipide', !empty($data['lipide']) ? $data['lipide'] : 0);
        $query->execute();
        return true;
    }
}
