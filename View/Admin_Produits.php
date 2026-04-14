<?php
require_once "../Controller/ProduitController.php";

$controller = new ProduitController();
$produits = $produits ?? $controller->list();
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
        <div class="sidebar-header">
            <!--<span><img src="C:\Youssef\2A\ProjetWeb\SmartPlate\Logo\logo.png" alt="Avatar" height="80%" width="50%"></span> SmartPlate Admin-->
        </div>
        <div class="sidebar-menu">
            <div class="menu-category">Menu Principal</div>
            <a href="#" class="menu-item">📊 Dashboard Analytics</a>
            <a href="#" class="menu-item">🎯 Modération Objectifs</a>
            <a href="#" class="menu-item">🍽️ Journaux Utilisateurs</a>
            <a href="#" class="menu-item active">🛒 Dashboard Produits</a>
            
            <div class="menu-category" style="margin-top: 20px;">Système</div>
            <!--<a href="#" class="menu-item">⚠️ Anomalies (3)</a>-->
        </div>
    </aside>

<main class="main-content">
    <header class="topbar">
        <h1>Dashboard Admin - Gestion des Produits</h1>
        <div class="admin-profile">
            <span>Malek (Admin)</span>
            <img src="https://ui-avatars.com/api/?name=Malek&background=20c997&color=fff" alt="Profile">
        </div>
    </header>

    <!-- Actions Admin -->
<div class="admin-actions">
     <div id="selectedInfo"></div>
    <div class="action-buttons">
        <button id="addProductBtn">➕ Ajouter un produit</button>
    </div>
    
<form id="searchForm" class="search-box" method="GET" action="../Controller/ProduitController.php">   
    <input type="text" id="searchInput" name="code" placeholder="Rechercher un produit...">
    <button type="submit" name="action" value="search">🔍</button>
</form>

    <div class="category-container">
        <select id="categoryFilter">
                    <option value="all">Catégories</option>
                    <option value="Yaourt">Smart Yaourt</option>
                    <option value="Barre_énergétique">Smart Barre énergétique</option>
                    <option value="Bsissa">Smart Bsissa</option>
                    <option value="ChocoPlate">Smart ChocoPlate</option>
        </select>

        <div class="action-buttons-categorie">
            <input type="text" id="CategoryInput" placeholder="Gérer une Catégorie">
                <button id="addCategorie">➕</button>
                <button id="deleteCategorie">➖</button>
                <button id="editCategorie">✏️</button>
        </div>
    </div>
</div>

    <!-- Tableau produits -->
    <table id="productsTable">
        <thead>
            <tr>
                <th>Code Produit</th>
                <th>Produit</th>
                <th>Catégorie</th>
                <th>Nb stock</th>
                <th>Prix</th>
                <th>Déscription</th>
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
                    <td><?= $p['NbStock'] ?></td>
                    <td><?= $p['Prix'] ?></td>
                    <td><?= $p['description'] ?></td>
                    <td>
                        <button class="btn-edit" data-code="<?= $p['Code'] ?>">✏️</button>
                        <button class="btn-delete" data-code="<?= $p['Code'] ?>">🗑️</button>
                    </td>
                </tr>
            <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="7" style="text-align:center;">Aucun produit trouvé ❌</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>


    <!-- Popup produit -->
<div id="modalProduit" class="modal">
  <div class="modal-content">
    <span id="closeModal">&times;</span>
    <h2 id="titre_form">Ajouter un produit</h2>

<form id="formProduit" action="../Controller/ProduitController.php" method="POST">

  <input type="text" id="code" name="code" placeholder="Code" required>
  <small class="error" id="codeError"></small>

  <input type="text" id="nom" name="nom" placeholder="Nom" required>
  <small class="error" id="nomError"></small>

  <input type="text" id="categorie" name="categorie" placeholder="Catégorie">
  <small class="error" id="categorieError"></small>

  <input type="number" id="stock" name="stock" placeholder="Stock">
  <small class="error" id="stockError"></small>

  <input type="number" id="prix" name="prix" step="0.01" placeholder="Prix" required>
  <small class="error" id="prixError"></small>

  <textarea id="description" name="description" placeholder="Description"></textarea>

<button type="submit" id="submitBtn" name="action" value="add">Ajouter</button>

</form>
  </div>
</div>

</main>
<script src="/ProjetWeb/View/js_admin.js"></script>
</body>
</html>