<?php
require_once __DIR__ . "/../Config/config.php";

class Stock {

    private $pdo;

    public function __construct() {
        $this->pdo = Config::getConnexion();
    }

    // 🔹 Récupérer tous les stocks
    public function getAll() {
        $sql = "SELECT 
                    p.Code,
                    p.Nom AS Produit,
                    p.Image,
                    b.NomB AS Boutique,
                    pb.Stock,
                    pb.CodeB
                FROM produits_boutiques pb
                JOIN produits p ON pb.Code = p.Code
                JOIN boutiques b ON pb.CodeB = b.CodeB";

        return $this->pdo->query($sql)->fetchAll();
    }


    // 🔹 Trouver un stock
    public function find($code, $codeB) {
        $sql = "SELECT * FROM produits_boutiques WHERE Code=? AND CodeB=?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$code, $codeB]);
        return $stmt->fetch();
    }

    // 🔹 Ajouter
    public function add($code, $codeB, $stock) {
        $sql = "INSERT INTO produits_boutiques (Code, CodeB, Stock)
                VALUES (?, ?, ?)";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([$code, $codeB, $stock]);
    }

    // 🔹 Update (ajout stock)
    public function updateStock($code, $codeB, $stock) {
        $sql = "UPDATE produits_boutiques 
                SET Stock = Stock + ? 
                WHERE Code=? AND CodeB=?";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([$stock, $code, $codeB]);
    }

    // 🔹 Supprimer (optionnel)
    public function delete($code, $codeB) {
        $sql = "DELETE FROM produits_boutiques WHERE Code=? AND CodeB=?";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([$code, $codeB]);
    }
}
