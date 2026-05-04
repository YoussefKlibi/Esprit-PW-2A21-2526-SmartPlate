<?php
class Objectif {
    private ?int $id_objectif = null;
    private ?int $id_utilisateur = null;
    private ?string $type_objectif = null;
    private ?float $poids_cible = null;
    private ?string $date_debut = null;
    private ?string $date_fin = null;
    private ?string $statut = null;
    private ?int $is_notif_enabled = 0;
    private ?string $heure_notification = '08:00:00';

    // Constructeur
    public function __construct($type, $poids, $debut, $fin, $statut, $id_user = 1, $is_notif = 0, $heure = '08:00:00') {
        $this->id_utilisateur = $id_user;
        $this->type_objectif = $type;
        $this->poids_cible = $poids;
        $this->date_debut = $debut;
        $this->date_fin = $fin;
        $this->statut = $statut;
        $this->is_notif_enabled = $is_notif;
        $this->heure_notification = $heure;
    }

    // --- METHODE : AJOUTER (Create) ---
  public function ajouter() {
        $db = Config::getConnexion();
        // 🔴 AJOUT DES DEUX COLONNES DANS LA REQUÊTE
        $sql = "INSERT INTO objectif (type_objectif, poids_cible, date_debut, date_fin, statut, id_utilisateur, is_notif_enabled, heure_notification) 
                VALUES (:type, :poids, :debut, :fin, :statut, :id_user, :is_notif, :heure)";
        try {
            $query = $db->prepare($sql);
            $query->execute([
                'type' => $this->type_objectif,
                'poids' => $this->poids_cible,
                'debut' => $this->date_debut,
                'fin' => $this->date_fin,
                'statut' => $this->statut,
                'id_user' => $this->id_utilisateur,
                // 🔴 AJOUT DES VARIABLES
                'is_notif' => $this->is_notif_enabled,
                'heure' => $this->heure_notification
            ]);
        } catch (Exception $e) {
            die('Erreur: ' . $e->getMessage());
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
        // 🔴 AJOUT DES COLONNES DANS L'UPDATE
        $sql = "UPDATE objectif SET 
                type_objectif = :type, 
                poids_cible = :poids, 
                date_debut = :debut, 
                date_fin = :fin, 
                statut = :statut, 
                id_utilisateur = :id_user,
                is_notif_enabled = :is_notif,
                heure_notification = :heure
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
                // 🔴 AJOUT DES VARIABLES
                'is_notif' => $this->is_notif_enabled,
                'heure' => $this->heure_notification,
                'id' => $id
            ]);
        } catch (Exception $e) {
            die('Erreur: ' . $e->getMessage());
        }
    }

    // --- METHODE : RÉCUPÉRER L'OBJECTIF ACTIF ---
    public static function getActif($id_user) {
        $db = Config::getConnexion();
        // On cherche uniquement un objectif "en cours" pour cet utilisateur.
        // Normaliser les valeurs (ex: 'en_cours' ou 'En cours') en remplaçant '_' par ' ' et en passant en minuscule.
        $sql = "SELECT * FROM objectif WHERE id_utilisateur = :id_user AND REPLACE(LOWER(statut),'_',' ') = 'en cours' LIMIT 1";
        try {
            $query = $db->prepare($sql);
            $query->execute(['id_user' => $id_user]);
            return $query->fetch(); // Retourne les données de l'objectif, ou 'false' s'il n'y en a pas
        } catch (Exception $e) {
            die('Erreur : ' . $e->getMessage());
        }
    }
    // --- METHODE : RECHERCHE DYNAMIQUE ---
    public static function rechercheDynamique($term) {
        $db = Config::getConnexion();
        // On recherche par ID d'objectif, ID d'utilisateur ou Type d'objectif
        $sql = "SELECT * FROM objectif WHERE id_objectif LIKE :term OR id_utilisateur LIKE :term OR type_objectif LIKE :term";
        
        try {
            $query = $db->prepare($sql);
            // On ajoute les % pour chercher le terme n'importe où dans la chaîne
            $query->execute(['term' => '%' . $term . '%']);
            return $query->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            die('Erreur lors de la recherche: ' . $e->getMessage());
        }
    }

    // --- METHODE : STATISTIQUES POUR LE DASHBOARD ---
