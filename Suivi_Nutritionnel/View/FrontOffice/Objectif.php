<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SmartPlate - Mes Objectifs</title>
    <link rel="stylesheet" href="templates/template.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
</head>
   <?php
    include_once '../../Controller/ObjectifController.php';
    $liste = Objectif::liste(); 
    
    // 1. On récupère le premier objectif de la liste s'il existe
    $objectifActuel = !empty($liste) ? $liste[0] : null;

    // ==========================================
    // 2. CALCUL DES CIBLES NUTRITIONNELLES
    // ==========================================
    $poidsActuel = 75; // Simulation : à remplacer plus tard par le vrai poids du journal
    $typeObjectif = $objectifActuel ? $objectifActuel['type_objectif'] : 'maintien';
    $calories = 2000; // Valeur par défaut

    // Calcul des calories selon l'objectif
    if ($typeObjectif == 'perte_poids') {
        $calories = $poidsActuel * 24;
    } elseif ($typeObjectif == 'prise_masse') {
        $calories = $poidsActuel * 35;
    } else {
        $calories = $poidsActuel * 30; // maintien
    }

    // Calcul des macronutriments (en grammes)
    // 1g Glucide = 4 kcal | 1g Protéine = 4 kcal | 1g Lipide = 9 kcal
    $glucides = round(($calories * 0.50) / 4);
    $proteines = round(($calories * 0.30) / 4);
    $lipides = round(($calories * 0.20) / 9);

    // ==========================================
    // 3. CALCUL POUR LA PROGRESSION DU POIDS
    // ==========================================
    $poidsCible = $objectifActuel ? $objectifActuel['poids_cible'] : 0;
    
    // On calcule la différence absolue (pour éviter un chiffre négatif)
    $diffPoids = abs($poidsActuel - $poidsCible);

    // Note pour plus tard : Pour avoir une vraie barre de progression en %, 
    // il faudrait enregistrer le "poids de départ" dans la base de données.
    // Pour l'instant on va simuler la barre à 50%.
    $pourcentageProgression = 50;
?>

<span class="weight-value">
</span>
<body>
    
    <aside class="sidebar">
    <div class="sidebar-logo">
        <img src="C:\Youssef\2A\ProjetWeb\SmartPlate\Logo\logo.png" alt="Avatar" height="80%" width="50%">
        <h2>SmartPlate</h2>
    </div>

    <nav class="sidebar-nav">
        <span class="nav-section-title">Menu Principal</span>
        
        <a href="Journal.html" class="nav-item">
            <span class="icon">🍽️</span>
            <span>Journal Alimentaire</span>
        </a>
        
        <a href="Objectif.html" class="nav-item">
            <span class="icon">🎯</span>
            <span>Mes Objectifs</span>
        </a>
        
        <a href="Progression.html" class="nav-item">
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
        <h1>Mes Objectifs</h1>
        <span class="date-badge">Mis à jour le 15 Mars</span>
    </header>

    <div class="journal-grid">
        
       <div class="card health-card">
    <div class="card-header">
        <h2>Progression Poids</h2>
        <span class="badge white">
            <?php 
                if ($typeObjectif == 'perte_poids') echo 'Perte de poids';
                elseif ($typeObjectif == 'prise_masse') echo 'Prise de masse';
                else echo 'Maintien';
            ?>
        </span>
    </div>
    <div class="health-content">
        <div class="weight-display">
            <div class="weight-item">
                <span class="weight-label">Actuel</span>
                <span class="weight-value"><?php echo $poidsActuel; ?> kg</span>
            </div>
            <div class="weight-divider">➔</div>
            <div class="weight-item">
                <span class="weight-label">Cible</span>
                <span class="weight-value"><?php echo $poidsCible; ?> kg</span>
            </div>
        </div>
        
        <div class="progress-bar-container mt-2">
            <div class="progress-bar" style="width: <?php echo $pourcentageProgression; ?>%;"></div>
        </div>
        
        <p class="encouragement">Encore <?php echo $diffPoids; ?> kg pour atteindre ton but, Youssef !</p>
    </div>
</div>

        <div class="card progress-card">
    <div class="card-header">
        <h2>Cibles Journalières</h2>
        <span class="badge yellow"><?php echo $calories; ?> kcal/jour</span>
    </div>
    <div class="progress-content">
        <div class="macros-summary full-width">
            <div class="macro-item">
                <div class="macro-title"><span class="dot blue"></span> Glucides (50%)</div>
                <span class="macro-target"><?php echo $glucides; ?>g</span>
            </div>
            <div class="macro-item">
                <div class="macro-title"><span class="dot yellow"></span> Protéines (30%)</div>
                <span class="macro-target"><?php echo $proteines; ?>g</span>
            </div>
            <div class="macro-item">
                <div class="macro-title"><span class="dot green"></span> Lipides (20%)</div>
                <span class="macro-target"><?php echo $lipides; ?>g</span>
            </div>
        </div>
    </div>
</div>

        <div class="form-section">
            <div class="card">
                <div class="card-header">
                    <h2>Gérer mon objectif</h2>
                </div>
                
               <form action="../../Controller/ObjectifController.php?action=add" method="POST" class="modern-form">
                    
                    <input type="hidden" name="id_objectif" value="">

                    <div class="form-row">
                       <div class="form-group">
    <label for="type">Type d'objectif</label>
    <select id="type" name="type" class="form-control">
        <?php $type = $objectifActuel ? $objectifActuel['type_objectif'] : ''; ?>
        <option value="perte_poids" <?php if($type == 'perte_poids') echo 'selected'; ?>>Perte de poids</option>
        <option value="maintien" <?php if($type == 'maintien') echo 'selected'; ?>>Maintien</option>
        <option value="prise_masse" <?php if($type == 'prise_masse') echo 'selected'; ?>>Prise de masse</option>
    </select>
</div>

                        <div class="form-group">
                            <label for="statut">Statut actuel</label>
                            <select id="statut" name="statut" class="form-control">
                                <option value="en_cours">En cours</option>
                                <option value="atteint">Atteint</option>
                                <option value="abandonne">Abandonné</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-row">
                       <div class="form-group">
                            <label for="poids_cible">Poids cible (kg)</label>
                            <input type="number" id="poids_cible" name="poids_cible" class="form-control" 
                            value="<?php echo $objectifActuel ? $objectifActuel['poids_cible'] : ''; ?>">
</div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
    <label for="Date_Debut">Date de début</label>
    <input type="date" id="Date_Debut" name="Date_Debut" class="form-control"
           value="<?php echo $objectifActuel ? $objectifActuel['date_debut'] : ''; ?>">
</div>

<div class="form-group">
    <label for="Date_Fin">Date de fin (prévue)</label>
    <input type="date" id="Date_Fin" name="Date_Fin" class="form-control"
           value="<?php echo $objectifActuel ? $objectifActuel['date_fin'] : ''; ?>">
</div>
                    </div>

                    <div class="form-actions">
                        <a href="../../Controller/ObjectifController.php?action=delete&id=<?php echo $liste[0]['id_objectif'] ?? 0; ?>" class="btn-danger" onclick="return confirm('Es-tu sûr de vouloir supprimer cet objectif ?');">
    Supprimer mon objectif
</a>
                        </a>
                        
                        <div class="right-actions">
                            <button type="reset" class="btn-secondary">Annuler</button>
                            <button type="submit" class="btn-main">Enregistrer l'objectif</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

                </form>
            </div>
        </div>

    </div>
</div>
        <script src="JavaScript.js"></script>
</body>
</html>