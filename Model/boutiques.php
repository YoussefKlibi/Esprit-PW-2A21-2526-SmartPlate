<?php
require_once __DIR__ . "/../Config/config.php";

class Boutiques {

    private $pdo;

    public function __construct() {
        $this->pdo = Config::getConnexion();
    }

    // ➕ AJOUTER BOUTIQUE
    public function addBoutique($data) {
        $sql = "INSERT INTO boutiques
        (CodeB, NomB, EmailB, TelB, AdresseB, VilleB, Code_postalB, PaysB, latitude, longitude)
        VALUES (:codeb, :nom, :email, :tel, :adresse, :ville, :cp, :pays, :lat, :lng)";

        $stmt = $this->pdo->prepare($sql);

        return $stmt->execute([
            'codeb' => $data['CodeB'],
            'nom' => $data['NomB'],
            'email' => $data['EmailB'],
            'tel' => $data['TelB'],
            'adresse' => $data['AdresseB'],
            'ville' => $data['VilleB'],
            'cp' => $data['Code_postalB'],
            'pays' => $data['PaysB'],
            'lat' => $data['latitude'],
            'lng' => $data['longitude']
        ]);
    }

    // 📄 LISTE BOUTIQUES
    public function getAllBoutiques() {
        $sql = "SELECT * FROM boutiques";
        $stmt = $this->pdo->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // ✏️ UPDATE BOUTIQUE
    public function updateBoutique($data) {
        $sql = "UPDATE boutiques SET
            NomB = :nom,
            EmailB = :email,
            TelB = :tel,
            AdresseB = :adresse,
            VilleB = :ville,
            Code_postalB = :cp,
            PaysB = :pays,
            latitude = :lat,
            longitude = :lng
        WHERE CodeB = :codeb";

        $stmt = $this->pdo->prepare($sql);

        return $stmt->execute([
            'codeb' => $data['CodeB'],
            'nom' => $data['NomB'],
            'email' => $data['EmailB'],
            'tel' => $data['TelB'],
            'adresse' => $data['AdresseB'],
            'ville' => $data['VilleB'],
            'cp' => $data['Code_postalB'],
            'pays' => $data['PaysB'],
            'lat' => $data['latitude'],
            'lng' => $data['longitude']
        ]);
    }

    // ❌ DELETE
    public function deleteBoutique($codeb) {
        $sql = "DELETE FROM boutiques WHERE CodeB = :codeb";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute(['codeb' => $codeb]);
    }

    // 🔍 SEARCH
    public function searchByCode($code) {
        $sql = "SELECT * FROM boutiques WHERE CodeB = :code";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            'code' => $code
        ]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }


    // 🔒 VALIDATION BOUTIQUE (AJOUT UNIQUEMENT)
public function validateBoutique($data, &$errors = []) {

    if (empty($data['CodeB']) || !preg_match('/^[A-Za-z0-9_-]{2,20}$/', $data['CodeB'])) {
        $errors[] = "Code boutique invalide (2-20 caractères).";
    }

    if (empty($data['NomB']) || strlen($data['NomB']) < 2) {
        $errors[] = "Nom boutique invalide.";
    }

    if (!filter_var($data['EmailB'], FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Email invalide.";
    }

    if (!preg_match('/^[0-9]{8,15}$/', $data['TelB'])) {
        $errors[] = "Téléphone invalide.";
    }

    if (empty($data['AdresseB'])) {
        $errors[] = "Adresse obligatoire.";
    }

    if (empty($data['VilleB'])) {
        $errors[] = "Ville obligatoire.";
    }

    if (!preg_match('/^[0-9]{3,10}$/', $data['Code_postalB'])) {
        $errors[] = "Code postal invalide.";
    }

    if (empty($data['PaysB'])) {
        $errors[] = "Pays obligatoire.";
    }

    if ($data['latitude'] !== "" && !is_numeric($data['latitude'])) {
        $errors[] = "Latitude invalide.";
    }

    if ($data['longitude'] !== "" && !is_numeric($data['longitude'])) {
        $errors[] = "Longitude invalide.";
    }

    return empty($errors);
}

    
}
?>
