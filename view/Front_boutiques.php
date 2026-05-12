<?php
require_once __DIR__ . "/../Model/Boutiques.php";

$model = new Boutiques();
$boutiques = $boutiques ?? ($model->getAllBoutiques() ?? []);
?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SmartPlate - Boutiques</title>

    <link rel="stylesheet" href="templates/Boutiques.css">

    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
</head>

<body>

<!-- SIDEBAR -->
<aside class="sidebar">

    <div class="sidebar-logo">
        <img src="Images/logo.jpg" alt="Logo" width="150">
        <h2>SmartPlate</h2>
    </div>

    <nav class="sidebar-nav">

        <span class="nav-section-title">Menu Principal</span>

        <a href="Journal.html" class="nav-item">🍽️ Journal Alimentaire</a>
        <a href="Objectif.html" class="nav-item">🎯 Mes Objectifs</a>
        <a href="Progression.html" class="nav-item">📈 Ma Progression</a>
        <a href="Front_produits.php" class="nav-item">🛒 Produits</a>
        <a href="Front_boutiques.php" class="nav-item active">🏪 Boutiques</a>

    </nav>

</aside>

<!-- MAIN -->
<main class="main-content">

    <!-- HERO -->
    <section class="hero">

        <img src="Images/im2.png" class="hero-image">

        <div class="hero-overlay">

            <!-- SEARCH (AJAX) -->
            <form class="search-box-boutiques" onsubmit="return false;">

                <input type="text"
                       id="searchInputBoutique"
                       placeholder="Trouvez votre boutique...">

                <button type="button">🔍</button>

            </form>

        </div>

    </section>

    <!-- CONTENT -->
    <section class="content">

        <div class="shops-list">

            <?php if (!empty($boutiques)): ?>

                <?php foreach ($boutiques as $boutique): ?>

                    <div class="shop-card">

                        <div class="shop-header">
                            <img src="Images/logo.jpg" class="shop-logo">
                            <h3><?= htmlspecialchars($boutique['NomB']) ?></h3>
                        </div>

                        <p>📍 <?= htmlspecialchars($boutique['AdresseB']) ?></p>
                        <p>📞 <?= htmlspecialchars($boutique['TelB']) ?></p>
                        <p>✉️ <?= htmlspecialchars($boutique['EmailB']) ?></p>

                    </div>

                <?php endforeach; ?>

            <?php else: ?>

                <div class="shop-card">
                    <h3>Aucune boutique trouvée</h3>
                </div>

            <?php endif; ?>

        </div>

        <!-- MAP -->
        <div class="map-container">
            <div id="map" style="height: 500px; width: 100%;"></div>
        </div>

    </section>

</main>

<!-- DATA JS INIT -->
<script>
const boutiques = <?= json_encode($boutiques ?? []); ?>;
</script>

<!-- JS -->
<script src="js_boutiques.js"></script>

</body>
</html>