public static function getStatsParStatut() {
    $db = Config::getConnexion();
    // On récupère le compte groupé par statut
    $sql = "SELECT statut, COUNT(*) as nb FROM objectif GROUP BY statut";
    
    try {
        $query = $db->query($sql);
        $resultats = $query->fetchAll(PDO::FETCH_ASSOC);
        
        // On initialise un tableau avec des clés fixes pour faciliter l'usage dans la vue
        $stats = ['en_cours' => 0, 'atteint' => 0, 'abandonne' => 0];
        
        foreach ($resultats as $row) {
            $s = strtolower(trim($row['statut']));
            if (strpos($s, 'cours') !== false) $stats['en_cours'] = (int)$row['nb'];
            elseif (strpos($s, 'atteint') !== false) $stats['atteint'] = (int)$row['nb'];
            elseif (strpos($s, 'abandonn') !== false) $stats['abandonne'] = (int)$row['nb'];
        }
        
        return $stats;
    } catch (Exception $e) {
        return ['en_cours' => 0, 'atteint' => 0, 'abandonne' => 0];
    }
}
// --- AJOUTER DANS Model/Objectif_Class.php ---

    public static function getUnrealisticAlerts() {
        $db = Config::getConnexion();
        $sql = "SELECT
                    'Objectif Irréaliste' AS type_alerte,
                    CONCAT('ID Objectif #', o.id_objectif, ': Poids cible de ', o.poids_cible, ' kg demandé.') AS description,
                    CONCAT(COALESCE(o.date_debut, CURDATE()), ' 00:00:00') AS dt,
                    o.id_objectif AS entity_id
                FROM objectif o
                WHERE (o.poids_cible IS NOT NULL AND (o.poids_cible < 35 OR o.poids_cible > 250))
                ORDER BY o.id_objectif DESC
                LIMIT 3";
        try {
            return $db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            return [];
        }
    }

    // --- METHODE : TRIER (Read / Sort) ---
    public static function trier($critere) {
        $db = Config::getConnexion();
        
        // Définition des tris
        // Pour 'en_cours', on utilise une astuce SQL (CASE) pour forcer ce statut en haut de liste
        $tris_autorises = [
            'en_cours' => "CASE WHEN statut = 'en_cours' THEN 1 ELSE 2 END, date_debut DESC",
            'date' => 'date_debut DESC',
            'statut' => 'statut ASC'
        ];
        
        $orderBy = isset($tris_autorises[$critere]) ? $tris_autorises[$critere] : $tris_autorises['en_cours'];
        
        $sql = "SELECT * FROM objectif ORDER BY " . $orderBy;
        
        try {
            return $db->query($sql)->fetchAll();
        } catch (Exception $e) {
            die('Erreur lors du tri: ' . $e->getMessage());
        }
    }

    public static function verifierEtEnvoyerRappels($email_test = "klibiyoussef2017@gmail.com") {
    $db = Config::getConnexion();
    
    // On cherche les objectifs qui doivent recevoir une notification pile à cette minute
    $sql = "SELECT * FROM objectif 
            WHERE is_notif_enabled = 1 
            AND statut = 'en_cours'
            AND DATE_FORMAT(heure_notification, '%H:%i') = DATE_FORMAT(NOW(), '%H:%i')";
            
    try {
        $query = $db->query($sql);
        $objectifs = $query->fetchAll(PDO::FETCH_ASSOC);

        if (count($objectifs) > 0) {
            $sujet = "SmartPlate - N'oublie pas ton journal alimentaire !";
            $headers = "From: noreply@smartplate.com\r\nContent-Type: text/html; charset=UTF-8\r\n";

            foreach ($objectifs as $obj) {
                $message = "<h3>Salut !</h3><p>Il est l'heure de remplir ton journal pour ton objectif de <strong>" . htmlspecialchars($obj['type_objectif']) . "</strong>.</p>";
                
                if(mail($email_test, $sujet, $message, $headers)) {
                    echo "[Succès] Mail envoyé pour l'objectif #" . $obj['id_objectif'] . "\n";
                }
            }
        } else {
            echo "Rien à envoyer à " . date('H:i') . "\n";
        }
    } catch (Exception $e) {
        echo 'Erreur : ' . $e->getMessage();
    }
}
}
?>