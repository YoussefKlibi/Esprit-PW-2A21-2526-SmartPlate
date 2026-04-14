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

    // Simulation de l'utilisateur connecté
    $id_utilisateur_connecte = 1; 

    // Récupération de l'objectif actif pour l'utilisateur (utilisateur simulé = 1)
    $id_utilisateur_connecte = 1;
    $objectifActuel = Objectif::getActif($id_utilisateur_connecte);

    // Préparer les valeurs par défaut au cas où il n'y aurait pas d'objectif actif
    $debutAff = '—';
    $finAff = '—';
    $joursPasses = 0;
    $joursRestants = 0;
    $pourcentage = 0;

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
    }
?>
<body>
    <aside class="sidebar">
    <div class="sidebar-logo">
        <img src="C:\Youssef\2A\ProjetWeb\SmartPlate\Logo\logo.png" alt="Avatar" height="80%" width="50%">
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
            <span>Mes Objectifs</span>
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
                <div class="circle-chart">
                    <div class="circle-inner">
                        <span class="percentage">65%</span>
                        <span class="label">Complété</span>
                    </div>
                </div>
                <div class="macros-summary">
                    <div class="macro-item">
                        <span class="dot blue"></span> Poids de départ <span>80 kg</span>
                    </div>
                    <div class="macro-item">
                        <span class="dot yellow"></span> Poids actuel <span>76.5 kg</span>
                    </div>
                    <div class="macro-item">
                        <span class="dot green"></span> Poids cible <span>70 kg</span> </div>
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
                <div class="chart-placeholder">
                    <svg width="100%" height="200" viewBox="0 0 500 200" preserveAspectRatio="none">
                        <path d="M0,150 L100,140 L200,160 L300,110 L400,90 L500,70" fill="none" stroke="var(--text-dark)" stroke-width="4" stroke-linecap="round" stroke-linejoin="round"/>
                        <circle cx="100" cy="140" r="5" fill="var(--green-light)" stroke="var(--text-dark)" stroke-width="2"/>
                        <circle cx="200" cy="160" r="5" fill="var(--green-light)" stroke="var(--text-dark)" stroke-width="2"/>
                        <circle cx="300" cy="110" r="5" fill="var(--green-light)" stroke="var(--text-dark)" stroke-width="2"/>
                        <circle cx="400" cy="90" r="5" fill="var(--green-light)" stroke="var(--text-dark)" stroke-width="2"/>
                        <circle cx="500" cy="70" r="5" fill="var(--yellow-light)" stroke="var(--text-dark)" stroke-width="2"/>
                    </svg>
                    <p class="chart-note">Intègre <a href="https://www.chartjs.org/" target="_blank">Chart.js</a> ici pour rendre cette courbe dynamique avec tes données PHP.</p>
                </div>
            </div>
        </div>

    </div>
</div>

</body>
</html>