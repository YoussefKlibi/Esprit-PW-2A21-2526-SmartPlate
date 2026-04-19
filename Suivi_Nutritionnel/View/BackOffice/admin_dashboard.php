<?php
require_once __DIR__ . '/../../config.php';

$db = Config::getConnexion();

$repas7j = 0;
$repas7jPrev = 0;
$trendPct = null;
try {
    $sql7 = "SELECT COUNT(*) AS c
             FROM repas r
             INNER JOIN journal_alimentaire j ON j.id_journal = r.id_journal
             WHERE j.date_journal >= DATE_SUB(CURDATE(), INTERVAL 6 DAY)";
    $repas7j = (int)$db->query($sql7)->fetch()['c'];

    $sqlPrev = "SELECT COUNT(*) AS c
                FROM repas r
                INNER JOIN journal_alimentaire j ON j.id_journal = r.id_journal
                WHERE j.date_journal >= DATE_SUB(CURDATE(), INTERVAL 13 DAY)
                  AND j.date_journal <  DATE_SUB(CURDATE(), INTERVAL 6 DAY)";
    $repas7jPrev = (int)$db->query($sqlPrev)->fetch()['c'];

    if ($repas7jPrev > 0) {
        $trendPct = (int)round((($repas7j - $repas7jPrev) / $repas7jPrev) * 100);
    } elseif ($repas7j > 0) {
        $trendPct = 100;
    } else {
        $trendPct = 0;
    }
} catch (Exception $e) {
    $trendPct = 0;
}

$objectifsAtteints = 0;
try {
    $sqlObj = "SELECT COUNT(*) AS c FROM objectif WHERE LOWER(statut) = 'atteint'";
    $objectifsAtteints = (int)$db->query($sqlObj)->fetch()['c'];
} catch (Exception $e) {
}

$journauxAnomalies = 0;
try {
    $sqlAnom = "SELECT COUNT(*) AS c FROM (
                    SELECT j.id_journal
                    FROM journal_alimentaire j
                    LEFT JOIN repas r ON r.id_journal = j.id_journal
                    GROUP BY j.id_journal
                    HAVING COUNT(r.id_repas) = 0 OR COALESCE(SUM(r.nbre_calories),0) >= 4500
               ) x";
    $journauxAnomalies = (int)$db->query($sqlAnom)->fetch()['c'];
} catch (Exception $e) {
}

$journalCounts = [];
try {
    $sqlAct = "SELECT j.date_journal AS d, COUNT(*) AS c
               FROM journal_alimentaire j
               WHERE j.date_journal >= DATE_SUB(CURDATE(), INTERVAL 4 DAY)
               GROUP BY j.date_journal
               ORDER BY j.date_journal ASC";
    $rows = $db->query($sqlAct)->fetchAll();
    for ($i = 4; $i >= 0; $i--) {
        $day = (new DateTime())->sub(new DateInterval('P' . $i . 'D'))->format('Y-m-d');
        $journalCounts[$day] = 0;
    }
    foreach ($rows as $r) {
        $journalCounts[$r['d']] = (int)$r['c'];
    }
} catch (Exception $e) {
    for ($i = 4; $i >= 0; $i--) {
        $day = (new DateTime())->sub(new DateInterval('P' . $i . 'D'))->format('Y-m-d');
        $journalCounts[$day] = 0;
    }
}

$maxJ = max($journalCounts ?: [1]);
if ($maxJ <= 0) {
    $maxJ = 1;
}

$alertes = [];
try {
    $sqlRepasSuspect = "SELECT
            'Repas Suspect' AS type_alerte,
            CONCAT('ID Repas #', r.id_repas, ': ', COALESCE(r.nbre_calories,0), ' kcal saisis en une fois.') AS description,
            CONCAT(j.date_journal, ' ', COALESCE(r.heure_repas, '00:00:00')) AS dt,
            r.id_repas AS entity_id
        FROM repas r
        INNER JOIN journal_alimentaire j ON j.id_journal = r.id_journal
        WHERE COALESCE(r.nbre_calories,0) >= 2500
        ORDER BY j.date_journal DESC, r.heure_repas DESC
        LIMIT 3";
    $repasAlerts = $db->query($sqlRepasSuspect)->fetchAll();

    $sqlObjIrr = "SELECT
            'Objectif Irréaliste' AS type_alerte,
            CONCAT('ID Objectif #', o.id_objectif, ': Poids cible de ', o.poids_cible, ' kg demandé.') AS description,
            CONCAT(COALESCE(o.date_debut, CURDATE()), ' 00:00:00') AS dt,
            o.id_objectif AS entity_id
        FROM objectif o
        WHERE (o.poids_cible IS NOT NULL AND (o.poids_cible < 35 OR o.poids_cible > 250))
        ORDER BY o.id_objectif DESC
        LIMIT 3";
    $objAlerts = $db->query($sqlObjIrr)->fetchAll();

    $sqlJournalVide = "SELECT
            'Journal Vide' AS type_alerte,
            CONCAT('Journal ID #', j.id_journal, ' validé sans aucun repas.') AS description,
            CONCAT(j.date_journal, ' 00:00:00') AS dt,
            j.id_journal AS entity_id
        FROM journal_alimentaire j
        LEFT JOIN repas r ON r.id_journal = j.id_journal
        GROUP BY j.id_journal
        HAVING COUNT(r.id_repas) = 0
        ORDER BY j.date_journal DESC
        LIMIT 3";
    $journalAlerts = $db->query($sqlJournalVide)->fetchAll();

    $alertes = array_merge($repasAlerts ?: [], $objAlerts ?: [], $journalAlerts ?: []);
    usort($alertes, function ($a, $b) {
        return strcmp($b['dt'], $a['dt']);
    });
    $alertes = array_slice($alertes, 0, 5);
} catch (Exception $e) {
    $alertes = [];
}

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
</head>
<body>

    <aside class="admin-sidebar">
        <div class="sidebar-header">
            <img src="../assets/logo.png" alt="Logo" height="150" width="150">
            <h4>SmartPlate Admin</h4>
        </div>
        <div class="sidebar-menu">
            <div class="menu-category">Menu Principal</div>
            <a href="admin_dashboard.php" class="menu-item active">📊 Dashboard Analytics</a>
            <a href="admin_objectifs.php" class="menu-item">🎯 Modération Objectifs</a>
            <a href="admin_journaux.php" class="menu-item">🍽️ Journaux Utilisateurs</a>
        </div>
    </aside>

    <main class="main-content">

        <header class="topbar">
            <div class="search-bar"></div>
            <div class="admin-profile">
                <span>Admin</span>
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
        </div>
    </main>

</body>
</html>
