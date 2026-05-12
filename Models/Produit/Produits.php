<?php
require_once __DIR__ . "/../../config.php";

class Produits {

    private $pdo;

    public function __construct() {
        $this->pdo = Config::getConnexion();
    }

    // ➕ AJOUTER PRODUIT
    public function addProduit($data) {
        $sql = "INSERT INTO produits 
                (/*Code, */Nom, Categorie, Prix, description, Image, option_panier)
                VALUES (/*:code, */:nom, :categorie, :prix, :description, :image, :option_panier)";

        $stmt = $this->pdo->prepare($sql);

        return $stmt->execute([
            /*'code' => $data['code'],*/
            'nom' => $data['nom'],
            'categorie' => $data['categorie'],
            'prix' => $data['prix'],
            'description' => $data['description'],
            'image' => $data['Image'] ?? "",
            'option_panier' => $data['option_panier'] ?? 1
        ]);
    }

    // 📄 AFFICHER TOUS LES PRODUITS (tri optionnel par prix)
    public function getAllProduits($sort = 'default') {
        $sql = "SELECT * FROM produits";

        if ($sort === 'asc') {
            $sql .= " ORDER BY Prix ASC";
        } elseif ($sort === 'desc') {
            $sql .= " ORDER BY Prix DESC";
        }

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
                    Image = :image,
                    option_panier = :option_panier
                WHERE Code = :code";

        $stmt = $this->pdo->prepare($sql);

        return $stmt->execute([
            'code' => $data['code'],
            'nom' => $data['nom'],
            'categorie' => $data['categorie'],
            'prix' => $data['prix'],
            'description' => $data['description'],
            'image' => $data['Image'] ?? "",
            'option_panier' => $data['option_panier'] ?? 1
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
}
?>
