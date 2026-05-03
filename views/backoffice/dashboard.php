<?php
require_once "../../config.php";

/* ===================== KPI PRINCIPAUX ===================== */

$totalRecettes = (int)$pdo->query("SELECT COUNT(*) FROM recette")->fetchColumn();
$totalIngredients = (int)$pdo->query("SELECT COUNT(*) FROM ingredient")->fetchColumn();

$moyenneCalories = $pdo->query("SELECT AVG(calories) FROM recette")->fetchColumn();
$moyenneCalories = $moyenneCalories ? round($moyenneCalories, 2) : 0;

$totalCalories = $pdo->query("SELECT SUM(calories) FROM recette")->fetchColumn();
$totalCalories = $totalCalories ? round($totalCalories, 2) : 0;

$totalProteines = $pdo->query("SELECT SUM(proteines) FROM recette")->fetchColumn();
$totalProteines = $totalProteines ? round($totalProteines, 2) : 0;

$totalLipides = $pdo->query("SELECT SUM(lipides) FROM recette")->fetchColumn();
$totalLipides = $totalLipides ? round($totalLipides, 2) : 0;

$totalGlucides = $pdo->query("SELECT SUM(glucides) FROM recette")->fetchColumn();
$totalGlucides = $totalGlucides ? round($totalGlucides, 2) : 0;

$moyenneTemps = $pdo->query("SELECT AVG(temps_preparation) FROM recette")->fetchColumn();
$moyenneTemps = $moyenneTemps ? round($moyenneTemps, 1) : 0;

/* ===================== DERNIERES RECETTES POUR LINE CHART ===================== */

