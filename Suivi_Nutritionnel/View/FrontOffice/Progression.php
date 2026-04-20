<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SmartPlate - Ma Progression</title>
    <link rel="stylesheet" href="templates/template.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
</head>
<?php
    // Import du contrôleur pour accéder aux méthodes de la base de données
    include_once '../../Controller/ObjectifController.php';
    include_once '../../Model/Journal_Class.php';

    // Simulation de l'utilisateur connecté
    $id_utilisateur_connecte = 1; 

    // URL absolue vers le contrôleur Chatbot (évite les soucis de chemins relatifs)
    $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $phpSelf = $_SERVER['PHP_SELF'] ?? '';
    $projectBase = '';
    $posView = strpos($phpSelf, '/View/');
    if ($posView !== false) {
        $projectBase = substr($phpSelf, 0, $posView);
    } else {
        $projectBase = dirname($phpSelf, 2);
    }
    $chatbotControllerUrl = $scheme . '://' . $host . rtrim($projectBase, '/') . '/Controller/ChatbotController.php';

    // Récupération de l'objectif actif pour l'utilisateur (utilisateur simulé = 1)
    $id_utilisateur_connecte = 1;
    $objectifActuel = Objectif::getActif($id_utilisateur_connecte);

    // Préparer les valeurs par défaut au cas où il n'y aurait pas d'objectif actif
    $debutAff = '—';
    $finAff = '—';
    $joursPasses = 0;
    $joursRestants = 0;
    $pourcentage = 0;

    // Poids (départ / actuel / cible) + % avancement réel
    $poidsDepart = null;
    $poidsActuel = null;
    $poidsCible = null;
    $pourcentageAvancement = 0;

    // Série de poids pour le graphique (labels = dates, data = poids)
    $weightLabels = [];
    $weightSeries = [];

    // Petit helper pour formater en français (abréviations)
    function formatDateFrShort($dateStr) {
        $mois = [1=> 'Jan', 'Fév','Mar','Avr','Mai','Juin','Juil','Aoû','Sep','Oct','Nov','Déc'];
        try {
            $d = new DateTime($dateStr);
            $num = (int)$d->format('n');
            return $d->format('d') . ' ' . $mois[$num];
        } catch (Exception $e) {
            return $dateStr;
        }
    }

    if ($objectifActuel) {
        $dateDebut = $objectifActuel['date_debut'];
        $dateFin = $objectifActuel['date_fin'];
        $poidsCible = isset($objectifActuel['poids_cible']) ? (float)$objectifActuel['poids_cible'] : null;

        // Format affichage
        $debutAff = formatDateFrShort($dateDebut);
        $finAff = formatDateFrShort($dateFin);

        $dDebut = new DateTime($dateDebut);
        $dFin = new DateTime($dateFin);
        $aujourdhui = new DateTime();

        // Total de jours (au moins 1 pour éviter division par zéro)
        $intervalTotal = $dDebut->diff($dFin)->days;
        $totalJours = max(1, $intervalTotal);

        // Jours passés : si aujourd'hui < début => 0 ; si aujourd'hui > fin => total
        if ($aujourdhui < $dDebut) {
            $joursPasses = 0;
        } elseif ($aujourdhui > $dFin) {
            $joursPasses = $totalJours;
        } else {
            $joursPasses = $dDebut->diff($aujourdhui)->days;
        }

        $joursRestants = max(0, $totalJours - $joursPasses);
        $pourcentage = (int) round(($joursPasses / $totalJours) * 100);
        $pourcentage = min(100, max(0, $pourcentage));

                // Récupérer le 1er poids (départ) et le dernier poids (actuel) via le modèle
                $firstLast = Journal::getFirstLastWeightInRange($id_utilisateur_connecte, $dateDebut, $dateFin);
                if (!empty($firstLast['first'])) $poidsDepart = (float)$firstLast['first'];
                if (!empty($firstLast['last'])) $poidsActuel = (float)$firstLast['last'];

        // Calcul du % d'avancement réel basé sur (départ -> cible) et le poids actuel
        if ($poidsDepart !== null && $poidsActuel !== null && $poidsCible !== null) {
            $den = $poidsCible - $poidsDepart;
            $num = $poidsActuel - $poidsDepart;

            if (abs($den) < 0.000001) {
                $pourcentageAvancement = (abs($poidsActuel - $poidsCible) < 0.000001) ? 100 : 0;
            } else {
                $raw = $num / $den; // 0 au départ, 1 à la cible (prise ou perte)
                $raw = max(0.0, min(1.0, $raw));
                $pourcentageAvancement = (int)round($raw * 100);
            }
        } else {
            $pourcentageAvancement = 0;
        }

        // Données du graphique : poids saisis (priorité sur la période de l'objectif)
        $rows = Journal::getWeightsSeries($id_utilisateur_connecte, $dateDebut, $dateFin);
        if ($rows && count($rows) > 0) {
            foreach ($rows as $r) {
                if (!isset($r['date_journal']) || !isset($r['poids_actuel'])) continue;
                $dateStr = (string)$r['date_journal'];
                $val = $r['poids_actuel'];
                if ($val === '' || $val === null) continue;

                // Label court "dd Mon" comme le reste de la page
                $weightLabels[] = formatDateFrShort($dateStr);
                $weightSeries[] = (float)$val;
            }
        }
    }
