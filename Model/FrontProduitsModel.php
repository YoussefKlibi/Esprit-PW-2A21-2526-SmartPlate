<?php
require_once __DIR__ . "/../Config/config.php";

class FrontProduitsModel {

    private $pdo;

    public function __construct() {
        $this->pdo = Config::getConnexion();
    }

    // 🔥 PRODUITS + STOCK + BOUTIQUE (CORRIGÉ)
    public function getProduitsAvecStock() {

        $sql = "SELECT 
                    p.Code,
                    p.Nom,
                    p.Categorie,
                    p.Prix,
                    p.Image,
                    p.description,
                    b.NomB AS Boutique,
                    pb.Stock
                FROM produits p
                LEFT JOIN produits_boutiques pb 
                    ON p.Code = pb.Code
                LEFT JOIN boutiques b 
                    ON pb.CodeB = b.CodeB
                ORDER BY p.Nom";

        $rows = $this->pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);

        $produits = [];

        foreach ($rows as $row) {

            $code = $row['Code'];

            // 🟢 initialisation produit
            if (!isset($produits[$code])) {
                $produits[$code] = [
                    'Code' => $code,
                    'Nom' => $row['Nom'],
                    'Categorie' => $row['Categorie'],
                    'Prix' => $row['Prix'],
                    'Image' => $row['Image'],
                    'description' => $row['description'],
                    'stocks' => []
                ];
            }

            // 🟢 ajout stock seulement si boutique existe
            if (!empty($row['Boutique']) && $row['Boutique'] !== null) {
                $produits[$code]['stocks'][] = [
                    'boutique' => $row['Boutique'],
                    'stock' => (int)$row['Stock']
                ];
            }
        }

        return array_values($produits); // 🔥 important pour foreach propre
    }
}
