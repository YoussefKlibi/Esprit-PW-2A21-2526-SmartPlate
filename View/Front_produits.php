<?php
require_once __DIR__ . "/../Model/Produits.php";
require_once "../Model/Categorie.php";

$model = new Produits();
$produits = $model->getAllProduits() ?? [];

$categorieModel = new Categories();
$categories = $categorieModel->getAllCategories();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>SmartPlate - Produits</title>

    <link rel="stylesheet" href="templates/template.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
</head>

<body>

<!-- SIDEBAR -->
<aside class="sidebar">
    <div class="sidebar-logo">
        <img src="Images/logo.jpg" alt="Logo" height="150" width="150">
        <h2>SmartPlate</h2>
    </div>

    <nav class="sidebar-nav">
        <span class="nav-section-title">Menu Principal</span>

        <a href="Journal.html" class="nav-item">🍽️ Journal Alimentaire</a>
        <a href="Objectif.html" class="nav-item">🎯 Mes Objectifs</a>
        <a href="Progression.html" class="nav-item">📈 Ma Progression</a>
        <a href="Front_produits.php" class="nav-item active">🛒 Produits</a>
        <a href="Front_boutiques.php" class="nav-item ">🏪 Boutiques</a>
    </nav>

    <div class="sidebar-footer">
        <div class="user-badge">
            <img src="https://ui-avatars.com/api/?name=Malek&background=d4f283&color=1a1a1a&rounded=true" alt="Avatar">
            <div class="user-info">
                <span class="user-name">Malek</span>
                <span class="user-status">Connectée</span>
            </div>
        </div>
    </div>
</aside>

<!-- CONTENU PRINCIPAL -->
<div class="dashboard">

    <header class="dashboard-header">
        <h1>Produits</h1>
        <span class="date-badge">Mis à jour le 06 Avril</span>
    </header>

    <!-- FILTRES -->
    <div class="card">
        <div class="form-row">

            <!--<div class="form-group">
                <input type="text" id="searchInput" class="form-control"
                       placeholder="Rechercher un produit...">
            </div>-->


            
<div class="form-group">
    <select id="filterCategory" class="select_categorie">

        <option value="all">Toutes les catégories</option>

        <?php foreach ($categories as $c): ?>
            <option value="<?= htmlspecialchars($c['NomC']) ?>">
                <?= htmlspecialchars($c['NomC']) ?>
            </option>
        <?php endforeach; ?>

    </select>
</div>


        </div>
    </div>


    <div class="form-group">
        <select id="sortPrice" class="form-control">
            <option value="default">Tri par prix</option>
            <option value="asc">Prix croissant</option>
            <option value="desc">Prix décroissant</option>
        </select>
    </div>


    <!-- PRODUITS -->
    <div class="products-grid" id="productsGrid">

        <?php if (!empty($produits)) : ?>

            <?php foreach ($produits as $p) : ?>

                <div class="product-card"
                     data-name="<?= htmlspecialchars($p['Nom']) ?>"
                     data-category="<?= htmlspecialchars($p['Categorie']) ?>">

                    <img src="../<?= htmlspecialchars($p['Image']) ?>"
                         alt="<?= htmlspecialchars($p['Nom']) ?>">

                    <h3><?= htmlspecialchars($p['Nom']) ?></h3>

                    <p class="price">
                        <?= htmlspecialchars($p['Prix']) ?> DT
                    </p>

                    <div class="product-actions">

                        <button class="btn-action"
                            onclick='showDetails(
                                <?= json_encode($p["Nom"]) ?>,
                                <?= json_encode($p["description"]) ?>
                            )'>
                            Voir détails
                        </button>

                        <button class="btn-panier">
                            Ajouter au panier
                        </button>

                    </div>
                </div>

            <?php endforeach; ?>

        <?php else : ?>

            <p>Aucun produit disponible</p>

        <?php endif; ?>

    </div>

    <!-- PAGINATION -->
    <div class="pagination-container" id="paginationBottom"></div>

</div>

<!-- MODAL -->
<div id="productModal" class="modal">
    <div class="modal-content">
        <span class="close" onclick="closeModal()">&times;</span>
        <h2 id="modalTitle"></h2>
        <p id="modalDescription"></p>
    </div>
</div>

<script src="js_produits.js"></script>
</body>
</html>
