<?php
require_once __DIR__ . "/../../config.php";

class Panier {
    private $pdo;

    public function __construct() {
        $this->pdo = Config::getConnexion();
    }

    // Récupérer le panier actif d'un utilisateur ou en créer un nouveau
    public function getOrCreateActivePanier($id_user) {
        $sql = "SELECT id_panier FROM panier WHERE id_user = :id_user AND statut = 'actif' LIMIT 1";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['id_user' => $id_user]);
        $panier = $stmt->fetch();

        if ($panier) {
            return $panier['id_panier'];
        }

        // Créer un nouveau panier
        $sql = "INSERT INTO panier (id_user, statut) VALUES (:id_user, 'actif')";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['id_user' => $id_user]);
        return $this->pdo->lastInsertId();
    }

    // Ajouter un produit au panier
    public function addProduit($id_panier, $code_produit, $quantite = 1) {
        // Vérifier si le produit est déjà dans le panier
        $sql = "SELECT id, quantite FROM panier_produit WHERE id_panier = :id_panier AND code_produit = :code_produit";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            'id_panier' => $id_panier,
            'code_produit' => $code_produit
        ]);
        $item = $stmt->fetch();

        if ($item) {
            // Mettre à jour la quantité
            $sql = "UPDATE panier_produit SET quantite = quantite + :quantite WHERE id = :id";
            $stmt = $this->pdo->prepare($sql);
            return $stmt->execute([
                'quantite' => $quantite,
                'id' => $item['id']
            ]);
        } else {
            // Ajouter nouveau produit
            $sql = "INSERT INTO panier_produit (id_panier, code_produit, quantite) VALUES (:id_panier, :code_produit, :quantite)";
            $stmt = $this->pdo->prepare($sql);
            return $stmt->execute([
                'id_panier' => $id_panier,
                'code_produit' => $code_produit,
                'quantite' => $quantite
            ]);
        }
    }

    // Récupérer le contenu du panier
    public function getPanierItems($id_panier) {
        $sql = "SELECT pp.*, p.Nom, p.Prix, p.Image 
                FROM panier_produit pp 
                JOIN produits p ON pp.code_produit = p.Code 
                WHERE pp.id_panier = :id_panier";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['id_panier' => $id_panier]);
        return $stmt->fetchAll();
    }

    // Supprimer un produit du panier
    public function removeProduit($id_panier, $code_produit) {
        $sql = "DELETE FROM panier_produit WHERE id_panier = :id_panier AND code_produit = :code_produit";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([
            'id_panier' => $id_panier,
            'code_produit' => $code_produit
        ]);
    }

    // Mettre à jour la quantité d'un produit
    public function updateQuantite($id_panier, $code_produit, $quantite) {
        if ($quantite <= 0) {
            return $this->removeProduit($id_panier, $code_produit);
        }
        $sql = "UPDATE panier_produit SET quantite = :quantite WHERE id_panier = :id_panier AND code_produit = :code_produit";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([
            'quantite' => $quantite,
            'id_panier' => $id_panier,
            'code_produit' => $code_produit
        ]);
    }
}
?>
