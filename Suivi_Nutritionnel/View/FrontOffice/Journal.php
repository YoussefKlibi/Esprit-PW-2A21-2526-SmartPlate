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
    $id_utilisateur_connecte = 1; // Remplacer par l'ID de l'utilisateur connecté si disponible
    // Mode recherche par date
    if (isset($_GET['date_recherche']) && !empty($_GET['date_recherche'])) {
        $searchDate = $_GET['date_recherche'];
        $latestJournal = Journal::getByDate($id_utilisateur_connecte, $searchDate);
    } else {
        $latestJournal = Journal::getLatest($id_utilisateur_connecte);
    }

    // Mode édition (pré-remplir le formulaire)
    $isEdit = false;
    $editJournal = null;
    if (isset($_GET['edit_id']) && !empty($_GET['edit_id'])) {
        $editId = intval($_GET['edit_id']);
        $editJournal = Journal::getById($editId);
        if ($editJournal) {
            $isEdit = true;
        }
    }

    // Construire une URL absolue vers le contrôleur (évite les chemins relatifs qui causent 404)
    $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'];
    // Tenter de détecter la racine du projet en retirant '/View/...' si présent
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
            <img src="..\assets\logo.png" alt="Logo" height="80%" width="50%">
            <h2>SmartPlate</h2>
        </div>

        <nav class="sidebar-nav">
            <span class="nav-section-title">Menu Principal</span>
            
            <a href="Journal.php" class="nav-item active">
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
        <h2>📅 Enregistrer ma Journée</h2>
    </div>

    <?php
        $submitLabel = $isEdit ? "Enregistrer les modifications" : "Enregistrer l'Entrée";
        // Form action for adding
        $formActionAdd = $controllerUrl . '?action=add';
    ?>
    <form id="journalAddForm" action="<?php echo $formActionAdd; ?>" method="POST" class="modern-form" novalidate>
        
        <div class="form-row">
            <div class="form-group">
                <label for="date_journal">Date du jour</label>
                <input type="date" id="date_journal" name="date_journal" class="form-control" value="<?php echo $isEdit ? htmlspecialchars($editJournal['date_journal']) : date('Y-m-d'); ?>">
            </div>
            <div class="form-group">
                <label for="poids_actuel">Mon poids actuel (kg)</label>
                <input id="poids_actuel" name="poids_actuel" class="form-control" placeholder="Ex: 75.2" step="0.1" value="<?php echo $isEdit ? htmlspecialchars($editJournal['poids_actuel']) : ''; ?>">
            </div>
        </div>

        <div class="form-row">
            <div class="form-group">

        <div class="form-row" style="margin-top: 1rem; border-top: 1px solid #f1f2f6; padding-top: 1.5rem;">
            <div class="form-group">
                <label for="heures_sommeil">Heures de sommeil 😴</label>
                <input id="heures_sommeil" name="heures_sommeil" class="form-control" placeholder="Ex: 7.5" step="0.5" value="<?php echo $isEdit ? htmlspecialchars($editJournal['heures_sommeil']) : ''; ?>">
            </div>
            <div class="form-group">
                <label for="humeur">Humeur du jour ✨</label>
                <select id="humeur" name="humeur" class="form-control">
                    <option value="excellent" <?php echo ($isEdit && $editJournal['humeur'] === 'excellent') ? 'selected' : ''; ?>>🤩 Excellent</option>
                    <option value="bien" <?php echo ($isEdit && $editJournal['humeur'] === 'bien') ? 'selected' : ''; ?>>😊 Bien</option>
                    <option value="neutre" <?php echo ($isEdit && $editJournal['humeur'] === 'neutre') ? 'selected' : ''; ?>>😐 Neutre</option>
                    <option value="fatigue" <?php echo ($isEdit && $editJournal['humeur'] === 'fatigue') ? 'selected' : ''; ?>>🥱 Fatigué(e)</option>
                    <option value="stresse" <?php echo ($isEdit && $editJournal['humeur'] === 'stresse') ? 'selected' : ''; ?>>😟 Stressé(e)</option>
                </select>
            </div>
        </div>

        <div class="form-actions" style="display: flex; justify-content: flex-end; gap: 1rem; margin-top: 2rem;">
            <button type="reset" class="btn-secondary">Effacer</button>
            <!-- Bouton pour ajouter un nouveau journal -->
            <button type="submit" id="journalAddBtn" class="btn-main">Enregistrer l'Entrée</button>

            <!-- Bouton pour enregistrer les modifications (caché par défaut) -->
            <?php $updateFormActionTemplate = $controllerUrl . '?action=update&id='; ?>
            <button type="submit" id="journalUpdateBtn" class="btn-main" style="display: <?php echo $isEdit ? 'inline-block' : 'none'; ?>; background:#111827; color:#fff;" formaction="<?php echo $isEdit ? ($updateFormActionTemplate . urlencode($editJournal['id_journal'])) : ''; ?>">Enregistrer les modifications</button>
        </div>
    </form>
