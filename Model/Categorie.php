<?php
require_once __DIR__ . "/../Config/config.php";

class Categories {

    private $pdo;

    public function __construct() {
        $this->pdo = Config::getConnexion();
    }

    // ➕ AJOUTER CATEGORIE
    public function addCategorie($data) {

        $sql = "INSERT INTO categories (NomC)
                VALUES (:nom)";

        $stmt = $this->pdo->prepare($sql);

        return $stmt->execute([
            'nom' => $data['NomC']
        ]);
    }

    // 📄 AFFICHER TOUTES LES CATEGORIES
    public function getAllCategories() {

        $sql = "SELECT * FROM categories ORDER BY CodeC DESC";

        $stmt = $this->pdo->query($sql);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // ✏️ MODIFIER CATEGORIE
    public function updateCategorie($data) {

        $sql = "UPDATE categories
                SET NomC = :nom
                WHERE CodeC = :code";

        $stmt = $this->pdo->prepare($sql);

        return $stmt->execute([
            'code' => $data['CodeC'],
            'nom' => $data['NomC']
        ]);
    }

    // ❌ SUPPRIMER CATEGORIE
    public function deleteCategorie($code) {

        $sql = "DELETE FROM categories WHERE CodeC = :code";

        $stmt = $this->pdo->prepare($sql);

        return $stmt->execute([
            'code' => $code
        ]);
    }

}
?>
