<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SmartPlate - Mon Journal</title>
    <link rel="stylesheet" href="templates/template.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
</head>
<?php
    include_once '../../Model/Journal_Class.php';
    include_once '../../Model/Repas_Class.php';
    include_once '../../Model/Objectif_Class.php';
    $id_utilisateur_connecte = 1; // Remplacer par l'ID de l'utilisateur connecte si disponible
    // Mode recherche par date
    if (isset($_GET['journal_id']) && !empty($_GET['journal_id'])) {
        $latestJournal = Journal::getById((int)$_GET['journal_id']);
    } elseif (isset($_GET['date_recherche']) && !empty($_GET['date_recherche'])) {
        $searchDate = $_GET['date_recherche'];
        $latestJournal = Journal::getByDate($id_utilisateur_connecte, $searchDate);
    } else {
        $latestJournal = Journal::getLatest($id_utilisateur_connecte);
    }

    $repasList = [];
    if (!empty($latestJournal['id_journal'])) {
        $repasList = Repas::listeParJournal((int)$latestJournal['id_journal']);
    }

    // Initialiser l'objectif actif (nécessaire avant les calculs ci-dessous)
    $objectifActif = Objectif::getActif($id_utilisateur_connecte);
    $objectifMessage = "Veuillez remplir un objectif pour afficher ces informations.";
    $hasJournalDisponible = !empty($latestJournal['id_journal']);

    // Récupérer l'objectif actif dès maintenant (utilisé pour calculs ci-dessous)
    

    // Calculer les totaux consommés à partir des repas du journal
    $totalCaloriesConsommees = 0;
    $totalProteinesConsommees = 0;
    $totalGlucidesConsommees = 0;
    $totalLipidesConsommees = 0;
    foreach ($repasList as $rp) {
        $totalCaloriesConsommees += floatval($rp['nbre_calories'] ?? 0);
        $totalProteinesConsommees += floatval($rp['proteine'] ?? 0);
        $totalGlucidesConsommees += floatval($rp['glucide'] ?? 0);
        $totalLipidesConsommees += floatval($rp['lipide'] ?? 0);
    }

    // Calculer les cibles journalières à partir de l'objectif actif (reprise de la logique de Objectif.php)
    $cibleCalories = 0; $cibleProteines = 0; $cibleGlucides = 0; $cibleLipides = 0;
    if ($objectifActif) {
        // récupérer le poids de référence via le modèle (déplacé hors de la vue)
        $poidsRef = Journal::getFirstWeight($id_utilisateur_connecte) ?? 75;

        $typeObjectif = $objectifActif ? $objectifActif['type_objectif'] : 'maintien';
        if ($typeObjectif == 'perte_poids') {
            $cibleCalories = $poidsRef * 22;
            $cibleProteines = $poidsRef * 2.2;
            $cibleLipides = $poidsRef * 0.8;
        } elseif ($typeObjectif == 'prise_masse') {
            $cibleCalories = $poidsRef * 30;
            $cibleProteines = $poidsRef * 1.8;
            $cibleLipides = $poidsRef * 1;
        } else {
            $cibleCalories = $poidsRef * 25;
            $cibleProteines = $poidsRef * 1.5;
            $cibleLipides = $poidsRef * 0.9;
        }
        $caloriesRestantes = $cibleCalories - ($cibleProteines * 4) - ($cibleLipides * 9);
        $cibleGlucides = $caloriesRestantes / 4;

        // Arrondir
        $cibleCalories = round($cibleCalories);
        $cibleProteines = round($cibleProteines);
        $cibleGlucides = round($cibleGlucides);
        $cibleLipides = round($cibleLipides);
    }

    // Pourcentage de l'objectif calorique atteint (capé à 100)
    $percentCalories = $cibleCalories > 0 ? round(min(100, ($totalCaloriesConsommees / $cibleCalories) * 100)) : 0;

    // Calcul d'un score nutritionnel réaliste
    // On compare consommé vs cible pour calories et macronutriments et on combine
    function _comp_score($cons, $target) {
        if ($target <= 0) return ($cons <= 0) ? 1 : 0;
        $diff = abs($cons - $target) / $target; // fraction d'écart
        $score = max(0, 1 - $diff); // 1 parfait, 0 mauvais
        return $score;
    }

    $w_cal = 0.4; $w_prot = 0.25; $w_glu = 0.2; $w_lip = 0.15;
    $comp_cal = _comp_score($totalCaloriesConsommees, $cibleCalories);
    $comp_prot = _comp_score($totalProteinesConsommees, $cibleProteines);
    $comp_glu = _comp_score($totalGlucidesConsommees, $cibleGlucides);
    $comp_lip = _comp_score($totalLipidesConsommees, $cibleLipides);
    $nutritionScore = round(100 * ($w_cal * $comp_cal + $w_prot * $comp_prot + $w_glu * $comp_glu + $w_lip * $comp_lip));
    if ($nutritionScore < 0) $nutritionScore = 0;
    if ($nutritionScore > 100) $nutritionScore = 100;

    if (!$objectifActif) {
        $objectifsUtilisateur = array_values(array_filter(Objectif::liste(), function ($objectif) use ($id_utilisateur_connecte) {
            return isset($objectif['id_utilisateur']) && (int)$objectif['id_utilisateur'] === (int)$id_utilisateur_connecte;
        }));

        if (!empty($objectifsUtilisateur)) {
            usort($objectifsUtilisateur, function ($a, $b) {
                return ((int)($b['id_objectif'] ?? 0)) <=> ((int)($a['id_objectif'] ?? 0));
            });

            $dernierStatut = strtolower(trim((string)($objectifsUtilisateur[0]['statut'] ?? '')));

            if (in_array($dernierStatut, ['atteint', 'objectif atteint'], true)) {
                $objectifMessage = "Votre dernier objectif est atteint. Veuillez remplir un nouvel objectif pour voir ces informations.";
            } elseif (in_array($dernierStatut, ['abandonne'], true)) {
                $objectifMessage = "Votre dernier objectif a ete abandonné. Veuillez remplir un nouvel objectif pour voir ces informations.";
            }
        }
    }

    $journalMessage = $objectifMessage;
    $repasMessage = $hasJournalDisponible
        ? ''
        : "Aucun journal n'est affiché pour le moment. Veuillez enregistrer ou afficher un journal pour acceder aux repas.";

    // Mode edition (pre-remplir le formulaire)
    $isEdit = false;
    $editJournal = null;
    if (isset($_GET['edit_id']) && !empty($_GET['edit_id'])) {
        $editId = intval($_GET['edit_id']);
        $editJournal = Journal::getById($editId);
        if ($editJournal) {
            $isEdit = true;
        }
    }

    // Construire une URL absolue vers le controleur (evite les chemins relatifs qui causent 404)
    $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'];
    // Tenter de detecter la racine du projet en retirant '/View/...' si present
    $phpSelf = $_SERVER['PHP_SELF'];
    $projectBase = '';
    $posView = strpos($phpSelf, '/View/');
    if ($posView !== false) {
        $projectBase = substr($phpSelf, 0, $posView);
    } else {
        // fallback : remonter de deux niveaux
        $projectBase = dirname($phpSelf, 2);
    }
    $controllerUrl = $scheme . '://' . $host . rtrim($projectBase, '/') . '/Controller/JournalController.php';
    $repasControllerUrl = $scheme . '://' . $host . rtrim($projectBase, '/') . '/Controller/RepasController.php';

    function formatDateFrLong($dateStr) {
        try {
            $d = new DateTime($dateStr);
            setlocale(LC_TIME, 'fr_FR.UTF-8');
            return strftime('%e %B %Y', $d->getTimestamp());
        } catch (Exception $e) {
            return $dateStr;
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
        <a href="Accueil.php" class="nav-item">
            <span class="icon">🏠</span>
            <span>Accueil</span>
        </a>

        <a href="Produits.php" class="nav-item">
            <span class="icon">🛍️</span>
            <span>Produits</span>
        </a>

        <a href="Recettes.php" class="nav-item">
            <span class="icon">🥗</span>
            <span>Recettes</span>
        </a>

        <div class="nav-section" style="margin-top: 1.5rem; margin-bottom: 1.5rem;">
            <span class="nav-section-title">Suivi Nutritionnel</span>
            
            <div class="sub-nav" style="padding-left: 1.2rem; margin-top: 0.5rem; border-left: 2px solid #e2e8f0; margin-left: 1rem;">
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
            </div>
        </div>

        <a href="Forum.php" class="nav-item">
            <span class="icon">💬</span>
            <span>Forum</span>
        </a>

        <a href="Reclamation.php" class="nav-item">
            <span class="icon">📝</span>
            <span>Réclamation</span>
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
        
        <header class="dashboard-header" style="flex-direction: column; align-items: stretch; gap: 1.5rem; margin-bottom: 2rem;">
            
            <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem;">
                <h1>Journal Alimentaire</h1>
                
                <div style="display: flex; gap: 1.5rem; align-items: center;">
                    <form id="journalSearchForm" action="Journal.php" method="GET" style="display: flex; gap: 0.5rem; align-items: center; border-right: 2px solid #e2e8f0; padding-right: 1.5rem;">
                        <input type="hidden" name="action" value="consulterJournal">
                        <span style="font-size: 0.85rem; color: var(--text-gray); font-weight: 600;">Historique :</span>
                        <input type="date" name="date_recherche" class="form-control" style="padding: 0.4rem; font-size: 0.9rem;">
                        <button type="submit" class="btn-secondary" style="padding: 0.4rem 0.8rem; font-size: 0.9rem;">Afficher</button>
                    </form>

                </div>
            </div>
                <div class="card">
    <div class="card-header">
        <h2>Enregistrer ma Journée</h2>
    </div>

    <?php
        $submitLabel = $isEdit ? "Enregistrer les modifications" : "Enregistrer l'Entree";
        // Form action for adding
        $formActionAdd = $controllerUrl . '?action=add';
        // Date par defaut : en edition = journal edite ; sinon alignee sur le journal affiche (evite decalage jour du formulaire vs repas du dernier journal)
        if ($isEdit && $editJournal) {
            $defaultDateJournal = date('Y-m-d', strtotime($editJournal['date_journal']));
        } elseif (!empty($latestJournal['date_journal'])) {
            $defaultDateJournal = date('Y-m-d', strtotime($latestJournal['date_journal']));
        } else {
            $defaultDateJournal = date('Y-m-d');
        }
    ?>
    <?php if ($objectifActif): ?>
    <form id="journalAddForm" action="<?php echo $formActionAdd; ?>" method="POST" class="modern-form" novalidate>
        
        <div class="form-row">
            <div class="form-group">
                <label for="date_journal">Date du jour</label>
                <input type="date" id="date_journal" name="date_journal" class="form-control" value="<?php echo htmlspecialchars($defaultDateJournal); ?>">
            </div>
            <div class="form-group">
                <label for="poids_actuel">Mon poids actuel (kg)</label>
                <input id="poids_actuel" name="poids_actuel" class="form-control" placeholder="Ex: 75.2" step="0.1" value="<?php echo $isEdit ? htmlspecialchars($editJournal['poids_actuel']) : htmlspecialchars(is_array($latestJournal) ? ($latestJournal['poids_actuel'] ?? '') : ''); ?>">
            </div>
        </div>

        <div class="form-row" style="margin-top: 1rem; border-top: 1px solid #f1f2f6; padding-top: 1.5rem;">
            <div class="form-group">
                <label for="heures_sommeil">Heures de sommeil</label>
                <input id="heures_sommeil" name="heures_sommeil" class="form-control" placeholder="Ex: 7.5" step="0.5" value="<?php echo $isEdit ? htmlspecialchars($editJournal['heures_sommeil']) : htmlspecialchars(is_array($latestJournal) ? ($latestJournal['heures_sommeil'] ?? '') : ''); ?>">
            </div>
            <div class="form-group">
                <label for="humeur">Humeur du jour</label>
                <select id="humeur" name="humeur" class="form-control">
                    <?php
                    $hSel = $isEdit ? ($editJournal['humeur'] ?? 'neutre') : (is_array($latestJournal) ? ($latestJournal['humeur'] ?? 'excellent') : 'excellent');
                    ?>
                    <option value="excellent" <?php echo ($hSel === 'excellent') ? 'selected' : ''; ?>>🤩​ Excellent</option>
                    <option value="bien" <?php echo ($hSel === 'bien') ? 'selected' : ''; ?>>🙂​ Bien</option>
                    <option value="neutre" <?php echo ($hSel === 'neutre') ? 'selected' : ''; ?>>😐​ Neutre</option>
                    <option value="fatigue" <?php echo ($hSel === 'fatigue') ? 'selected' : ''; ?>>😴​ Fatigue(e)</option>
                    <option value="stresse" <?php echo ($hSel === 'stresse') ? 'selected' : ''; ?>>😰​ Stresse(e)</option>
                </select>
            </div>
        </div>

        <div class="form-actions" style="display: flex; justify-content: flex-end; gap: 1rem; margin-top: 2rem;">
            <button type="button" id="journalResetBtn" class="btn-secondary">Effacer</button>
            <!-- Bouton pour ajouter un nouveau journal -->
            <button type="submit" id="journalAddBtn" class="btn-main">Enregistrer l'Entree</button>

            <!-- Bouton pour enregistrer les modifications (cache par defaut) -->
            <?php $updateFormActionTemplate = $controllerUrl . '?action=update&id='; ?>
            <button type="submit" id="journalUpdateBtn" class="btn-main" style="display: <?php echo $isEdit ? 'inline-block' : 'none'; ?>; background:#111827; color:#fff;" formaction="<?php echo $isEdit ? ($updateFormActionTemplate . urlencode($editJournal['id_journal'])) : ''; ?>">Enregistrer les modifications</button>
        </div>
    </form>
    <?php else: ?>
    <div style="padding: 1.5rem 0; color: var(--text-gray);">
        <p style="margin: 0 0 0.75rem; font-weight: 600;">Le formulaire du journal est indisponible.</p>
        <p style="margin: 0 0 1rem;"><?php echo htmlspecialchars($journalMessage); ?></p>
        <a href="Objectif.php" class="btn-secondary" style="display: inline-flex; text-decoration: none;">Remplir un objectif</a>
    </div>
    <?php endif; ?>
                </div>
            <div class="active-journal-bar" style="display: flex; justify-content: space-between; align-items: center; background: white; padding: 1rem 1.5rem; border-radius: 16px; box-shadow: var(--shadow); border-left: 4px solid var(--green-light);">
                <div class="journal-info-left" style="display: flex; align-items: center; gap: 1rem;">
                    <?php if ($latestJournal): ?>
                        <span class="date-badge" style="font-size: 1.1rem; font-weight: 600;"><?php echo htmlspecialchars(formatDateFrLong($latestJournal['date_journal'])); ?></span>
                        <span class="badge white" style="border: 1px solid #e2e8f0; font-weight: 600;"><?php echo htmlspecialchars($latestJournal['poids_actuel']); ?> kg</span>
                        <span class="badge white" style="border: 1px solid #e2e8f0; font-weight: 600;"> <?php echo htmlspecialchars(!empty($latestJournal['heures_sommeil']) ? ($latestJournal['heures_sommeil'] . ' h') : '-'); ?></span>
                        <?php
$humeur = $latestJournal['humeur'] ?? '';

switch ($humeur) {
     case 'Excellent':
        $humeurLabel = '🤩 Excellent';
        $humeurClass = 'badge-humeur-excellent';
        break;
    case 'Bien':
        $humeurLabel = '🙂 Bien';
        $humeurClass = 'badge-humeur-bien';
        break;
    case 'Neutre':
        $humeurLabel = '😐 Neutre';
        $humeurClass = 'badge-humeur-neutre';
        break;
    case 'Fatigué':
        $humeurLabel = '😴 Fatigué(e)';
        $humeurClass = 'badge-humeur-fatigue';
        break;
    case 'Stressé':
        $humeurLabel = '😰 Stressé(e)';
        $humeurClass = 'badge-humeur-stresse';
        break;
    default:
        $humeurLabel = '-';
        $humeurClass = '';
}
?>
                        <span class="badge <?php echo $humeurClass; ?>" 
      style="font-weight:600; padding:0.35rem 0.6rem; border-radius:12px;">
    <?php echo htmlspecialchars($humeurLabel); ?>
</span>
                        <span class="badge green-light">Journal Actif</span>
                    <?php else: ?>
                        <span class="date-badge" style="font-size: 1.1rem; font-weight: 600;">Aucun journal</span>
                        <span class="badge white" style="border: 1px solid #e2e8f0; font-weight: 600;">-</span>
                        <span class="badge yellow-light">Creez un journal</span>
                    <?php endif; ?>
                </div>
                
                <div class="journal-actions-right" style="display: flex; gap: 0.8rem;">
                    <?php if ($latestJournal): ?>
                        <button type="button"
                                class="btn-icon btn-modify"
                                style="background:none; border:1px solid #e2e8f0; color:#111827; padding:5px 10px; border-radius:8px; cursor:pointer;"
                                data-id="<?php echo htmlspecialchars($latestJournal['id_journal']); ?>"
                                data-date="<?php echo htmlspecialchars($latestJournal['date_journal']); ?>"
                                data-poids="<?php echo htmlspecialchars($latestJournal['poids_actuel']); ?>"
                                data-heures="<?php echo htmlspecialchars($latestJournal['heures_sommeil'] ?? ''); ?>"
                                data-humeur="<?php echo htmlspecialchars($latestJournal['humeur'] ?? 'neutre'); ?>">
                            Modifier
                        </button>
                        <a href="../../Controller/JournalController.php?action=delete&id=<?php echo urlencode($latestJournal['id_journal']); ?>" class="btn-icon danger" style="text-decoration: none; color: #e53e3e; border: 1px solid #feb2b2; padding: 5px 10px; border-radius: 8px;" onclick="return confirm('Voulez-vous vraiment supprimer ce journal et ses repas ?');">
                            Supprimer
                        </a>
                    <?php else: ?>
                        <button class="btn-icon" disabled style="opacity:0.6;">Modifier</button>
                        <button class="btn-icon" disabled style="opacity:0.6;">Supprimer</button>
                    <?php endif; ?>
                </div>
            </div>
        </header>

        <div class="journal-grid">
            
            <div class="card progress-card">
                <div class="card-header">
                    <h2>Total Calorique</h2>
                    <?php if ($objectifActif): ?>
                        <span class="badge yellow">Objectif : <?php echo htmlspecialchars($cibleCalories); ?> kcal</span>
                    <?php endif; ?>
                </div>
                <div class="progress-content">
                    <?php if ($objectifActif): ?>
                    <div class="circle-chart">
                        <div class="circle-inner">
                            <span class="percentage"><?php echo htmlspecialchars($percentCalories); ?>%</span>
                            <span class="label">Complete</span>
                        </div>
                    </div>
                    <div class="macros-summary">
                        <div class="macro-item"><span class="dot blue"></span> Glucides <span><?php echo htmlspecialchars(round($totalGlucidesConsommees)); ?>g / <?php echo htmlspecialchars($cibleGlucides); ?>g</span></div>
                        <div class="macro-item"><span class="dot yellow"></span> Proteines <span><?php echo htmlspecialchars(round($totalProteinesConsommees)); ?>g / <?php echo htmlspecialchars($cibleProteines); ?>g</span></div>
                        <div class="macro-item"><span class="dot green"></span> Lipides <span><?php echo htmlspecialchars(round($totalLipidesConsommees)); ?>g / <?php echo htmlspecialchars($cibleLipides); ?>g</span></div>
                    </div>
                    <?php else: ?>
                    <div style="padding: 1.5rem 0; color: var(--text-gray);">
                        <p style="margin: 0 0 0.75rem; font-weight: 600;">Aucune donnee d'objectif disponible.</p>
                        <p style="margin: 0 0 1rem;"><?php echo htmlspecialchars($objectifMessage); ?></p>
                        <a href="Objectif.php" class="btn-secondary" style="display: inline-flex; text-decoration: none;">Remplir un objectif</a>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="card health-card">
                <div class="card-header">
                    <h2>Score Nutritionnel</h2>
                </div>
                <div class="health-content">
                    <?php if ($objectifActif): ?>
                    <?php
if ($nutritionScore >= 85) {
    $scoreLabel = 'Très équilibré';
    $badgeClass = 'badge-nutrition-green';
} elseif ($nutritionScore >= 70) {
    $scoreLabel = 'Équilibré';
    $badgeClass = 'badge-nutrition-light-green';
} elseif ($nutritionScore >= 50) {
    $scoreLabel = 'Moyen';
    $badgeClass = 'badge-nutrition-orange';
} else {
    $scoreLabel = 'À améliorer';
    $badgeClass = 'badge-nutrition-red';
}
?>
                    <div class="score-display">
                        <span class="score-number"><?php echo htmlspecialchars($nutritionScore); ?>%</span>
                        <span class="badge <?php echo $badgeClass; ?>"><?php echo htmlspecialchars($scoreLabel); ?></span>
                    </div>
                    <div class="progress-bar-container">
                        <div class="progress-bar" style="width: <?php echo htmlspecialchars($nutritionScore); ?>%;"></div>
                    </div>
                    <p class="encouragement"><?php echo ($nutritionScore >= 50) ? 'Continue comme ça pour atteindre ton objectif !' : 'Ajustez vos apports pour se rapprocher de vos cibles.'; ?></p>
                    <?php else: ?>
                    <div style="padding: 1.5rem 0; color: #1f2937;">
                        <p style="margin: 0 0 0.75rem; font-weight: 600;">Score nutritionnel indisponible.</p>
                        <p style="margin: 0;"><?php echo htmlspecialchars($objectifMessage); ?></p>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="meals-section">
                <div class="section-header">
                    <h2>Mes Repas</h2>
                </div>
                <?php if ($hasJournalDisponible && !empty($latestJournal['date_journal'])): ?>
                    <p style="color: var(--text-gray); font-size: 0.85rem; margin: -0.75rem 0 1rem;">Repas lies au journal du <strong><?php echo htmlspecialchars(date('d/m/Y', strtotime($latestJournal['date_journal']))); ?></strong>.</p>
                <?php endif; ?>

                <?php if (!$hasJournalDisponible): ?>
                    <div class="card" style="margin-top: 1rem;">
                        <div style="padding: 1.5rem 0; color: var(--text-gray);">
                            <p style="margin: 0 0 0.75rem; font-weight: 600;">Le bloc repas est indisponible.</p>
                            <p style="margin: 0;"><?php echo htmlspecialchars($repasMessage); ?></p>
                        </div>
                    </div>
                <?php elseif (empty($repasList)): ?>
                    <p style="color: var(--text-gray); padding: 1rem;">Aucun repas enregistre pour ce journal. Ajoute un repas ci-dessous.</p>
                <?php else: ?>
                    <?php foreach ($repasList as $r): ?>
                        <?php
                        $imgSrc = 'https://images.unsplash.com/photo-1490645935967-10de6ba17061?auto=format&fit=crop&w=150&q=80';
                        if (!empty($r['image_repas'])) {
                            $imgSrc = '../../uploads/repas/' . rawurlencode($r['image_repas']);
                        }
                        ?>
                <div class="meal-card">
                    <div class="meal-image">
                        <img src="<?php echo htmlspecialchars($imgSrc); ?>" alt="Repas">
                    </div>
                    <div class="meal-info">
                        <div class="meal-tags">
                            <span class="badge green-light"><?php echo htmlspecialchars($r['type_repas'] ?? ''); ?></span>
                            <span class="time-info"><?php echo htmlspecialchars($r['heure_repas'] ?? ''); ?></span>
                        </div>
                        <h3><?php echo htmlspecialchars($r['nom'] ?? 'Repas'); ?></h3>
                        <p><?php echo htmlspecialchars(($r['quantite'] ?? '') !== '' ? 'Quantite : ' . $r['quantite'] . ' g' : ''); ?></p>
                    </div>
                    <div class="meal-stats">
                        <div class="stat-col">
                            <span class="stat-val"><?php echo htmlspecialchars($r['nbre_calories'] ?? '-'); ?> kcal</span>
                            <span class="stat-val"><?php echo htmlspecialchars($r['glucide'] ?? '-'); ?>g Glucides</span>
                        </div>
                        <div class="stat-col">
                            <span class="stat-val"><?php echo htmlspecialchars($r['proteine'] ?? '-'); ?>g Proteines</span>
                            <span class="stat-val"><?php echo htmlspecialchars($r['lipide'] ?? '-'); ?>g Lipides</span>
                        </div>
                        <div class="meal-actions">
                            <button type="button" class="btn-icon btn-meal-modify" data-id="<?php echo htmlspecialchars($r['id_repas']); ?>" data-journal-id="<?php echo htmlspecialchars($r['id_journal']); ?>" data-type="<?php echo htmlspecialchars($r['type_repas'] ?? ''); ?>" data-heure="<?php echo htmlspecialchars($r['heure_repas'] ?? ''); ?>" data-nom="<?php echo htmlspecialchars($r['nom'] ?? ''); ?>" data-quantite="<?php echo htmlspecialchars($r['quantite'] ?? ''); ?>" data-calories="<?php echo htmlspecialchars($r['nbre_calories'] ?? ''); ?>" data-proteine="<?php echo htmlspecialchars($r['proteine'] ?? ''); ?>" data-glucide="<?php echo htmlspecialchars($r['glucide'] ?? ''); ?>" data-lipide="<?php echo htmlspecialchars($r['lipide'] ?? ''); ?>">Modifier</button>
                            <a href="<?php echo htmlspecialchars($repasControllerUrl . '?action=delete&id=' . urlencode($r['id_repas'])); ?>" class="btn-icon danger" onclick="return confirm('Voulez-vous vraiment supprimer ce repas ?');">Supprimer</a>
                        </div>
                    </div>
                </div>
                    <?php endforeach; ?>
                <?php endif; ?>

               <?php if ($hasJournalDisponible): ?>
               <div class="card" style="margin-top: 2rem; border: 2px solid var(--green-light);">
    <div class="card-header">
        <h2>Enregistrer un nouveau repas</h2>
    </div>
    
    <form id="repasForm" action="<?php echo htmlspecialchars($repasControllerUrl . '?action=add'); ?>" method="POST" enctype="multipart/form-data" class="modern-form">
        <?php if (!empty($latestJournal['id_journal'])): ?>
            <input type="hidden" name="id_journal" value="<?php echo htmlspecialchars($latestJournal['id_journal']); ?>">
        <?php endif; ?>

        <div class="form-row">
            <div class="form-group">
                <label for="type_repas">Type de repas</label>
                <select id="type_repas" name="type_repas" class="form-control">
                    <option value="Petit-Dejeuner">Petit-Dejeuner</option>
                    <option value="Dejeuner">Dejeuner</option>
                    <option value="Diner">Diner</option>
                    <option value="Collation">Collation</option>
                </select>
            </div>
            <div class="form-group">
                <label for="heure_repas">Heure du repas</label>
                <input type="time" id="heure_repas" name="heure_repas" class="form-control">
            </div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label for="nom">Nom du repas</label>
                <input type="text" id="nom" name="nom" class="form-control" placeholder="Ex: Blanc de poulet">
            </div>
            <div class="form-group">
                <label for="quantite">Quantite (g ou portion)</label>
                <input id="quantite" name="quantite" class="form-control" placeholder="Ex: 150">
            </div>
        </div>

        <div class="form-row">
            <!-- ✨ DÉBUT DU BOUTON IA ✨ -->
        <style>
            /* Style du switch façon iOS */
            .switch { position: relative; display: inline-block; width: 50px; height: 28px; flex-shrink: 0; }
            .switch input { opacity: 0; width: 0; height: 0; }
            .slider { position: absolute; cursor: pointer; top: 0; left: 0; right: 0; bottom: 0; background-color: #cbd5e1; transition: .3s; border-radius: 34px; }
            .slider:before { position: absolute; content: ""; height: 20px; width: 20px; left: 4px; bottom: 4px; background-color: white; transition: .3s; border-radius: 50%; box-shadow: 0 2px 4px rgba(0,0,0,0.2); }
            input:checked + .slider { background-color: #84cc16; /* Vert SmartPlate */ }
            input:checked + .slider:before { transform: translateX(22px); }
            
            /* Animation douce pour la boîte */
            .ai-toggle-box { transition: all 0.3s ease; }
            .ai-toggle-box:hover { box-shadow: 0 4px 6px rgba(0,0,0,0.05); border-color: #cbd5e1; }
        </style>

        <div class="ai-toggle-box" style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 1.5rem; background: #f8fafc; padding: 12px 18px; border-radius: 12px; border: 1px solid #e2e8f0;">
            <div style="display: flex; flex-direction: column;">
                <span style="font-weight: 600; color: #1e293b; display: flex; align-items: center; gap: 8px;">
                    🤖 Assistant IA SmartPlate
                </span>
                <span style="font-size: 0.85rem; color: #64748b; margin-top: 2px;">
                    Laisser l'IA analyser la photo et calculer les macros automatiquement.
                </span>
            </div>
            <label class="switch">
                <input type="checkbox" id="ia_mode_toggle" checked>
                <span class="slider"></span>
            </label>
        </div>
        <!-- ✨ FIN DU BOUTON IA ✨ -->
            <div class="form-group">
                <label for="repas_image">Photo du repas (optionnel)</label>
                <input type="file" id="repas_image" name="repas_image" accept="image/*" class="form-control">
            </div>
            <div class="form-group" style="display:flex;align-items:center;">
                <div id="repasImagePreview" style="max-width:120px;max-height:80px;border:1px dashed #e2e8f0;padding:6px;border-radius:6px;display:flex;align-items:center;justify-content:center;color:#94a3b8;">
                    Apercu
                </div>
            </div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label for="nbre_calories">Calories (kcal)</label>
                <input id="nbre_calories" name="nbre_calories" class="form-control" placeholder="Ex: 250">
            </div>
            <div class="form-group">
                <label for="proteine">Proteines (g)</label>
                <input id="proteine" name="proteine" class="form-control" placeholder="Ex: 30">
            </div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label for="glucide">Glucides (g)</label>
                <input id="glucide" name="glucide" class="form-control" placeholder="Ex: 0">
            </div>
            <div class="form-group">
                <label for="lipide">Lipides (g)</label>
                <input id="lipide" name="lipide" class="form-control" placeholder="Ex: 5">
            </div>
        </div>

        <div class="form-actions" style="display: flex; justify-content: flex-end; gap: 1rem; margin-top: 1.5rem;">
            <button type="reset" id="repasResetBtn" class="btn-secondary">Effacer les champs</button>
            <div class="right-actions">
                <button type="submit" id="repasAddBtn" class="btn-main" <?php echo empty($latestJournal['id_journal']) ? 'disabled title="Creez d abord un journal pour aujourd hui"' : ''; ?>>Ajouter au journal</button>
                <button type="submit" id="repasUpdateBtn" class="btn-main" style="display:none; background:#111827; color:#fff;">Enregistrer les modifications</button>
            </div>
        </div>
    </form>
</div>
                <?php endif; ?>

            </div>
        </div>
    </div>

    <script src="JavaScript_Front.js"></script>
    <script>
        // Fournit l'URL absolue du controleur au JS
        window.JOURNAL_CONTROLLER_URL = '<?php echo $controllerUrl; ?>';
        window.REPAS_CONTROLLER_URL = '<?php echo $repasControllerUrl; ?>';
    </script>
</body>
</html>