?>
<body>
    <aside class="sidebar">
    <div class="sidebar-logo">
    <img src="..\assets\logo.png" alt="Logo" height="150" width="150">
    <h2>SmartPlate</h2>
    </div>

    <nav class="sidebar-nav">
        <span class="nav-section-title">Menu Principal</span>
        
        <a href="Journal.php" class="nav-item">
            <span class="icon">🍽️</span>
            <span>Journal Alimentaire</span>
        </a>
        
        <a href="Objectif.php" class="nav-item">
            <span class="icon">🎯</span>
            <span>Mon Objectif</span>
        </a>
        
        <a href="Progression.php" class="nav-item">
            <span class="icon">📈</span>
            <span>Ma Progression</span>
        </a>
    </nav>

    <div class="sidebar-footer">
        <div class="user-badge">
            <img src="https://ui-avatars.com/api/?name=Youssef&background=d4f283&color=1a1a1a&rounded=true" alt="Avatar">
            <div class="user-info">
                <span class="user-name">Youssef</span>
                <span class="user-status">Connecté</span>
            </div>
        </div>
    </div>
</aside>

<div class="dashboard">
    
    <header class="dashboard-header">
        <h1>Ma Progression</h1>
        <span class="badge green-light">Statut : En cours</span> </header>

    <div class="journal-grid">
        
        <div class="card progress-card">
            <div class="card-header">
                <h2>Avancement Global</h2>
                <select class="form-control" style="width: auto; padding: 0.3rem; border-radius: 8px;">
                    <option>Cet Objectif</option>
                    <option>Ce Mois</option>
                </select>
            </div>
            <div class="progress-content">
                <div class="circle-chart" style="background: conic-gradient(var(--green-light) 0% <?php echo (int)$pourcentageAvancement; ?>%, #eee <?php echo (int)$pourcentageAvancement; ?>% 100%);">
                    <div class="circle-inner">
                        <span class="percentage"><?php echo (int)$pourcentageAvancement; ?>%</span>
                        <span class="label">Complété</span>
                    </div>
                </div>
                <div class="macros-summary">
                    <div class="macro-item">
                        <span class="dot blue"></span> Poids de départ <span><?php echo $poidsDepart !== null ? rtrim(rtrim(number_format($poidsDepart, 1, '.', ''), '0'), '.') . ' kg' : '—'; ?></span>
                    </div>
                    <div class="macro-item">
                        <span class="dot yellow"></span> Poids actuel <span><?php echo $poidsActuel !== null ? rtrim(rtrim(number_format($poidsActuel, 1, '.', ''), '0'), '.') . ' kg' : '—'; ?></span>
                    </div>
                    <div class="macro-item">
                        <span class="dot green"></span> Poids cible <span><?php echo $poidsCible !== null ? rtrim(rtrim(number_format((float)$poidsCible, 1, '.', ''), '0'), '.') . ' kg' : '—'; ?></span> </div>
                </div>
            </div>
        </div>
        

        <div class="card health-card">
            <div class="card-header">
                <h2>Temps Écoulé</h2>
                <span class="badge white"><?php echo $joursRestants; ?> Jours restants</span>
            </div>
            <div class="health-content">
                
                <div class="timeline-container">
                    <div class="timeline-dates">
                        <span>Début : <?php echo $debutAff; ?></span>
                        <span>Aujourd'hui</span>
                        <span>Fin : <?php echo $finAff; ?></span>
                    </div>

                    <div class="progress-bar-container mt-2">
                        <div class="progress-bar" style="width: <?php echo $pourcentage; ?>%;"></div>
                    </div>
                </div>

                <div class="stats-row mt-2">
                    <div class="stat-box">
                        <span class="stat-number"><?php echo $joursPasses; ?></span>
                        <span class="stat-label">Jours passés</span>
                    </div>
                    <div class="stat-box">
                        <span class="stat-number"><?php echo $joursRestants; ?></span>
                        <span class="stat-label">Jours restants</span>
                    </div>
                </div>

            </div>
        </div>

        <div class="card full-width-card">
            <div class="card-header">
                <h2>Évolution du Poids</h2>
            </div>
            <div class="chart-container">
                <div class="chart-placeholder" style="height: 260px;">
                    <?php if (count($weightSeries) >= 2): ?>
                        <canvas id="weightChart" aria-label="Courbe d'évolution du poids" role="img"></canvas>
                    <?php elseif (count($weightSeries) === 1): ?>
                        <div style="display:flex;align-items:center;justify-content:center;height:100%;color:var(--text-gray);font-style:italic;">
                            Ajoute au moins 2 journaux avec un poids pour afficher la courbe.
                        </div>
                    <?php else: ?>
                        <div style="display:flex;align-items:center;justify-content:center;height:100%;color:var(--text-gray);font-style:italic;">
                            Aucun poids trouvé dans tes journaux pour le moment.
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="card full-width-card">
            <div class="card-header">
                <h2>Assistant IA</h2>
                <span class="badge blue-light">Chat</span>
            </div>

            <div class="sp-chat">
                <div class="sp-chat-messages" id="spChatMessages" aria-live="polite"></div>

                <form class="sp-chat-composer" id="spChatForm" autocomplete="off">
                    <input
                        id="spChatInput"
                        class="form-control sp-chat-input"
                        type="text"
                        placeholder="Écris ta question… (ex: Que puis-je manger ce soir pour atteindre mon objectif ?)"
                        aria-label="Votre question"
                    />
                    <button class="btn-main sp-chat-send" id="spChatSend" type="submit">Envoyer</button>
                </form>

                <div class="sp-chat-hint">
                    Astuce : tu peux demander un menu, une estimation de calories ou des conseils pour ton objectif.
                </div>
            </div>
        </div>

    </div>
