<?php
class Journal {
    private ?int $id_journal = null;
    private ?string $date_journal = null;
    private ?float $poids_actuel = null;
    private ?string $humeur = null;
    private ?int $heures_sommeil = null;
    private ?int $id_utilisateur = null;

    public function __construct($date, $poids, $humeur, $sommeil, $id_user = 1) {
        $this->date_journal = $date;
        $this->poids_actuel = $poids;
        $this->humeur = $humeur;
        $this->heures_sommeil = $sommeil;
        $this->id_utilisateur = $id_user;
    }

    // --- Create : Ajouter un journal ---
    public function ajouter() {
        $db = Config::getConnexion();
        $sql = "INSERT INTO journal_alimentaire 
                (date_journal, poids_actuel, humeur, heures_sommeil, id_utilisateur) 
                VALUES (:date_j, :poids, :humeur, :sommeil, :id_user)";
        try {
            $query = $db->prepare($sql);
            $query->execute([
                'date_j' => $this->date_journal,
                'poids' => $this->poids_actuel,
                'humeur' => $this->humeur,
                'sommeil' => $this->heures_sommeil,
                'id_user' => $this->id_utilisateur
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
}
?>