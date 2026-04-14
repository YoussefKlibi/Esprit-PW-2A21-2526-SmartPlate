<?php
require_once __DIR__ . "/../Config/config.php";

class Produits {

    private $pdo;

    public function __construct() {
        $this->pdo = Config::getConnexion();
    }

    // ➕ AJOUTER PRODUIT
    public function addProduit($data) {
        $sql = "INSERT INTO Produits (Code, Nom, Categorie, NbStock, Prix, description)
                VALUES (:code, :nom, :categorie, :stock, :prix, :description)";

        $stmt = $this->pdo->prepare($sql);

        return $stmt->execute([
            'code' => $data['code'],
            'nom' => $data['nom'],
            'categorie' => $data['categorie'],
            'stock' => $data['stock'],
            'prix' => $data['prix'],
            'description' => $data['description']
        ]);
    }

    // 📄 AFFICHER TOUS LES PRODUITS
    public function getAllProduits() {
        $sql = "SELECT * FROM Produits";
        $stmt = $this->pdo->query($sql);
        return $stmt->fetchAll();
    }

    // ✏️ MODIFIER PRODUIT
    public function updateProduit($data) {
        $sql = "UPDATE Produits 
                SET Nom = :nom,
                    Categorie = :categorie,
                    NbStock = :stock,
                    Prix = :prix,
                    description = :description
                WHERE Code = :code";

        $stmt = $this->pdo->prepare($sql);

        return $stmt->execute([
            'code' => $data['code'],
            'nom' => $data['nom'],
            'categorie' => $data['categorie'],
            'stock' => $data['stock'],
            'prix' => $data['prix'],
            'description' => $data['description']
        ]);
    }

    // ❌ SUPPRIMER PRODUIT
    public function deleteProduit($code) {
        $sql = "DELETE FROM Produits WHERE Code = :code";
        $stmt = $this->pdo->prepare($sql);

        return $stmt->execute(['code' => $code]);
    }

    // 🔍 RECHERCHER UN PRODUIT PAR CODE
    public function getProduitByCode($code) {
        $sql = "SELECT * FROM Produits WHERE Code LIKE :code";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['code' => "%$code%"]);
        return $stmt->fetchAll(); 
    }
}
?>