<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SmartPlate - Ma Progression</title>
    <link rel="stylesheet" href="templates/template.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
</head>
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
                <span class="badge white">14 Jours restants</span>
            </div>
            <div class="health-content">
                
                <div class="timeline-container">
                    <div class="timeline-dates">
                        <span>Début : 01 Fév</span> <span>Aujourd'hui</span>
                        <span>Fin : 30 Avr</span> </div>
                    
                    <div class="progress-bar-container mt-2">
                        <div class="progress-bar" style="width: 70%;"></div>
                    </div>
                </div>

                <div class="stats-row mt-2">
                    <div class="stat-box">
                        <span class="stat-number">45</span>
                        <span class="stat-label">Jours passés</span>
                    </div>
                    <div class="stat-box">
                        <span class="stat-number">14</span>
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