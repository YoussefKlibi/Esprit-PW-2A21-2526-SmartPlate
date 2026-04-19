<?php
// Assurer que la configuration (classe Config) est disponible
if (!class_exists('Config')) {
    require_once __DIR__ . '/../config.php';
}
class Journal {
    private ?int $id_journal = null;
    private ?string $date_journal = null;
    private ?float $poids_actuel = null;
    private ?string $humeur = null;
    private ?int $heures_sommeil = null;
    private ?int $id_utilisateur = null;
    private ?int $id_objectif = null;

    public function __construct($date, $poids, $humeur, $sommeil, $id_user = 1, $id_objectif = null) {
        $this->date_journal = $date;
        $this->poids_actuel = $poids;
        $this->humeur = $humeur;
        $this->heures_sommeil = $sommeil;
        $this->id_utilisateur = $id_user;
        $this->id_objectif = $id_objectif;
    }

    // --- Delete : supprimer un journal et ses repas associés ---
    public static function supprimer($id) {
        $db = Config::getConnexion();
        try {
            $db->beginTransaction();
            // Supprimer les repas liés (si la table existe)
            $query = $db->prepare('DELETE FROM repas WHERE id_journal = :id');
            $query->execute(['id' => $id]);

            // Supprimer le journal
            $query = $db->prepare('DELETE FROM journal_alimentaire WHERE id_journal = :id');
            $query->execute(['id' => $id]);

            $db->commit();
        } catch (Exception $e) {
            $db->rollBack();
            die('Erreur lors de la suppression du journal : ' . $e->getMessage());
        }
    }

    // --- Create : Ajouter un journal ---
    public function ajouter() {
        $db = Config::getConnexion();
        // Si la colonne id_objectif existe et que nous avons une valeur, l'insérer, sinon laisser NULL
        $sql = "INSERT INTO journal_alimentaire 
                (date_journal, poids_actuel, humeur, heures_sommeil, id_utilisateur, id_objectif) 
                VALUES (:date_j, :poids, :humeur, :sommeil, :id_user, :id_objectif)";
        try {
            $query = $db->prepare($sql);
            $query->execute([
                'date_j' => $this->date_journal,
                'poids' => $this->poids_actuel,
                'humeur' => $this->humeur,
                'sommeil' => $this->heures_sommeil,
                'id_user' => $this->id_utilisateur,
                'id_objectif' => $this->id_objectif
            ]);
        } catch (Exception $e) {
            die('Erreur : ' . $e->getMessage());
        }
    }

    // --- Read : Liste pour l'Admin (avec calcul des totaux à la volée) ---
    public static function liste() {
        $db = Config::getConnexion();
        $sql = "SELECT j.*, 
                COALESCE(SUM(r.nbre_calories), 0) AS total_calories
                FROM journal_alimentaire j
                LEFT JOIN repas r ON j.id_journal = r.id_journal
                GROUP BY j.id_journal
                ORDER BY j.date_journal DESC";
        return $db->query($sql)->fetchAll();
    }

    // --- Read : récupérer le dernier journal d'un utilisateur ---
    public static function getLatest($id_user = 1) {
        $db = Config::getConnexion();
        $sql = "SELECT j.*, COALESCE(SUM(r.nbre_calories), 0) AS total_calories
                FROM journal_alimentaire j
                LEFT JOIN repas r ON j.id_journal = r.id_journal
                WHERE j.id_utilisateur = :id_user
                GROUP BY j.id_journal
                ORDER BY j.date_journal DESC
                LIMIT 1";
        try {
            $query = $db->prepare($sql);
            $query->execute(['id_user' => $id_user]);
            return $query->fetch();
        } catch (Exception $e) {
            die('Erreur: ' . $e->getMessage());
        }
    }

    // --- Read : récupérer un journal d'un utilisateur pour une date donnée ---
    public static function getByDate($id_user = 1, $date) {
        $db = Config::getConnexion();
        $sql = "SELECT j.*, COALESCE(SUM(r.nbre_calories), 0) AS total_calories
                FROM journal_alimentaire j
                LEFT JOIN repas r ON j.id_journal = r.id_journal
                WHERE j.id_utilisateur = :id_user AND j.date_journal = :date_journal
                GROUP BY j.id_journal
                LIMIT 1";
        try {
            $query = $db->prepare($sql);
            $query->execute(['id_user' => $id_user, 'date_journal' => $date]);
            return $query->fetch();
        } catch (Exception $e) {
            die('Erreur: ' . $e->getMessage());
        }
    }

    // --- Read : récupérer un journal par son id ---
    public static function getById($id) {
        $db = Config::getConnexion();
        $sql = "SELECT j.*, COALESCE(SUM(r.nbre_calories), 0) AS total_calories
                FROM journal_alimentaire j
                LEFT JOIN repas r ON j.id_journal = r.id_journal
                WHERE j.id_journal = :id
                GROUP BY j.id_journal
                LIMIT 1";
        try {
            $query = $db->prepare($sql);
            $query->execute(['id' => $id]);
            return $query->fetch();
        } catch (Exception $e) {
            die('Erreur: ' . $e->getMessage());
        }
    }

    // --- Update : modifier un journal existant ---
    public static function update($id, $date, $poids, $humeur, $sommeil) {
        $db = Config::getConnexion();
        $sql = "UPDATE journal_alimentaire SET date_journal = :date_j, poids_actuel = :poids, humeur = :humeur, heures_sommeil = :sommeil WHERE id_journal = :id";
        try {
            $query = $db->prepare($sql);
            $query->execute([
                'date_j' => $date,
                'poids' => $poids,
                'humeur' => $humeur,
                'sommeil' => $sommeil,
                'id' => $id
            ]);
            return true;
        } catch (Exception $e) {
            die('Erreur lors de la mise à jour : ' . $e->getMessage());
        }
    }
}
?>