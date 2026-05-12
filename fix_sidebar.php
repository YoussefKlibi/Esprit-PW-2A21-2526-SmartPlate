<?php
$files = [
    'c:/xampp/htdocs/integration/view/User/backend/user_list.php',
    'c:/xampp/htdocs/integration/view/User/backend/admin_welcome.php',
    'c:/xampp/htdocs/integration/view/Suivi_Nutritionnel/BackOffice/admin_journaux.php',
    'c:/xampp/htdocs/integration/view/Suivi_Nutritionnel/BackOffice/admin_dashboard.php',
    'c:/xampp/htdocs/integration/view/Suivi_Nutritionnel/BackOffice/admin_objectifs.php',
    'c:/xampp/htdocs/integration/view/Recette/backoffice/ingredients.php',
    'c:/xampp/htdocs/integration/view/Recette/backoffice/recettes.php',
    'c:/xampp/htdocs/integration/view/Recette/backoffice/dashboard.php',
    'c:/xampp/htdocs/integration/view/Reclamation/back/admin_reply_reclamation.php'
];

foreach ($files as $file) {
    if (file_exists($file)) {
        $content = file_get_contents($file);
        $newContent = str_replace('<a href="#" class="menu-item">📦 Produit</a>', '<a href="../../Produit/Admin_Produits.php" class="menu-item">📦 Produits & Boutiques</a>', $content);
        if ($content !== $newContent) {
            file_put_contents($file, $newContent);
            echo "Updated $file\n";
        }
    }
}
echo "Done.\n";