</div>
<script src="JavaScript_Front.js"></script>
<!-- Chart.js (courbe évolution du poids) -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
    (function() {
        var el = document.getElementById('weightChart');
        if (!el) return;

        var labels = <?php echo json_encode($weightLabels, JSON_UNESCAPED_UNICODE); ?>;
        var data = <?php echo json_encode($weightSeries, JSON_UNESCAPED_UNICODE); ?>;
        if (!Array.isArray(labels) || !Array.isArray(data) || labels.length < 2 || data.length < 2) return;

        // léger padding visuel pour l'axe Y
        var min = Math.min.apply(null, data);
        var max = Math.max.apply(null, data);
        var pad = Math.max(0.5, (max - min) * 0.15);

        new Chart(el.getContext('2d'), {
            type: 'line',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Poids (kg)',
                    data: data,
                    borderColor: '#111827',
                    backgroundColor: 'rgba(210, 255, 118, 0.25)',
                    pointBackgroundColor: '#d4f283',
                    pointBorderColor: '#111827',
                    pointRadius: 4,
                    pointHoverRadius: 5,
                    borderWidth: 3,
                    tension: 0.28,
                    fill: true
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            label: function(ctx) {
                                var v = ctx.parsed && typeof ctx.parsed.y === 'number' ? ctx.parsed.y : ctx.raw;
                                return ' ' + String(v).replace('.', ',') + ' kg';
                            }
                        }
                    }
                },
                scales: {
                    x: {
                        grid: { display: false },
                        ticks: { maxRotation: 0, autoSkip: true }
                    },
                    y: {
                        suggestedMin: min - pad,
                        suggestedMax: max + pad,
                        ticks: {
                            callback: function(value) { return String(value).replace('.', ',') + ' kg'; }
                        },
                        grid: { color: 'rgba(148, 163, 184, 0.35)' }
                    }
                }
            }
        });
    })();
</script>

</body>
</html>