</div>
</div>
</div>
            <div class="active-journal-bar" style="display: flex; justify-content: space-between; align-items: center; background: white; padding: 1rem 1.5rem; border-radius: 16px; box-shadow: var(--shadow); border-left: 4px solid var(--green-light);">
                <div class="journal-info-left" style="display: flex; align-items: center; gap: 1rem;">
                    <?php if ($latestJournal): ?>
                        <span class="date-badge" style="font-size: 1.1rem; font-weight: 600;">📅 <?php echo htmlspecialchars(formatDateFrLong($latestJournal['date_journal'])); ?></span>
                        <span class="badge white" style="border: 1px solid #e2e8f0; font-weight: 600;">⚖️ <?php echo htmlspecialchars($latestJournal['poids_actuel']); ?> kg</span>
                        <span class="badge green-light">Journal Actif</span>
                    <?php else: ?>
                        <span class="date-badge" style="font-size: 1.1rem; font-weight: 600;">Aucun journal</span>
                        <span class="badge white" style="border: 1px solid #e2e8f0; font-weight: 600;">—</span>
                        <span class="badge yellow-light">Créez un journal</span>
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
                            ✏️ Modifier
                        </button>
                        <a href="../../Controller/JournalController.php?action=delete&id=<?php echo urlencode($latestJournal['id_journal']); ?>" class="btn-icon danger" style="text-decoration: none; color: #e53e3e; border: 1px solid #feb2b2; padding: 5px 10px; border-radius: 8px;" onclick="return confirm('Voulez-vous vraiment supprimer ce journal et ses repas ?');">
                            🗑️ Supprimer
                        </a>
                    <?php else: ?>
                        <button class="btn-icon" disabled style="opacity:0.6;">✏️ Modifier</button>
                        <button class="btn-icon" disabled style="opacity:0.6;">🗑️ Supprimer</button>
                    <?php endif; ?>
                </div>
            </div>
        </header>

        <div class="journal-grid">
            
            <div class="card progress-card">
                <div class="card-header">
                    <h2>Total Calorique</h2>
                    <span class="badge yellow">Objectif : 2000 kcal</span>
                </div>
                <div class="progress-content">
                    <div class="circle-chart">
                        <div class="circle-inner">
                            <span class="percentage">75%</span>
                            <span class="label">Complété</span>
                        </div>
                    </div>
                    <div class="macros-summary">
                        <div class="macro-item"><span class="dot blue"></span> Glucides <span>150g / 200g</span></div>
                        <div class="macro-item"><span class="dot yellow"></span> Protéines <span>90g / 120g</span></div>
                        <div class="macro-item"><span class="dot green"></span> Lipides <span>45g / 65g</span></div>
                    </div>
                </div>
            </div>

            <div class="card health-card">
                <div class="card-header">
                    <h2>Score Nutritionnel</h2>
                </div>
                <div class="health-content">
                    <div class="score-display">
                        <span class="score-number">82%</span>
                        <span class="badge white">Très Équilibré</span>
                    </div>
                    <div class="progress-bar-container">
                        <div class="progress-bar" style="width: 82%;"></div>
                    </div>
                    <p class="encouragement">Continue comme ça pour atteindre ton objectif !</p>
                </div>
            </div>

            <div class="meals-section">
                <div class="section-header">
                    <h2>Mes Repas</h2>
                </div>

                <div class="meal-card">
                    <div class="meal-image">
                        <img src="https://images.unsplash.com/photo-1490645935967-10de6ba17061?auto=format&fit=crop&w=150&q=80" alt="Repas">
                    </div>
                    <div class="meal-info">
                        <div class="meal-tags">
                            <span class="badge green-light">Déjeuner</span>
                            <span class="time-info">12:30</span>
                        </div>
                        <h3>Salade Saumon & Quinoa</h3>
                        <p>Saumon au four, brocolis vapeur et quinoa.</p>
                    </div>
                    <div class="meal-stats">
                        <div class="stat-col">
                            <span class="stat-val">450 Cal</span>
                            <span class="stat-val">40g Glucides</span>
                        </div>
                        <div class="stat-col">
                            <span class="stat-val">35g Protéines</span>
                            <span class="stat-val">15g Lipides</span>
                        </div>
                        <button class="btn-action">+ Détails</button>
                    </div>
                </div>

               <div class="card" style="margin-top: 2rem; border: 2px solid var(--green-light);">
    <div class="card-header">
        <h2>Enregistrer un nouveau repas</h2>
    </div>
    
    <form id="repasForm" action="index.php?action=addRepas" method="POST" enctype="multipart/form-data" class="modern-form">
        
        <div class="form-row">
            <div class="form-group">
                <label for="type_repas">Type de repas</label>
                <select id="type_repas" name="type_repas" class="form-control">
                    <option value="Petit-Déjeuner">Petit-Déjeuner 🍳</option>
                    <option value="Déjeuner">Déjeuner 🍱</option>
                    <option value="Dîner">Dîner 🍲</option>
                    <option value="Collation">Collation 🍎</option>
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
                <label for="qte">Quantité (g ou portion)</label>
                <input id="qte" name="qte" class="form-control" placeholder="Ex: 150">
            </div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label for="repas_image">Photo du repas (optionnel)</label>
                <input type="file" id="repas_image" name="repas_image" accept="image/*" class="form-control">
            </div>
            <div class="form-group" style="display:flex;align-items:center;">
                <div id="repasImagePreview" style="max-width:120px;max-height:80px;border:1px dashed #e2e8f0;padding:6px;border-radius:6px;display:flex;align-items:center;justify-content:center;color:#94a3b8;">
                    Aperçu
                </div>
            </div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label for="nbre_calories">Calories (kcal)</label>
                <input id="nbre_calories" name="nbre_calories" class="form-control" placeholder="Ex: 250">
            </div>
            <div class="form-group">
                <label for="proteine">Protéines (g)</label>
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
            <button type="reset" class="btn-secondary">Effacer les champs</button>
            <button type="submit" class="btn-main">Ajouter au journal</button>
        </div>
    </form>
</div>

            </div>
        </div>
    </div>

    <script src="JavaScript_Front.js"></script>
    <script>
        // Fournit l'URL absolue du contrôleur au JS
        window.JOURNAL_CONTROLLER_URL = '<?php echo $controllerUrl; ?>';
    </script>

    <script>
        // Aperçu de l'image sélectionnée pour un repas
        (function(){
            var input = document.getElementById('repas_image');
            var preview = document.getElementById('repasImagePreview');
            if(!input || !preview) return;
            input.addEventListener('change', function(e){
                var file = this.files && this.files[0];
                if(!file){ preview.innerHTML = 'Aperçu'; return; }
                if(!file.type.startsWith('image/')){ preview.innerHTML = 'Fichier non image'; return; }
                var reader = new FileReader();
                reader.onload = function(ev){
                    preview.innerHTML = '';
                    var img = document.createElement('img');
                    img.src = ev.target.result;
                    img.style.maxWidth = '100%';
                    img.style.maxHeight = '100%';
                    img.style.display = 'block';
                    preview.appendChild(img);
                };
                reader.readAsDataURL(file);
            });
        })();
    </script>

</body>
</html>