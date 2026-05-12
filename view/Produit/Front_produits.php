<?php
require_once __DIR__ . "/../../Models/Produit/Produits.php";
require_once __DIR__ . "/../../Models/Produit/Categorie.php";
require_once __DIR__ . "/../../Models/Produit/Stock.php";

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

$base_url = '/integration/view';

$model = new Produits();
$sort = $_GET['sort'] ?? 'default';
if (!in_array($sort, ['default', 'asc', 'desc'], true)) {
    $sort = 'default';
}
$produits = $model->getAllProduits($sort) ?? [];

$categorieModel = new Categories();
$categories = $categorieModel->getAllCategories();

$stockModel = new Stock();
$allStocks = $stockModel->getAll();
$stockByProduct = [];
foreach ($allStocks as $s) {
    if (!isset($stockByProduct[$s['Code']])) {
        $stockByProduct[$s['Code']] = [];
    }
    $stockByProduct[$s['Code']][] = [
        'boutique' => $s['Boutique'],
        'quantite' => $s['Stock']
    ];
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SmartPlate - Produits</title>

    <link rel="stylesheet" href="templates/template.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
</head>

<body>

<!-- SIDEBAR -->
<?php include __DIR__ . '/../front_sidebar.php'; ?>

<!-- CONTENU PRINCIPAL -->
<div class="dashboard">

    <header class="dashboard-header">
        <h1>Produits</h1>
        <span class="date-badge">Mis à jour le <?= date('d M Y') ?></span>
    </header>

    <!-- FILTRES -->
    <div class="card">
        <div class="form-row">

            
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
        <form method="GET">
            <select id="sortPrice" name="sort" class="form-control" onchange="this.form.submit()">
                <option value="default" <?= $sort === 'default' ? 'selected' : '' ?>>Tri par prix</option>
                <option value="asc" <?= $sort === 'asc' ? 'selected' : '' ?>>Prix croissant</option>
                <option value="desc" <?= $sort === 'desc' ? 'selected' : '' ?>>Prix décroissant</option>
            </select>
        </form>
    </div>


    <!-- PRODUITS -->
    <div class="products-grid" id="productsGrid">

        <?php if (!empty($produits)) : ?>

            <?php foreach ($produits as $p) : ?>

                <div class="product-card"
                     data-name="<?= htmlspecialchars($p['Nom']) ?>"
                     data-category="<?= htmlspecialchars($p['Categorie']) ?>">

                    <?php if (!empty($p['Image'])): ?>
                        <img src="<?= $base_url . '/' . htmlspecialchars($p['Image']) ?>"
                             alt="<?= htmlspecialchars($p['Nom']) ?>">
                    <?php else: ?>
                        <img src="https://ui-avatars.com/api/?name=<?= urlencode($p['Nom']) ?>&background=d4f283&color=1a1a1a&size=200"
                             alt="<?= htmlspecialchars($p['Nom']) ?>">
                    <?php endif; ?>

                    <h3><?= htmlspecialchars($p['Nom']) ?></h3>

                    <p class="price" data-price="<?= $p['Prix'] ?>">
                        <?= htmlspecialchars($p['Prix']) ?> DT
                    </p>

                    <div class="product-actions">

                        <button class="btn-action"
                            onclick='showDetails(
                                <?= json_encode($p["Code"]) ?>,
                                <?= json_encode($p["Nom"]) ?>,
                                <?= json_encode($p["description"]) ?>,
                                <?= json_encode(!empty($p["Image"]) ? $base_url . "/" . $p["Image"] : "") ?>,
                                <?= json_encode($p["Prix"]) ?>,
                                <?= json_encode($stockByProduct[$p["Code"]] ?? []) ?>,
                                <?= json_encode(isset($p["option_panier"]) && $p["option_panier"]) ?>
                            )'>
                            Voir détails
                        </button>

                        <?php if (isset($p['option_panier']) && $p['option_panier']): ?>
                            <button class="btn-panier" data-code="<?= $p['Code'] ?>">
                                Ajouter au panier
                            </button>
                        <?php endif; ?>

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
        <img id="modalImage" src="" alt="Produit">
        <h2 id="modalTitle"></h2>
        <p id="modalPrice"></p>
        <p id="modalDescription"></p>
        
        <div id="modalStockContainer" style="margin-top: 15px; border-top: 1px solid #ddd; padding-top: 10px;">
            <h4 style="font-weight: 400 ;margin-bottom: 8px;">Disponibilité en boutique :</h4>
            <ul id="modalStockList" style="list-style: none; padding: 0;"></ul>
        </div>

        <button id="modalAddBtn" class="btn-panier" style="margin-top: 15px;">Ajouter au panier</button>
    </div>
</div>

<script src="js_produits.js"></script>
</body>
</html>
