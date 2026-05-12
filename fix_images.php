<?php
require 'c:/xampp/htdocs/integration/config.php';
$pdo = Config::getConnexion();
$stmt = $pdo->query('SELECT Code, Image FROM produits');
foreach($stmt->fetchAll() as $row) {
    $img = $row['Image'];
    if (empty($img)) continue;
    $newImg = str_replace('View/Images/', 'Produit/Images/', $img);
    if ($img !== $newImg) {
        $pdo->prepare('UPDATE produits SET Image = ? WHERE Code = ?')->execute([$newImg, $row['Code']]);
        echo "Updated {$row['Code']}: $newImg\n";
    }
}
echo "Done.\n";
