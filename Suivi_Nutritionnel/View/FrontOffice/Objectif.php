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
    // 1. Inclure le contrôleur (qui inclut déjà la classe Objectif et la Config)
    include_once '../../Controller/ObjectifController.php';

    // 2. Récupérer l'utilisateur connecté (Simulé à 1 pour l'instant)
    $id_utilisateur_connecte = 1; 

    // 3. Récupérer tous les objectifs de la base
    $tousLesObjectifs = Objectif::liste(); 
    

   // 4. LOGIQUE : Extraire l'unique objectif "En cours"
    $objectifActuel = null;

foreach($tousLesObjectifs as $obj) {
    $statutBDD = strtolower(trim($obj['statut']));
    
    if ($obj['id_utilisateur'] == $id_utilisateur_connecte && 
       ($statutBDD == 'en cours' || $statutBDD == 'en_cours')) {
        $objectifActuel = $obj;
        break;
    }
}


    // ==========================================
    // 5. CALCULS NUTRITIONNELS DYNAMIQUES
    // ==========================================
    $calories = 0; $proteines = 0; $glucides = 0; $lipides = 0; $diffPoids = 0;
    if ($objectifActuel) {
    // Valeurs de base
    $poidsActuel = 75; 
    $typeObjectif = $objectifActuel ? $objectifActuel['type_objectif'] : 'maintien';
    $poidsCible = $objectifActuel ? $objectifActuel['poids_cible'] : 0;
    $diffPoids = $objectifActuel ? abs($poidsActuel - $poidsCible) : 0;
    // Initialisation des cibles
    $calories = 0;
    $proteines = 0;
    $glucides = 0;
    $lipides = 0;

    // Formules de calcul selon le type d'objectif
    if ($typeObjectif == 'perte_poids') {
        $calories = $poidsActuel * 22; // Déficit calorique
        $proteines = $poidsActuel * 2.2; // Haute protéine pour garder le muscle
        $lipides = $poidsActuel * 0.8;
    } 
    elseif ($typeObjectif == 'prise_masse') {
        $calories = $poidsActuel * 30; // Surplus calorique
        $proteines = $poidsActuel * 1.8;
        $lipides = $poidsActuel * 1;
    } 
    else { // Maintien
        $calories = $poidsActuel * 25;
        $proteines = $poidsActuel * 1.5;
        $lipides = $poidsActuel * 0.9;
    }

    // Le reste des calories provient des glucides (1g glucide = 4 kcal)
    $caloriesRestantes = $calories - ($proteines * 4) - ($lipides * 9);
    $glucides = $caloriesRestantes / 4;

    // Arrondir les valeurs pour l'affichage
    $calories = round($calories);
    $proteines = round($proteines);
    $glucides = round($glucides);
    $lipides = round($lipides);
    }
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
        
        <a href="Journal.php" class="nav-item">
            <span class="icon">🍽️</span>
            <span>Journal Alimentaire</span>
        </a>
        
        <a href="Objectif.html" class="nav-item">
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
        <h1>Mes Objectifs</h1>
        <span class="date-badge">Mis à jour le 15 Mars</span>
    </header>

    <div class="journal-grid">
        
      <div class="card health-card">
            <div class="card-header">
                <h2>Progression Poids</h2>
                <?php if ($objectifActuel): ?>
                    <span class="badge white">
                        <?php 
                            if ($typeObjectif == 'perte_poids') echo 'Perte de poids';
                            elseif ($typeObjectif == 'prise_masse') echo 'Prise de masse';
                            else echo 'Maintien';
                        ?>
                    </span>
                <?php endif; ?>
            </div>
            
            <div class="health-content">
                <div class="card health-card" style="height: 100%; display: flex; flex-direction: column;">
                <?php if ($objectifActuel): ?>
                    
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
                        <div class="progress-bar" style="width: <?php echo isset($pourcentageProgression) ? $pourcentageProgression : 0; ?>%;"></div>
                    </div>
                    
                    <p class="encouragement">Encore <?php echo $diffPoids; ?> kg pour atteindre ton but, Youssef !</p>

                <?php else: ?>
                    
                    <p style="font-style: italic; opacity: 0.9; margin-top: 20px; font-size: 1rem;">
                        ⚠️ Veuillez définir et enregistrer un objectif ci-dessous pour voir votre progression.
                    </p>
                    
                <?php endif; ?>
            </div>
        </div>
                </div>
                

       <div class="card progress-card">
            <div class="card-header">
                <h2>Cibles Journalières</h2>
                <?php if ($objectifActuel): ?>
                    <span class="badge yellow"><?php echo $calories; ?> kcal/jour</span>
                <?php endif; ?>
            </div>
            
            <div class="progress-content">
                <div class="progress-content" style="flex: 1; display: flex; flex-direction: column; justify-content: center;">
                <?php if ($objectifActuel): ?>
                    
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

                <?php else: ?>
                    
                    <p style="font-style: italic; color: var(--text-gray); margin-top: 20px; font-size: 1rem;">
                        ⚠️ Veuillez valider un objectif pour calculer automatiquement vos besoins nutritionnels.
                    </p>

                <?php endif; ?>
            </div>
        </div>
    </div>


        <div class="form-section">
            <div class="card">
    <div class="card-header">
        <h2><?php echo $objectifActuel ? '✏️ Modifier mon objectif actuel' : '🚀 Créer un nouvel objectif'; ?></h2>
    </div>

    <form action="../../Controller/ObjectifController.php?action=<?php echo $objectifActuel ? 'update&id='.$objectifActuel['id_objectif'] : 'add'; ?>" method="POST">
        
        <input type="hidden" name="id_utilisateur" value="<?php echo $id_utilisateur_connecte; ?>">

        <div class="form-row">
            <div class="form-group">
                <label>Type d'objectif</label>
                <select name="type" class="form-control">
                    <option value="perte_poids" <?php if($objectifActuel && $objectifActuel['type_objectif'] == 'perte_poids') echo 'selected'; ?>>Perte de poids</option>
                    <option value="maintien" <?php if($objectifActuel && $objectifActuel['type_objectif'] == 'maintien') echo 'selected'; ?>>Maintien de forme</option>
                    <option value="prise_masse" <?php if($objectifActuel && $objectifActuel['type_objectif'] == 'prise_masse') echo 'selected'; ?>>Prise de masse</option>
                </select>
            </div>
            <div class="form-group">
                <label>Poids cible (kg)</label>
                <input step="0.1" name="poids_cible" class="form-control" value="<?php echo $objectifActuel ? $objectifActuel['poids_cible'] : ''; ?>">
            </div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label>Date de début</label>
                <input type="date" name="Date_Debut" class="form-control" value="<?php echo $objectifActuel ? $objectifActuel['date_debut'] : date('Y-m-d'); ?>">
            </div>
            <div class="form-group">
                <label>Date de fin (prévue)</label>
                <input type="date" name="Date_Fin" class="form-control" value="<?php echo $objectifActuel ? $objectifActuel['date_fin'] : ''; ?>">
            </div>
        </div>

        <?php if($objectifActuel): ?>
            <div class="form-group" style="margin-top: 15px;">
                <label style="color: #e74c3c; font-weight: bold;">Statut de l'objectif (Modifier pour pouvoir en créer un nouveau)</label>
                <select name="statut" class="form-control" style="border: 2px solid #e74c3c;">
                    <option value="en_cours" selected>En cours</option>
                    <option value="Atteint">🏆 Objectif Atteint</option>
                    <option value="Abandonné">❌ Abandonner l'objectif</option>
                </select>
            </div>
        <?php else: ?>
            <input type="hidden" name="statut" value="en_cours">
        <?php endif; ?>

        <div class="form-actions" style="margin-top: 20px; display: flex; justify-content: space-between;">
            
            <?php if($objectifActuel): ?>
                <a href="../../Controller/ObjectifController.php?action=delete&id=<?php echo $objectifActuel['id_objectif']; ?>" 
                   class="btn-danger" style="color: red; text-decoration: none;" 
                   onclick="return confirm('Supprimer cet objectif ?');">Supprimer</a>
                
                <button type="submit" class="btn-main" style="background: #3498db;">Mettre à jour l'objectif</button>
            
            <?php else: ?>
                <div></div> <button type="submit" class="btn-main">Enregistrer mon objectif</button>
            <?php endif; ?>

        </div>
    </form>
</div>
        </div>

                </form>
            </div>
        </div>

    </div>
</div>
    <script src="JavaScript_Front.js"></script>
</body>
</html>