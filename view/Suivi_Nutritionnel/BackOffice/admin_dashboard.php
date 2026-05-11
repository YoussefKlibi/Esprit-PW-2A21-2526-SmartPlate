<?php
require_once __DIR__ . '/../../../config.php';
require_once __DIR__ . '/../../../Models/Suivi_Nutritionnel/Objectif_Class.php';
require_once __DIR__ . '/../../../Models/Suivi_Nutritionnel/Repas_Class.php';
require_once __DIR__ . '/../../../Models/Suivi_Nutritionnel/Journal_Class.php';

// Récupération des données via les modèles (séparation MVC)
$statsObjectifs = Objectif::getStatsParStatut();
$repas7j        = Repas::countRepasRecents();
$repas7jPrev    = Repas::countRepasPrecedents();
$calData        = Repas::getCaloriesSeptDerniersJours();

// Calcul de la tendance (présentation)
$trendPct = 0;
if ($repas7jPrev > 0) {
    $trendPct = (int)round((($repas7j - $repas7jPrev) / $repas7jPrev) * 100);
} elseif ($repas7j > 0) {
    $trendPct = 100;
}

// Activité des 5 derniers jours (Journal)
$journalCounts = Journal::getActivityLastDays();

$maxJ = max($journalCounts ?: [1]);
if ($maxJ <= 0) {
    $maxJ = 1;
}

// Statistiques rapides
$objectifsAtteints = $statsObjectifs['atteint'] ?? 0;
$journauxAnomalies = Journal::countAnomalies();

// Alertes (fusion des alertes fournies par les modèles)
$repasAlerts = Repas::getSuspectAlerts();
$objAlerts = Objectif::getUnrealisticAlerts();
$journalAlerts = Journal::getEmptyJournalAlerts();
$alertes = array_merge($repasAlerts ?: [], $objAlerts ?: [], $journalAlerts ?: []);
usort($alertes, function ($a, $b) {
    return strcmp($b['dt'], $a['dt']);
});
$alertes = array_slice($alertes, 0, 5);

function sp_format_nombre($n) {
    return number_format((int)$n, 0, ',', ' ');
}

