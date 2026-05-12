<?php
require_once __DIR__ . "/../../controller/Produit/ProduitController.php";
require_once __DIR__ . "/../../controller/Produit/BoutiqueController.php";
require_once __DIR__ . "/../../controller/Produit/StockController.php";
require_once __DIR__ . "/../../Models/Produit/Categorie.php";

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

$controller = new ProduitController();
$controllerB = new BoutiqueController();
$stockController = new StockController();
$categorieModel = new Categories();
$categories = $categorieModel->getAllCategories();

/* PRODUITS */
if (isset($_GET['code']) && !empty($_GET['code'])) {
    $produits = $controller->search($_GET['code']);
} else {
    $produits = $controller->list();
}

/* BOUTIQUES */
if (isset($_GET['codeb']) && !empty($_GET['codeb'])) {
    $boutiques = $controllerB->searchByCode($_GET['codeb']);
} else {
    $boutiques = $controllerB->list();
}

/* STOCK */
$stocks = $stockController->getAllStocks();

$oldBoutique = $_SESSION['old_boutique'] ?? [];
$fieldErrorsBoutique = $_SESSION['field_errors_boutique'] ?? [];
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SmartPlate - Admin Dashboard Produits</title>

    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="templates/Template_Admin_Produits.css">
</head>

<body>

<?php include __DIR__ . '/../admin_sidebar.php'; ?>


<main class="main-content">

<header class="topbar">
    <h1>Dashboard Admin - Gestion des Produits</h1>
    <div class="admin-profile">
        <span>Admin</span>
        <img src="https://ui-avatars.com/api/?name=Admin&background=20c997&color=fff">
    </div>
</header>

<!-- ACTIONS -->
    <div class="stockSection">
        <button id="CategorieBtn">Gérer les Catégories</button>
        <button id="StockBtn">Gérer le stock</button>
    </div>

<!-- toggle categorie -->
    <div id="CategorieContent" class="CategorieContent">
        <h3>Gestion des catégories</h3>
        <form id="CategorieForm" method="POST" action="../../controller/Produit/CategorieController.php">
            <input type="hidden" name="CodeC" id="codeCategorieHidden">
            <input type="hidden" name="action" id="categorieAction" value="add">
            <input type="text" name="NomC" placeholder="Nom catégorie" required>
            <button type="submit">Ajouter</button>
        </form>

        
<div class="stock-table-container">
       <table id="categorieTable">
    <thead>
        <tr>
            <th>Code</th>
            <th>Catégorie</th>
            <th>Action</th>
        </tr>
    </thead>

    <tbody>
        <?php if (!empty($categories)): ?>
        <?php foreach ($categories as $c): ?>
            <tr data-code="<?= $c['CodeC'] ?>" data-codeb="<?= $c['CodeC'] ?>">
                <td><?= $c['CodeC'] ?></td>
                <td><?= $c['NomC'] ?></td>
                <td>
                    <button type="button" class="edit-categorie" data-code="<?= $c['CodeC'] ?>" data-nom="<?= $c['NomC'] ?>"> ✏️</button>
                    <a href="../../controller/Produit/CategorieController.php?delete=<?= $c['CodeC'] ?>" onclick="return confirm('Supprimer cette catégorie ?')">
                    🗑️
                    </a>
            </td>
            </tr>
        <?php endforeach; ?>
        <?php else: ?>
        <tr>
            <td colspan="9" style="text-align:center;">Aucune catégorie trouvée ❌</td>
        </tr>
        <?php endif; ?>
    </tbody>
</table>
</div>
</div>


<!-- toggle stock -->
    <div id="stockContent" class="stockContent">
        <h3>Gestion du stock</h3>
        <form id="stockForm" method="POST" action="../../controller/Produit/StockController.php">
            <select class="select_stock"name="Code" required>
                <option value="" disabled selected>Produit</option>
                <?php foreach ($produits as $p): ?>
                    <option value="<?= $p['Code'] ?>" data-image="<?= $p['Image'] ?>">
                        <?= $p['Nom'] ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <select class="select_stock" name="CodeB" required>
            <option value="" disabled selected>Boutique</option>
                <?php foreach ($boutiques as $b): ?>
                    <option value="<?= $b['CodeB'] ?>">
                        <?= $b['NomB'] ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <input type="number" name="Stock" placeholder="Stock">
            <button type="submit">Ajouter</button>
        </form>

        <div class="stock-table-container">
            <table id="stockTable">
                <thead>
                    <tr>
                        <th>Image</th>
                        <th>Produit</th>
                        <th>Boutique</th>
                        <th>Stock</th>
                        <th>Action</th>
                    </tr>
                </thead>

                <tbody>
                    <?php foreach ($stocks as $s): ?>
                        <tr data-code="<?= $s['Code'] ?>" data-codeb="<?= $s['CodeB'] ?>">
                            <td>
                                <?php if (!empty($s['Image'])): ?>
                                    <img src="../<?= $s['Image'] ?>"
                                        alt="Produit"
                                        style="width:30px; height:30px; object-fit:cover; border-radius:6px; cursor:pointer;"
                                        class="btn-image"
                                        data-img="../<?= $s['Image'] ?>">
                                <?php else: ?>
                                    <span style="color:gray;">Aucune image</span>
                                <?php endif; ?>
                            </td>

                            <td><?= $s['Produit'] ?></td>
                            <td><?= $s['Boutique'] ?></td>
                            <td><?= $s['Stock'] ?></td>

                            <!-- ✅ suppression sans reload -->
                            <td class="delete-stock" style="cursor:pointer;">🗑️</td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

    </div>

