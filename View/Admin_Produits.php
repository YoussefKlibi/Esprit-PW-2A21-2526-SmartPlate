<?php
require_once "../Controller/ProduitController.php";
require_once "../Controller/BoutiqueController.php";
require_once "../Controller/StockController.php";

$controller = new ProduitController();
$controllerB = new BoutiqueController();
$stockController = new StockController();

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
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SmartPlate - Admin Dashboard</title>

    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../View/templates/Template_Admin_Produits.css">
</head>

<body>

<aside class="admin-sidebar">
    <div class="sidebar-header"></div>

    <div class="sidebar-menu">
        <div class="menu-category">Menu Principal</div>
        <a href="#" class="menu-item">📊 Dashboard Analytics</a>
        <a href="#" class="menu-item">🎯 Modération Objectifs</a>
        <a href="#" class="menu-item">🍽️ Journaux Utilisateurs</a>
        <a href="#" class="menu-item active">🛒 Dashboard Produits</a>
    </div>
</aside>

<main class="main-content">

<header class="topbar">
    <h1>Dashboard Admin - Gestion des Produits</h1>
    <div class="admin-profile">
        <span>Malek (Admin)</span>
        <img src="https://ui-avatars.com/api/?name=Malek&background=20c997&color=fff">
    </div>
</header>

<!-- ACTIONS -->
    <div class="stockSection">
        <button id="StockBtn">Gérer le stock</button>
    </div>

    <div id="stockContent" class="stockContent">
        <h3>Gestion du stock</h3>
        <form id="stockForm" method="POST" action="../Controller/StockController.php">
            <select class="select_stock"name="Code" required>
                <?php foreach ($produits as $p): ?>
                    <option value="<?= $p['Code'] ?>" data-image="<?= $p['Image'] ?>">
                        <?= $p['Nom'] ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <select class="select_stock" name="CodeB" required>
                <?php foreach ($boutiques as $b): ?>
                    <option value="<?= $b['CodeB'] ?>">
                        <?= $b['NomB'] ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <input type="number" name="Stock" placeholder="Stock">
            <button type="submit">Ajouter</button>
        </form>

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
                            data-img="<?= $s['Image'] ?>">
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

<div class="admin-actions">
    <div id="selectedInfo"></div>
    <div class="action-buttons">
        <button id="addProductBtn">➕ Nouveau produit</button>
    </div>

    <form id="searchForm" class="search-box" method="GET" action="../View/Admin_Produits.php">
        <input type="text" id="searchInputProduit" name="code" placeholder="Rechercher un produit...">
        <button type="submit" name="action" value="search">🔍</button>
    </form>

    <!--<div class="category-container">
        <select id="categoryFilter">
            <option value="all">Catégories</option>
            <option value="Yaourt">Smart Yaourt</option>
            <option value="Barre_énergétique">Smart Barre énergétique</option>
            <option value="Bsissa">Smart Bsissa</option>
            <option value="ChocoPlate">Smart ChocoPlate</option>
        </select>

        <div class="action-buttons-categorie">
            <input type="text" id="CategoryInput" placeholder="Gérer une Catégorie">
            <button id="addCategorie" type="button">➕</button>
            <button id="deleteCategorie" type="button">➖</button>
            <button id="editCategorie" type="button">✏️</button>
        </div>
    </div> -->
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
                    data-img="<?= $p['Image'] ?>">
            <?php else: ?>
                <span style="color:gray;">Aucune image</span>
            <?php endif; ?>
        </td>

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

<form id="formProduit" action="../Controller/ProduitController.php" method="POST" enctype="multipart/form-data">

    <input type="text" id="code" name="code" placeholder="Code" required>
    <small class="error" id="codeError"></small>

    <input type="text" id="nom" name="nom" placeholder="Nom" required>
    <small class="error" id="nomError"></small>

    <input type="text" id="categorie" name="categorie" placeholder="Catégorie">
    <small class="error" id="categorieError"></small>

    <input type="number" id="prix" name="prix" step="0.01" placeholder="Prix" required>
    <small class="error" id="prixError"></small>

    <textarea id="description" name="description" placeholder="Description"></textarea>

    <input type="file" id="image" name="image" accept="image/*">

    <button type="submit" id="submitBtn" name="action" value="add">Ajouter</button>

</form>
</div>
</div>


<!-- BOUTIQUE -->
<div class="boutique-section">
    <button id="addboutiqueBtn">➕ Nouvelle boutique</button>

    <form id="searchFormBoutique" class="search-box" method="GET" action="../Controller/BoutiqueController.php">
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
    <th>Latitude</th>
    <th>Longitude</th>
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
    <td><?= $f['latitude'] ?></td>
    <td><?= $f['longitude'] ?></td>
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

<form id="formBoutique" action="../Controller/BoutiqueController.php" method="POST">
    <input type="text" id="CodeBoutique" name="CodeB" placeholder="Code Boutique" required>
    <small class="error" id="CodeBError"></small>

    <input type="text" id="NomB" name="NomB" placeholder="Nom" required>
    <small class="error" id="NomBError"></small>

    <input type="email" id="EmailB" name="EmailB" placeholder="Email" required>
    <small class="error" id="EmailBError"></small>

    <input type="text" id="TelB" name="TelB" placeholder="Téléphone" required>
    <small class="error" id="TelBError"></small>

    <input type="text" id="AdresseB" name="AdresseB" placeholder="Adresse" required>
    <small class="error" id="AdresseBError"></small>

    <input type="text" id="VilleB" name="VilleB" placeholder="Ville" required>
    <small class="error" id="VilleBError"></small>

    <input type="text" id="CodePostalB" name="Code_postalB" placeholder="Code postal" required>
    <small class="error" id="CodePostalBError"></small>

    <input type="text" id="PaysB" name="PaysB" placeholder="Pays" required>
    <small class="error" id="PaysBError"></small>

    <input type="text" id="Latitude" name="latitude" placeholder="Latitude">
    <small class="error" id="LatitudeError"></small>

    <input type="text" id="Longitude" name="longitude" placeholder="Longitude">
    <small class="error" id="LongitudeError"></small>

    <button type="submit" id="submitBtnBoutique" name="action" value="add">Ajouter</button>
</form>

<?php
if (isset($_GET['error']) && $_GET['error'] == 'boutique') {
    session_start();

    if (!empty($_SESSION['errors_boutique'])) {
        echo "<div style='color:red;font-weight:bold;margin:10px 0;'>";
        echo "⚠️ Erreurs de saisie :<ul>";

        foreach ($_SESSION['errors_boutique'] as $e) {
            echo "<li>$e</li>";
        }

        echo "</ul></div>";

        unset($_SESSION['errors_boutique']);
    }
}
?>

</div>
</div>

</main>

<!-- IMAGE MODAL -->
<div id="imageModal" class="modal">
<span id="closeImage">&times;</span>
<img id="modalImg" style="max-width:70%; display:block; margin:auto; margin-top:100px;">
</div>

<script src="/ProjetWeb/View/js_admin.js"></script>
</body>
</html>