function sp_format_temps_alerte($dt) {
    try {
        $d = new DateTime($dt);
        $now = new DateTime();
        $diff = $now->getTimestamp() - $d->getTimestamp();
        if ($diff < 0) {
            $diff = 0;
        }
        if ($diff < 60) {
            return "À l'instant";
        }
        if ($diff < 3600) {
            return "Il y a " . (int)floor($diff / 60) . " minutes";
        }
        if ($diff < 86400) {
            return "Aujourd'hui, " . $d->format('H:i');
        }
        return $d->format('d/m/Y, H:i');
    } catch (Exception $e) {
        return '';
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SmartPlate - Admin Dashboard</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="templates/Template.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>

    <aside class="admin-sidebar">
        <div class="sidebar-header">
            <span>
                <img src="../../User/xpdf/logo.jpg" alt="SmartPlate Logo" style="width:30px; height:30px; object-fit:cover; border-radius:8px; display:block;">
            </span> SmartPlate
        </div>
        <div class="sidebar-menu">
            <div class="menu-category">Menu Principal</div>
            <a href="../../User/backend/admin_welcome.php" class="menu-item">📊 Vue d'ensemble</a>
            <a href="../../User/backend/user_list.php" class="menu-item">👥 Utilisateurs & Logins</a>
            <a href="#" class="menu-item">📦 Produit</a>

            <div class="nav-dropdown">
                <a href="#" class="menu-item" onclick="toggleSubMenu(event, 'recettesMenu')">
                    🍲 Recettes
                    <span id="arrow-recettesMenu" style="float: right; font-size: 0.8em; margin-top: 4px; transition: transform 0.3s;">▼</span>
                </a>
                <div id="recettesMenu" style="display: none; background: rgba(0,0,0,0.03); border-radius: 8px; margin-bottom: 5px; padding-top: 5px; padding-bottom: 5px;">
                    <a href="../../Recette/backoffice/recettes.php" class="menu-item" style="padding-left: 40px; font-size: 0.9em; margin-bottom: 2px;">🍽 Gestion Recettes</a>
                    <a href="../../Recette/backoffice/ingredients.php" class="menu-item" style="padding-left: 40px; font-size: 0.9em; margin-bottom: 2px;">🥕 Gestion Ingrédients</a>
                    <a href="../../Recette/backoffice/dashboard.php" class="menu-item" style="padding-left: 40px; font-size: 0.9em; margin-bottom: 2px;">📊 Dashboard</a>
                </div>
            </div>

            <div class="nav-dropdown">
                <a href="#" class="menu-item active" onclick="toggleSubMenu(event, 'suiviNutritionnelMenu')">
                    📈 Suivi nutritionnel
                    <span id="arrow-suiviNutritionnelMenu" style="float: right; font-size: 0.8em; margin-top: 4px; transition: transform 0.3s;">▼</span>
                </a>
                <div id="suiviNutritionnelMenu" style="display: block; background: rgba(0,0,0,0.03); border-radius: 8px; margin-bottom: 5px; padding-top: 5px; padding-bottom: 5px;">
                    <a href="admin_dashboard.php" class="menu-item active" style="padding-left: 40px; font-size: 0.9em; margin-bottom: 2px;">📊 Dashboard Analytics</a>
                    <a href="admin_objectifs.php" class="menu-item" style="padding-left: 40px; font-size: 0.9em; margin-bottom: 2px;">🎯 Modération Objectifs</a>
                    <a href="admin_journaux.php" class="menu-item" style="padding-left: 40px; font-size: 0.9em; margin-bottom: 2px;">🍽️ Journaux Utilisateurs</a>
                </div>
            </div>

            <a href="../../Forum/backoffice/admin_forum.php" class="menu-item">💬 Forum</a>
            <a href="../../Reclamation/back/admin_reclamations.php" class="menu-item">📝 Réclamation</a>
        </div>
    </aside>
    <script>
        function toggleSubMenu(event, menuId) {
            event.preventDefault();
            const menu = document.getElementById(menuId);
            const arrow = document.getElementById('arrow-' + menuId);
            if (menu.style.display === 'none' || menu.style.display === '') {
                menu.style.display = 'block';
                if (arrow) arrow.style.transform = 'rotate(180deg)';
            } else {
                menu.style.display = 'none';
                if (arrow) arrow.style.transform = 'rotate(0deg)';
            }
        }
    </script>

    <main class="main-content">

        <header class="topbar">
            <div class="search-bar"></div>
            <div class="admin-profile">
                <span>Youssef</span>
                <img src="https://ui-avatars.com/api/?name=Admin&background=20c997&color=fff" alt="Profile">
            </div>
        </header>

        <div class="dashboard-container">

            <div class="page-header">
                <div>
                    <h1>Module : Suivi Nutritionnel</h1>
                    <p>Aperçu des performances globales des entités Objectifs, Journaux et Repas.</p>
                </div>
            </div>

            <div class="kpi-grid">
                <div class="card kpi-card">
                    <div class="kpi-icon yellow">🍽️</div>
                    <div class="kpi-info">
                        <h3>
                            <?php echo sp_format_nombre($repas7j); ?>
                            <?php if ($trendPct !== null): ?>
                                <span class="trend <?php echo ($trendPct >= 0) ? 'up' : 'down'; ?>">
                                    <?php echo ($trendPct >= 0) ? '↑' : '↓'; ?> <?php echo abs((int)$trendPct); ?>%
                                </span>
                            <?php endif; ?>
                        </h3>
                        <span>Repas enregistrés (7 jours)</span>
                    </div>
                </div>
                <div class="card kpi-card">
                    <div class="kpi-icon green">🎯</div>
                    <div class="kpi-info">
                        <h3><?php echo sp_format_nombre($objectifsAtteints); ?></h3>
                        <span>Objectifs "Atteints"</span>
                    </div>
                </div>
                <div class="card kpi-card">
                    <div class="kpi-icon red">⚠️</div>
                    <div class="kpi-info">
                        <h3><?php echo sp_format_nombre($journauxAnomalies); ?></h3>
                        <span>Journaux avec anomalies</span>
                    </div>
                </div>
            </div>

            <div class="content-grid">

                <div class="card">
                    <div class="card-header">
                        <h2>Activité des Journaux Alimentaires</h2>
                    </div>
                    <div class="chart-mockup" style="height: 250px; display: flex; align-items: flex-end; gap: 10px; padding-top: 20px;">
                        <?php foreach ($journalCounts as $day => $count): ?>
                            <?php
                            $pct = (int)round(($count / $maxJ) * 100);
                            if ($pct < 8) {
                                $pct = 8;
                            }
                            ?>
                            <div
                                title="<?php echo htmlspecialchars(date('d/m', strtotime($day)) . ' : ' . $count . ' journal(x)'); ?>"
                                style="flex: 1; background: #e8f8f5; border-top: 3px solid var(--admin-green); height: <?php echo $pct; ?>%; border-radius: 4px 4px 0 0;"
                            ></div>
                        <?php endforeach; ?>
                    </div>
                </div>
                

                <div class="card">
                    <div class="card-header">
                        <h2>Dernières Alertes (Modération)</h2>
                    </div>
                    <div class="timeline">
                        <?php if (!empty($alertes)): ?>
                            <?php foreach ($alertes as $a): ?>
                                <?php
                                $type = $a['type_alerte'] ?? 'Alerte';
                                $desc = $a['description'] ?? '';
                                $dt = $a['dt'] ?? '';
                                $markerStyle = '';
                                $btnLabel = 'Inspecter';
                                $btnHref = 'admin_journaux.php';
                                if ($type === 'Objectif Irréaliste') {
                                    $markerStyle = ' style="--marker-color: #f1c40f;"';
                                    $btnHref = 'admin_objectifs.php';
                                } elseif ($type === 'Journal Vide') {
                                    $btnLabel = 'Voir journaux';
                                    $btnHref = 'admin_journaux.php';
                                }
                                ?>
                                <div class="timeline-item"<?php echo $markerStyle; ?>>
                                    <strong><?php echo htmlspecialchars($type); ?></strong>
                                    <span class="timeline-time"><?php echo htmlspecialchars(sp_format_temps_alerte((string)$dt)); ?></span>
                                    <p style="color: var(--text-gray); font-size: 0.85rem; margin-top: 5px;"><?php echo htmlspecialchars($desc); ?></p>
                                    <a class="btn-action" href="<?php echo htmlspecialchars($btnHref); ?>" style="display:inline-block; text-decoration:none;"><?php echo htmlspecialchars($btnLabel); ?></a>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="timeline-item">
                                <strong>Aucune alerte</strong>
                                <span class="timeline-time">—</span>
                                <p style="color: var(--text-gray); font-size: 0.85rem; margin-top: 5px;">Aucune anomalie détectée pour le moment.</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

            </div>
            <div class="content-grid" style="margin-top: 20px; grid-template-columns: 1fr;">
                <div class="card" style="display: flex; flex-direction: column; align-items: center;">
                    <div class="card-header" style="width: 100%;">
                        <h2>Répartition des Objectifs</h2>
                    </div>
                    <div style="width: 100%; max-width: 350px; padding: 20px;">
                        <canvas id="objectifsChart" 
                                data-encours="<?php echo $statsObjectifs['en_cours'] ?? 0; ?>"
                                data-atteint="<?php echo $statsObjectifs['atteint'] ?? 0; ?>"
                                data-abandonne="<?php echo $statsObjectifs['abandonne'] ?? 0; ?>">
                        </canvas>
                    </div>
                </div>
            </div>
        </div>
    </main>
<script src="JavaScript.js"></script>
</body>
</html>