<div class="admin-actions">
    <div id="selectedInfo"></div>
    <div class="action-buttons">
        <button id="addProductBtn">➕ Nouveau produit</button>
    </div>

    <form id="searchForm" class="search-box" method="GET" action="Admin_Produits.php">
        <input type="text" id="searchInputProduit" name="code" placeholder="Rechercher un produit...">
        <button type="submit" name="action" value="search">🔍</button>
    </form>
</div>

<!-- TABLE PRODUITS -->
<div class="table-container">
<table id="productsTable">
    <thead>
        <tr>
            <th>Code Produit</th>
            <th>Produit</th>
            <th>Catégorie</th>
            <th>Prix</th>
            <th>Description</th>
            <th>Image</th>
            <th>Panier</th>
            <th>Actions</th>
        </tr>
    </thead>

    <tbody>
        <?php if (!empty($produits)): ?>
        <?php foreach ($produits as $p): ?>

        <tr class="product-row" data-code="<?= $p['Code'] ?>">
            <td><?= $p['Code'] ?></td>
            <td><?= $p['Nom'] ?></td>
            <td><?= $p['Categorie'] ?></td>
            <td><?= $p['Prix'] ?></td>
            <td><?= $p['description'] ?></td>

            <td>
                <?php if (!empty($p['Image'])): ?>
                    <img src="../<?= $p['Image'] ?>" 
                        alt="Produit"
                        style="width:30px; height:30px; object-fit:cover; border-radius:6px; cursor:pointer;"
                        class="btn-image"
                        data-img="../<?= $p['Image'] ?>">
                <?php else: ?>
                    <span style="color:gray;">Aucune image</span>
                <?php endif; ?>
            </td>

            <td><?= isset($p['option_panier']) && $p['option_panier'] ? 'Oui' : 'Non' ?></td>

            <td>
                <button class="btn-edit" data-code="<?= $p['Code'] ?>">✏️</button>
                <button class="btn-delete" data-code="<?= $p['Code'] ?>">🗑️</button>
            </td>
        </tr>

        <?php endforeach; ?>
        <?php else: ?>
        <tr>
            <td colspan="9" style="text-align:center;">Aucun produit trouvé ❌</td>
        </tr>
        <?php endif; ?>
    </tbody>
</table>
</div>

<!-- FORM MODAL -->
<div id="modalProduit" class="modal">
<div class="modal-content">

<span id="closeModal">&times;</span>
<h2 id="titre_form">Ajouter un produit</h2>

