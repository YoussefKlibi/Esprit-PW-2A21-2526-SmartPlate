<?php
require_once __DIR__ . "/../Config/config.php";

class Produits {

    private $pdo;

    public function __construct() {
        $this->pdo = Config::getConnexion();
    }

    // ➕ AJOUTER PRODUIT
    public function addProduit($data) {
        $sql = "INSERT INTO produits 
                (Code, Nom, Categorie, Prix, description, Image)
                VALUES (:code, :nom, :categorie, :prix, :description, :image)";

        $stmt = $this->pdo->prepare($sql);

        return $stmt->execute([
            'code' => $data['code'],
            'nom' => $data['nom'],
            'categorie' => $data['categorie'],
            'prix' => $data['prix'],
            'description' => $data['description'],
            'image' => $data['Image'] ?? ""
        ]);
    }

    // 📄 AFFICHER TOUS LES PRODUITS
    public function getAllProduits() {
        $sql = "SELECT * FROM produits";
        $stmt = $this->pdo->query($sql);
        return $stmt->fetchAll();
    }

    // ✏️ MODIFIER PRODUIT
    public function updateProduit($data) {
        $sql = "UPDATE produits 
                SET 
                    Nom = :nom,
                    Categorie = :categorie,
                    Prix = :prix,
                    description = :description,
                    Image = :image
                WHERE Code = :code";

        $stmt = $this->pdo->prepare($sql);

        return $stmt->execute([
            'code' => $data['code'],
            'nom' => $data['nom'],
            'categorie' => $data['categorie'],
            'prix' => $data['prix'],
            'description' => $data['description'],
            'image' => $data['Image'] ?? ""
        ]);
    }

    // ❌ SUPPRIMER PRODUIT
    public function deleteProduit($code) {
        $sql = "DELETE FROM produits WHERE Code = :code";
        $stmt = $this->pdo->prepare($sql);

        return $stmt->execute(['code' => $code]);
    }

// 🔍 RECHERCHER UN PRODUIT PAR CODE (ou partiel)
    public function getProduitByCode($code) {
        $sql = "SELECT * FROM produits WHERE Code = :code";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            'code' => $code
        ]);

        return $stmt->fetchAll();
    }


    /* 🔍 RECHERCHER PAR BOUTIQUE (AJOUT IMPORTANT)
    public function getProduitsByFournisseur($CodeB) {
        $sql = "SELECT * FROM produits WHERE CodeB LIKE :CodeB";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['CodeB' => "%$CodeB%"]);
        return $stmt->fetchAll();
    }*/
}
?>