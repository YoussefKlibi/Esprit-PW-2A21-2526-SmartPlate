<?php
require_once "../../config.php";
require_once __DIR__ . "/../../models/PlanificateurMetier.php";

$profil = [
    'objectif' => 'perte_poids',
    'temps_max' => 20,
    'calories_max' => 450
];

$resultatPlanning = null;
$messagePlanning = "";

if (isset($_POST['generer_planning'])) {
    $profil['objectif'] = $_POST['objectif'] ?? 'perte_poids';
    $profil['temps_max'] = (int)($_POST['temps_max'] ?? 20);
    $profil['calories_max'] = (int)($_POST['calories_max'] ?? 450);
    $nbJours = (int)($_POST['nb_jours'] ?? 7);

    $resultatPlanning = PlanificateurMetier::genererPlanning($pdo, $profil, $nbJours);

    if (!empty($resultatPlanning['planning'])) {
        PlanificateurMetier::sauvegarderPlanning($pdo, $profil, $nbJours, $resultatPlanning);
        $messagePlanning = "Planning généré avec succès.";
    } else {
        $messagePlanning = "Aucune recette ne correspond à votre profil.";
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Planning hebdomadaire</title>
    <link rel="stylesheet" href="templates/stylefront.css">
</head>
<body>

<aside class="sidebar">
    <div class="sidebar-logo">
        <div class="logo-icon">🥗</div>
        <h2>SmartPlate</h2>
    </div>

    <nav class="sidebar-nav">
        <div class="nav-section-title">Menu</div>
        <a href="frontoffice.php" class="nav-item"><span class="icon">🏠</span>Dashboard</a>
        <a href="planning.php" class="nav-item active"><span class="icon">📅</span>Planning</a>
    </nav>
</aside>

<main class="dashboard">
    <div class="dashboard-header">
        <div>
            <h1>Planificateur hebdomadaire</h1>
            <p>Générez automatiquement votre menu et votre liste de courses.</p>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h2>Paramètres du planning</h2>
            <span class="badge blue-light">Métier avancé</span>
        </div>

        <?php if ($messagePlanning !== "") { ?>
            <div class="front-alert"><?= htmlspecialchars($messagePlanning) ?></div>
        <?php } ?>

        <form method="POST" class="modern-form">
            <div class="form-row">
                <div class="form-group">
                    <label for="objectif">Objectif</label>
                    <select name="objectif" id="objectif" class="form-control">
                        <option value="perte_poids">Perte de poids</option>
                        <option value="maintien">Maintien</option>
                        <option value="prise_masse">Prise de masse</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="nb_jours">Nombre de jours</label>
                    <input type="number" name="nb_jours" id="nb_jours" class="form-control" value="7" min="1" max="7">
                </div>

                <div class="form-group">
                    <label for="temps_max">Temps max (min)</label>
                    <input type="number" name="temps_max" id="temps_max" class="form-control" value="20">
                </div>

                <div class="form-group">
                    <label for="calories_max">Calories max</label>
                    <input type="number" name="calories_max" id="calories_max" class="form-control" value="450">
                </div>
            </div>

            <div class="form-actions">
                <div></div>
                <div class="right-actions">
                    <button type="submit" name="generer_planning" class="btn-main">Générer le planning</button>
                </div>
            </div>
        </form>
    </div>

    <?php if ($resultatPlanning && !empty($resultatPlanning['planning'])) { ?>

        <div class="card mt-2">
            <div class="card-header">
                <h2>Planning repas</h2>
                <span class="badge green-light"><?= count($resultatPlanning['planning']) ?> jours</span>
            </div>

            <?php foreach ($resultatPlanning['planning'] as $item) { ?>
                <div class="meal-card">
                    <div class="meal-image">
                        <img src="../../images/<?= htmlspecialchars($item['recette']['image'] ?: 'default.jpg') ?>" alt="recette">
                    </div>

                    <div class="meal-info">
                        <div class="meal-tags">
                            <span class="badge green-light"><?= htmlspecialchars($item['jour']) ?></span>
                            <span class="time-info"><?= (int)$item['recette']['temps_preparation'] ?> min</span>
                        </div>

                        <h3><?= htmlspecialchars($item['recette']['nom_recette']) ?></h3>
                        <p><?= htmlspecialchars($item['recette']['description']) ?></p>
                    </div>

                    <div class="meal-stats">
                        <div class="stat-col">
                            <div class="stat-val">🔥 <?= round($item['recette']['calories']) ?> kcal</div>
                            <div class="stat-val">P <?= round($item['recette']['proteines']) ?> g</div>
                        </div>
                    </div>
                </div>
            <?php } ?>
        </div>

        <div class="card mt-2">
            <div class="card-header">
                <h2>Liste de courses générée</h2>
                <span class="badge yellow">Budget estimé : <?= number_format($resultatPlanning['budget_total'], 2) ?> DT</span>
            </div>

            <?php foreach ($resultatPlanning['courses'] as $course) { ?>
                <div class="meal-card">
                    <div class="meal-info">
                        <h3><?= htmlspecialchars($course['nom_ingredient']) ?></h3>
                        <p>
                            Quantité totale : <?= number_format($course['quantite_totale'], 2) . ' ' . htmlspecialchars($course['unite']) ?>
                        </p>
                    </div>

                    <div class="meal-stats">
                        <div class="stat-col">
                            <div class="stat-val">Prix unitaire : <?= number_format($course['prix_unitaire'], 2) ?> DT</div>
                            <div class="stat-val">Coût : <?= number_format($course['cout_total'], 2) ?> DT</div>
                        </div>
                    </div>
                </div>
            <?php } ?>
        </div>

    <?php } ?>
</main>

</body>
</html>