<form id="formProduit" action="../../controller/Produit/ProduitController.php" method="POST" enctype="multipart/form-data">

    <input type="hidden" id="code" name="code">

    <input type="text" id="nom" name="nom" placeholder="Nom" required>
    <small class="error" id="nomError"></small>

    <select id="categorie" class="select_categorie" name="CodeC" required>
        <option value="" disabled selected>Catégorie</option>
        <?php foreach ($categories as $c): ?>
            <option value="<?= $c['NomC'] ?>">
                <?= $c['NomC'] ?>
            </option>
        <?php endforeach; ?>
    </select>

    <input type="number" id="prix" name="prix" step="0.01" placeholder="Prix" required>
    <small class="error" id="prixError"></small>

    <textarea id="description" name="description" placeholder="Description"></textarea>

    <div style="margin: 10px 0; display: flex; align-items: center; gap: 8px;">
        <input type="checkbox" id="option_panier" name="option_panier" value="1" style="width: auto;" checked>
        <label for="option_panier">Option Panier (Autoriser l'ajout au panier)</label>
    </div>

    <input type="file" id="image" name="image" accept="image/*">

    <button type="submit" id="submitBtn" name="action" value="add">Ajouter</button>

</form>
</div>
</div>


<!-- BOUTIQUE -->
<div class="boutique-section">
    <button id="addboutiqueBtn">➕ Nouvelle boutique</button>

    <form id="searchFormBoutique" class="search-box" method="GET" action="../../controller/Produit/BoutiqueController.php">
        <input type="text" name="codeb" placeholder="Rechercher une boutique...">
        <button type="submit" name="action" value="search">🔍</button>
    </form>
</div>



<!-- TABLE BOUTIQUES -->
<div class="table-container">
<table id="boutiqueTable">
<thead>
<tr>
    <th>Code</th>
    <th>Nom</th>
    <th>Email</th>
    <th>Tel</th>
    <th>Adresse</th>
    <th>Ville</th>
    <th>Code postal</th>
    <th>Pays</th>
    <th>Actions</th>

</tr>
</thead>

<tbody>

<?php if (!empty($boutiques)): ?>
<?php foreach ($boutiques as $f): ?>

<tr>
    <td><?= $f['CodeB'] ?></td>
    <td><?= $f['NomB'] ?></td>
    <td><?= $f['EmailB'] ?></td>
    <td><?= $f['TelB'] ?></td>
    <td><?= $f['AdresseB'] ?></td>
    <td><?= $f['VilleB'] ?></td>
    <td><?= $f['Code_postalB'] ?></td>
    <td><?= $f['PaysB'] ?></td>
    <td>
        <button class="btn-edit-boutique" data-code="<?= $f['CodeB'] ?>">✏️</button>
        <button class="btn-delete-boutique" data-code="<?= $f['CodeB'] ?>">🗑️</button>
    </td>
</tr>

<?php endforeach; ?>
<?php else: ?>
<tr>
    <td colspan="10" style="text-align:center;">
        Aucune boutique trouvée ❌
    </td>
</tr>
<?php endif; ?>

</tbody>
</table>
</div>


<!-- formulaire boutique --> 
<div id="modalBoutique" class="modal">
<div class="modal-content">

<span id="closeModalBoutique">&times;</span>
<h2 id="titre_form_boutique">Ajouter une boutique</h2>

<form id="formBoutique" action="../../controller/Produit/BoutiqueController.php" method="POST">

    <input type="hidden" id="CodeBoutique" name="CodeB" value="<?= htmlspecialchars($oldBoutique['CodeB'] ?? '') ?>">

    <input type="text" id="NomB" name="NomB" placeholder="Nom" value="<?= htmlspecialchars($oldBoutique['NomB'] ?? '') ?>" required>
    <small class="error" id="NomBError"></small>

    <input type="email" id="EmailB" name="EmailB" placeholder="Email" value="<?= htmlspecialchars($oldBoutique['EmailB'] ?? '') ?>" required>
    <small class="error" id="EmailBError"></small>

    <!-- MODIF RECENTE: message d'erreur cible juste sous TelB -->
    <input type="text" id="TelB" name="TelB" placeholder="Téléphone" value="<?= htmlspecialchars($oldBoutique['TelB'] ?? '') ?>" required>
    <small class="error" id="TelBError"><?= htmlspecialchars($fieldErrorsBoutique['TelB'] ?? '') ?></small>

    <input type="text" id="AdresseB" name="AdresseB" placeholder="Adresse" value="<?= htmlspecialchars($oldBoutique['AdresseB'] ?? '') ?>" required>
    <small class="error" id="AdresseBError"></small>

    <input type="text" id="VilleB" name="VilleB" placeholder="Ville" value="<?= htmlspecialchars($oldBoutique['VilleB'] ?? '') ?>" required>
    <small class="error" id="VilleBError"></small>

    <input type="text" id="CodePostalB" name="Code_postalB" placeholder="Code postal" value="<?= htmlspecialchars($oldBoutique['Code_postalB'] ?? '') ?>" required>
    <small class="error" id="CodePostalBError"></small>

    <input type="text" id="PaysB" name="PaysB" placeholder="Pays" value="<?= htmlspecialchars($oldBoutique['PaysB'] ?? '') ?>" required>
    <small class="error" id="PaysBError"></small>

    <button type="submit" id="submitBtnBoutique" name="action" value="add">Ajouter</button>
</form>

<?php
if (isset($_GET['error']) && $_GET['error'] == 'boutique') {
    unset($_SESSION['errors_boutique']);
} else {
    unset($_SESSION['old_boutique']);
}
unset($_SESSION['field_errors_boutique']);
?>

</div>
</div>

</main>

<!-- IMAGE MODAL -->
<div id="imageModal" class="modal">
<span id="closeImage">&times;</span>
<img id="modalImg" style="max-width:70%; display:block; margin:auto; margin-top:100px;">
</div>

<script src="js_admin.js"></script>
</body>
</html>