$lastRecettes = $pdo->query("
    SELECT id_recette, nom_recette, calories, proteines
    FROM recette
    ORDER BY id_recette DESC
    LIMIT 6
")->fetchAll(PDO::FETCH_ASSOC);

$lastRecettes = array_reverse($lastRecettes);

$lineLabels = [];
$lineCalories = [];
$lineProteines = [];

foreach($lastRecettes as $r){
    $lineLabels[] = $r['nom_recette'];
    $lineCalories[] = (float)$r['calories'];
    $lineProteines[] = (float)$r['proteines'];
}

/* ===================== CATEGORIES POUR DONUT ===================== */

$categories = $pdo->query("
    SELECT categorie, COUNT(*) AS total
    FROM recette
    GROUP BY categorie
    ORDER BY total DESC
")->fetchAll(PDO::FETCH_ASSOC);

$donutLabels = [];
$donutValues = [];
$donutPercentages = [];

$totalCatRecettes = 0;
foreach($categories as $cat){
    $totalCatRecettes += (int)$cat['total'];
}

foreach($categories as $cat){
    $donutLabels[] = $cat['categorie'];
    $donutValues[] = (int)$cat['total'];
    $donutPercentages[] = $totalCatRecettes > 0 ? round(($cat['total'] * 100) / $totalCatRecettes) : 0;
}

/* ===================== TOP ELEMENTS ===================== */

$topRecette = $pdo->query("
    SELECT nom_recette, calories
    FROM recette
    ORDER BY calories DESC
    LIMIT 1
")->fetch(PDO::FETCH_ASSOC);

$topIngredient = $pdo->query("
    SELECT nom_ingredient, calories
    FROM ingredient
    ORDER BY calories DESC
    LIMIT 1
")->fetch(PDO::FETCH_ASSOC);

/* ===================== TABLEAUX RECENTS ===================== */

$dernieresRecettes = $pdo->query("
    SELECT id_recette, nom_recette, calories, temps_preparation, categorie
    FROM recette
    ORDER BY id_recette DESC
    LIMIT 5
")->fetchAll(PDO::FETCH_ASSOC);

$derniersIngredients = $pdo->query("
    SELECT id_ingredient, nom_ingredient, type_ingredient, calories
    FROM ingredient
    ORDER BY id_ingredient DESC
    LIMIT 5
")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Dashboard SmartPlate</title>
<link rel="stylesheet" href="../backoffice/templates/templateback.css">
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>

<div class="admin-sidebar">
    <div class="sidebar-header">🍽 SmartPlate Admin</div>

    <div class="sidebar-menu">
        <div class="menu-category">Menu</div>
        <a href="recettes.php" class="menu-item">🍴 Gestion Recettes</a>
        <a href="ingredients.php" class="menu-item">🥕 Gestion Ingrédients</a>
        <a href="dashboard.php" class="menu-item active">📊 Dashboard</a>
        <a href="utilisateurs.php" class="menu-item">👤 Utilisateurs</a>
    </div>
</div>

<div class="main-content">

    <div class="topbar">
        <div>Administration</div>
        <div class="admin-profile">
            <img src="https://i.pravatar.cc/40" alt="admin">
            Admin
        </div>
    </div>

    <div class="dashboard-container">

        <!-- Header -->
        <div class="analytics-header">
            <div>
                <h1>Dashboard</h1>
                <p>Overview of SmartPlate statistics</p>
            </div>
            <div class="analytics-tabs">
                <span class="active">DAILY</span>
                <span>WEEKLY</span>
                <span>MONTHLY</span>
                <span>YEARLY</span>
            </div>
        </div>

        <!-- Top area -->
        <div class="analytics-top-grid">

            <!-- Left big panel -->
            <div class="analytics-panel large-panel">
                <div class="panel-top-row">
                    <div>
                        <h3>Dashboard</h3>
                        <p>Evolution of latest recipes</p>
                    </div>
                </div>

                <div class="chart-wrapper-large">
                    <canvas id="lineChart"></canvas>
                </div>

                <div class="summary-button-row">
                    <div class="main-big-value">
                        <span class="big-number"><?= $totalRecettes ?></span>
                        <span class="big-label">Current Total Recipes</span>
                    </div>

                    <button class="orange-summary-btn">Last Month Summary</button>
                </div>

                <div class="small-kpi-row">
                    <div class="small-kpi">
                        <div class="small-kpi-icon red">❤</div>
                        <div>
                            <div class="small-kpi-title">Total Calories</div>
                            <div class="small-kpi-value"><?= $totalCalories ?> kcal</div>
                        </div>
                    </div>

                    <div class="small-kpi">
                        <div class="small-kpi-icon blue">⚙</div>
                        <div>
                            <div class="small-kpi-title">Top Recette</div>
                            <div class="small-kpi-value">
                                <?= !empty($topRecette) ? htmlspecialchars($topRecette['nom_recette']) : '—' ?>
                            </div>
                        </div>
                    </div>

                    <div class="small-kpi">
                        <div class="small-kpi-icon green">✔</div>
                        <div>
                            <div class="small-kpi-title">Average Calories</div>
                            <div class="small-kpi-value"><?= $moyenneCalories ?></div>
                        </div>
                    </div>

                    <div class="small-kpi">
                        <div class="small-kpi-icon pink">📈</div>
                        <div>
                            <div class="small-kpi-title">Top Ingredient</div>
                            <div class="small-kpi-value">
                                <?= !empty($topIngredient) ? htmlspecialchars($topIngredient['nom_ingredient']) : '—' ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right donut panel -->
            <div class="analytics-panel side-panel">
                <div class="panel-top-row">
                    <div>
                        <h3>Répartition catégories</h3>
                    </div>
                </div>

                <div class="chart-wrapper-donut">
                    <canvas id="donutChart"></canvas>
                </div>

                <div class="donut-legend-grid">
                    <?php for($i=0; $i<count($donutLabels); $i++){ ?>
                        <div class="donut-legend-item">
                            <div class="donut-percent"><?= $donutPercentages[$i] ?>%</div>
                            <div class="donut-label"><?= htmlspecialchars($donutLabels[$i]) ?></div>
                        </div>
                    <?php } ?>

                    <?php if(empty($donutLabels)){ ?>
                        <div class="empty-dashboard-box">Aucune donnée disponible</div>
                    <?php } ?>
                </div>
            </div>
        </div>

        <!-- Bottom mini cards -->
        <div class="mini-cards-grid">
            <div class="mini-analytics-card card-blue">
                <div class="mini-card-header">Protein Status</div>
                <div class="mini-card-value"><?= $totalProteines ?> g</div>
                <div class="mini-card-footer">Total proteins in recipes</div>
            </div>

            <div class="mini-analytics-card card-yellow">
                <div class="mini-card-header">Lipides</div>
                <div class="mini-card-value"><?= $totalLipides ?> g</div>
                <div class="mini-card-footer">Total lipids in recipes</div>
            </div>

            <div class="mini-analytics-card card-peach">
                <div class="mini-card-header">Glucides</div>
                <div class="mini-card-value"><?= $totalGlucides ?> g</div>
                <div class="mini-card-footer">Total carbohydrates in recipes</div>
            </div>

            <div class="mini-analytics-card card-purple">
                <div class="mini-card-header">Average Time</div>
                <div class="mini-card-value"><?= $moyenneTemps ?> min</div>
                <div class="mini-card-footer">Average preparation time</div>
            </div>
        </div>

        <!-- Tables -->
        <div class="dashboard-premium-grid two-cols">
            <div class="premium-panel">
                <div class="premium-panel-header">
                    <h2>Dernières recettes</h2>
                    <span class="premium-badge">5 dernières</span>
                </div>

                <table class="simple-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nom</th>
                            <th>Calories</th>
                            <th>Temps</th>
                            <th>Catégorie</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(!empty($dernieresRecettes)){ ?>
                            <?php foreach($dernieresRecettes as $r){ ?>
                                <tr>
                                    <td><?= $r['id_recette'] ?></td>
                                    <td><?= htmlspecialchars($r['nom_recette']) ?></td>
                                    <td><?= $r['calories'] ?></td>
                                    <td><?= $r['temps_preparation'] ?> min</td>
                                    <td><?= htmlspecialchars($r['categorie']) ?></td>
                                </tr>
                            <?php } ?>
                        <?php } else { ?>
                            <tr>
                                <td colspan="5">Aucune recette trouvée</td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>

            <div class="premium-panel">
                <div class="premium-panel-header">
                    <h2>Derniers ingrédients</h2>
                    <span class="premium-badge">5 derniers</span>
                </div>

                <table class="simple-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nom</th>
                            <th>Type</th>
                            <th>Calories</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(!empty($derniersIngredients)){ ?>
                            <?php foreach($derniersIngredients as $ing){ ?>
                                <tr>
                                    <td><?= $ing['id_ingredient'] ?></td>
                                    <td><?= htmlspecialchars($ing['nom_ingredient']) ?></td>
                                    <td><?= htmlspecialchars($ing['type_ingredient']) ?></td>
                                    <td><?= $ing['calories'] ?></td>
                                </tr>
                            <?php } ?>
                        <?php } else { ?>
                            <tr>
                                <td colspan="4">Aucun ingrédient trouvé</td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</div>

<script>
const lineLabels = <?= json_encode($lineLabels, JSON_UNESCAPED_UNICODE) ?>;
const lineCalories = <?= json_encode($lineCalories) ?>;
const lineProteines = <?= json_encode($lineProteines) ?>;

const donutLabels = <?= json_encode($donutLabels, JSON_UNESCAPED_UNICODE) ?>;
const donutValues = <?= json_encode($donutValues) ?>;

/* ===== LINE CHART ===== */
const ctxLine = document.getElementById('lineChart');
if (ctxLine && lineLabels.length > 0) {
    new Chart(ctxLine, {
        type: 'line',
        data: {
            labels: lineLabels,
            datasets: [
                {
                    label: 'Calories',
                    data: lineCalories,
                    borderColor: '#29a8df',
                    backgroundColor: 'rgba(41,168,223,0.10)',
                    tension: 0.4,
                    fill: false,
                    pointRadius: 4,
                    pointBackgroundColor: '#29a8df'
                },
                {
                    label: 'Protéines',
                    data: lineProteines,
                    borderColor: '#e8b35d',
                    backgroundColor: 'rgba(232,179,93,0.10)',
                    tension: 0.4,
                    fill: false,
                    pointRadius: 4,
                    pointBackgroundColor: '#e8b35d'
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'top',
                    align: 'end',
                    labels: {
                        usePointStyle: true,
                        boxWidth: 8
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    grid: {
                        color: '#edf2f7'
                    }
                },
                x: {
                    grid: {
                        display: false
                    }
                }
            }
        }
    });
}

/* ===== DONUT CHART ===== */
const ctxDonut = document.getElementById('donutChart');
if (ctxDonut && donutLabels.length > 0) {
    new Chart(ctxDonut, {
        type: 'doughnut',
        data: {
            labels: donutLabels,
            datasets: [{
                data: donutValues,
                backgroundColor: ['#2f9cf4', '#ff6a13', '#f9c300', '#8b5cf6', '#10b981', '#ef4444'],
                borderWidth: 0
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            cutout: '68%',
            plugins: {
                legend: {
                    display: false
                }
            }
        }
    });
}
</script>

</body>
</html>