<?php
include_once '../../Controller/JournalController.php';
require_once __DIR__ . '/../../Model/Repas_Class.php';

// Gestion du tri : Date par défaut
    $sort = isset($_GET['sort']) ? $_GET['sort'] : 'date';
    $listeJournaux = Journal::trier($sort);

    // 2. NOUVEAU : Tri des Repas (2ème tableau)
    $sort_repas = isset($_GET['sort_repas']) ? $_GET['sort_repas'] : 'date';
    $listeRepas = Repas::trierTousLesRepas($sort_repas);

?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SmartPlate - Admin Journaux</title>
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
            <a href="admin_dashboard.php" class="menu-item">📊 Dashboard Analytics</a>
            <a href="admin_objectifs.php" class="menu-item">🎯 Modération Objectifs</a>
            <a href="admin_journaux.php" class="menu-item active">🍽️ Journaux Utilisateurs</a>
        </div>
    </aside>
    

    <main class="main-content">
        <header class="topbar">
            <div class="search-bar"></div>
            <div class="admin-profile">
                <span>Youssef</span>
                <img src="https://ui-avatars.com/api/?name=Admin&background=20c997&color=fff" alt="Profile">
            </div>
        </header>

        <header style="gap: 8px;">
            <h2>Administration des Journaux</h2>
            <p style="color: var(--text-gray);">Consultez et modérez les journaux de suivi quotidien des utilisateurs.</p>
        </header>

        <div id="sectionAjout" style="display: none; background-color: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 20px; margin-bottom: 20px; border-left: 4px solid #20c997;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
                <h3 id="formTitle" style="color: #2d3748;">• Ajouter un Journal</h3>
            </div>

            <form id="adminJournalForm" action="../../Controller/JournalController.php?action=add" method="POST" class="modern-form">
                <input type="hidden" name="admin_force" value="1">
                <div class="form-row">
                    <div style="flex: 1;">
                        <label for="id_utilisateur" style="display: block; margin-bottom: 5px; font-weight: 500; color: var(--admin-green-dark);">ID Utilisateur</label>
                        <input id="id_utilisateur" name="id_utilisateur" placeholder="Ex: 1" style="width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px;">
                    </div>
                    <div style="flex: 1;">
                        <label for="date_journal" style="display: block; margin-bottom: 5px; font-weight: 500;">Date du journal</label>
                        <input type="date" id="date_journal" name="date_journal" style="width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px;">
                    </div>
                </div>

                <div class="form-row">
                    <div style="flex: 1;">
                        <label for="id_objectif" style="display: block; margin-bottom: 5px; font-weight: 500;">ID Objectif</label>
                        <input id="id_objectif" name="id_objectif" placeholder="Ex: 12" style="width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px;">
                    </div>
                </div>

                <div class="form-row">
                    <div style="flex: 1;">
                        <label for="poids_actuel" style="display: block; margin-bottom: 5px; font-weight: 500;">Poids actuel (kg)</label>
                        <input id="poids_actuel" name="poids_actuel" step="0.1" style="width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px;">
                    </div>
                    <div style="flex: 1;">
                        <label for="Heure_Sommeil" style="display: block; margin-bottom: 5px; font-weight: 500;">Heures de Sommeil</label>
                        <input id="Heure_Sommeil" name="heures_sommeil" step="0.1" style="width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px;">
                    </div>
                </div>

                <div class="form-row">
                    <div style="flex: 1;">
                        <label for="humeur" style="display: block; margin-bottom: 5px; font-weight: 500;">Humeur</label>
                        <select id="humeur" name="humeur" style="width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px; background: white;">
                            <option value="">-- Sélectionner --</option>
                            <option value="excellent">Excellent</option>
                            <option value="bien">🙂 Bien</option>
                            <option value="neutre">😐 Neutre</option>
                            <option value="fatigue">😴 Fatigué(e)</option>
                            <option value="stresse">😰 Stressé(e)</option>
                        </select>
                    </div>
                </div>

                <div style="display: flex; gap: 10px;">
                    <button type="submit" id="adminSaveBtn" style="background: var(--admin-green); color: white; padding: 8px 15px; border: none; border-radius: 4px; cursor: pointer; font-weight: 600;">Enregistrer</button>
                    <button type="button" onclick="closeJournalForm()" class="btn-action">Annuler</button>
                </div>
            </form>
        </div>

        <div class="card full-width-card" style="background: white; padding: 20px; border-radius: 8px; box-shadow: var(--shadow);">
            <div class="card-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                <h2>Historique des journaux</h2>
                <div style="display: flex; gap: 15px; align-items: center;">
                    <input type="text" id="searchJournalId" placeholder="🔍 Rechercher par ID..."
                    style="padding: 8px 15px; border-radius: 5px; border: 1px solid #ccc; width: 250px; font-family: inherit;">
                <button type="button" onclick="openAddForm()" class="btn-main" style="background: var(--admin-green); color: white; padding: 8px 15px; border: none; border-radius: 5px; cursor: pointer;">+ Nouveau Journal</button>
                <form method="GET" action="admin_journaux.php" style="margin: 0;">
                    <label style="font-weight: 600; margin-right: 8px;">Trier par :</label>
                    <select name="sort" onchange="this.form.submit()" style="padding: 8px 12px; border-radius: 6px; border: 1px solid #ddd; background: white; cursor: pointer;">
                        <option value="date" <?php echo ($sort == 'date') ? 'selected' : ''; ?>>📅 Date (Récent)</option>
                        <option value="poids" <?php echo ($sort == 'poids') ? 'selected' : ''; ?>>⚖️ Poids saisi (Max)</option>
                    </select>
                </form>
            </div>
            </div>

            <table id="journauxTable" class="admin-table" style="width: 100%; text-align: left; border-collapse: collapse;">
                <thead>
                    <tr style="border-bottom: 2px solid #e2e8f0;">
                        <th style="padding: 10px;">ID Journal</th>
                        <th style="padding: 10px;">Utilisateur</th>
                        <th style="padding: 10px;">Date du Suivi</th>
                        <th style="padding: 10px;">Poids Saisi</th>
                        <th style="padding: 10px;">Heure Sommeil</th>
                        <th style="padding: 10px;">Humeur</th>
                        <th style="padding: 10px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($listeJournaux)): ?>
                        <?php foreach ($listeJournaux as $journal): ?>
                        <tr style="border-bottom: 1px solid #e2e8f0;">
                            <td style="padding: 10px;">#<?php echo htmlspecialchars($journal['id_journal']); ?></td>
                            <td style="padding: 10px;"><strong>Utilisateur #<?php echo htmlspecialchars($journal['id_utilisateur']); ?></strong></td>
                            <td style="padding: 10px;">
                                <span class="badge" style="background: #eef2f5; color: #2d3436; padding: 5px 10px; border-radius: 4px;">
                                    🔍 <?php echo date('d/m/Y', strtotime($journal['date_journal'])); ?>
                                </span>
                            </td>
                            <td style="padding: 10px;">
                                <?php echo !empty($journal['poids_actuel']) ? htmlspecialchars($journal['poids_actuel']) . ' kg' : '<span style="color: #95a5a6; font-style: italic;">Non renseigné</span>'; ?>
                            </td>
                            <td style="padding: 10px;">
                                <?php echo !empty($journal['heures_sommeil']) ? htmlspecialchars($journal['heures_sommeil']) : '<span style="color: #95a5a6; font-style: italic;">—</span>'; ?>
                            </td>
                            <td style="padding: 10px;">
                                <?php echo !empty($journal['humeur']) ? htmlspecialchars($journal['humeur']) : '<span style="color: #95a5a6; font-style: italic;">—</span>'; ?>
                            </td>
                            <td style="padding: 10px; display:flex; gap:8px; align-items:center; flex-wrap:wrap;">
                                <button type="button" class="btn-action" style="background:#3498db; color:white; padding:6px 10px; border-radius:6px; border:none;"
                                    data-id="<?php echo htmlspecialchars($journal['id_journal']); ?>"
                                    data-user="<?php echo htmlspecialchars($journal['id_utilisateur']); ?>"
                                    data-date="<?php echo htmlspecialchars($journal['date_journal']); ?>"
                                    data-poids="<?php echo htmlspecialchars($journal['poids_actuel'] ?? ''); ?>"
                                    data-heures="<?php echo htmlspecialchars($journal['heures_sommeil'] ?? ''); ?>"
                                    data-humeur="<?php echo htmlspecialchars($journal['humeur'] ?? ''); ?>"
                                    onclick="openJournalEdit(this)">Éditer</button>
                                <a href="#" class="voir-repas" data-id="<?php echo htmlspecialchars($journal['id_journal']); ?>" data-user="<?php echo htmlspecialchars($journal['id_utilisateur']); ?>" style="text-decoration: none; color: #3498db;">👀 Voir Repas</a>
                                <a href="../../Controller/JournalController.php?action=delete&id=<?php echo urlencode($journal['id_journal']); ?>" style="color: red; text-decoration: none;" onclick="return confirm('Supprimer ce journal et tous les repas associés ?');">🗑️ Suppr.</a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" style="text-align: center; padding: 20px; color: #7f8c8d;">Aucun journal trouvé dans la base de données.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <div id="sectionAjoutRepas" class="card admin-soft-card" style="display: none; margin-top: 24px;">
            <div class="card-header" style="display:flex; justify-content:space-between; align-items:center;">
                <h3 id="formRepasTitle">Ajouter un nouveau repas</h3>
                <button type="button" onclick="closeAddRepasForm()" class="btn-secondary">Annuler</button>
            </div>
            <div class="card-body" style="padding-top:12px;">
                <form id="adminRepasForm" action="../../Controller/RepasController.php?action=add" method="POST" enctype="multipart/form-data" class="modern-form">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="id_journal">Journal cible</label>
                            <select id="id_journal" name="id_journal" class="form-control">
                                <?php foreach ($listeJournaux as $j): ?>
                                    <option value="<?php echo htmlspecialchars($j['id_journal']); ?>">#<?php echo htmlspecialchars($j['id_journal']); ?> - Utilisateur #<?php echo htmlspecialchars($j['id_utilisateur']); ?> (<?php echo date('d/m/Y', strtotime($j['date_journal'])); ?>)</option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="type_repas">Type de repas</label>
                            <select id="type_repas" name="type_repas" class="form-control">
                                <option>Petit-Dejeuner</option>
                                <option>Dejeuner</option>
                                <option>Diner</option>
                                <option>Collation</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="heure_repas">Heure du repas</label>
                            <input type="time" id="heure_repas" name="heure_repas" class="form-control">
                        </div>
                        <div class="form-group form-group-wide">
                            <label for="nom">Nom du repas</label>
                            <input id="nom" type="text" name="nom" class="form-control" placeholder="Ex: Blanc de poulet">
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="quantite">Quantite (g)</label>
                            <input id="quantite" name="quantite" class="form-control" placeholder="Ex: 150">
                        </div>
                        <div class="form-group form-group-wide">
                            <label for="repas_image">Photo du repas (optionnel)</label>
                            <input id="repas_image" type="file" name="repas_image" accept="image/*" class="form-control file-input-control">
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="nbre_calories">Calories (kcal)</label>
                            <input id="nbre_calories"  step="0.1" name="nbre_calories" class="form-control" placeholder="Ex: 250">
                        </div>
                        <div class="form-group">
                            <label for="proteine">Proteines (g)</label>
                            <input id="proteine" step="0.1" name="proteine" class="form-control" placeholder="Ex: 30">
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="glucide">Glucides (g)</label>
                            <input id="glucide" step="0.1" name="glucide" class="form-control" placeholder="Ex: 20">
                        </div>
                        <div class="form-group">
                            <label for="lipide">Lipides (g)</label>
                            <input id="lipide" step="0.1" name="lipide" class="form-control" placeholder="Ex: 5">
                        </div>
                    </div>

                    <div class="form-actions">
                        <button type="reset" class="btn-secondary">Effacer</button>
                        <button id="btnSubmitRepas" type="submit" class="btn-main">Enregistrer le repas</button>
                    </div>
                </form>
            </div>
        </div>

        <h2 style="margin: 28px 0 8px;">Administration des Repas</h2>
        <p style="color: var(--text-gray);">Consultez et modérez les repas des utilisateurs.</p>

        <div class="card full-width-card" style="background: white; padding: 20px; border-radius: 8px; box-shadow: var(--shadow);">
            <div class="card-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                <h2>Historique des repas</h2>
                <input type="text" id="searchRepasId" placeholder="🔍 Rechercher par ID..."
                    style="padding: 8px 15px; border-radius: 5px; border: 1px solid #ccc; width: 250px; font-family: inherit;">
                    <form method="GET" action="admin_journaux.php" style="margin: 0; display: flex; align-items: center; gap: 10px;">
                <input type="hidden" name="sort" value="<?php echo htmlspecialchars($sort); ?>">
                
                <label style="font-weight: 600;">Trier repas par :</label>
                <select name="sort_repas" onchange="this.form.submit()" style="padding: 8px 12px; border-radius: 6px; border: 1px solid #ddd; background: #fff; cursor: pointer;">
                    <option value="date" <?php echo ($sort_repas == 'date') ? 'selected' : ''; ?>>📅 Date du journal</option>
                    <option value="quantite" <?php echo ($sort_repas == 'quantite') ? 'selected' : ''; ?>>⚖️ Quantité (Plus élevée)</option>
                </select>
            </form>
                <div style="display:flex; gap:8px; align-items:center;">
                    <button type="button" id="btnShowAllRepas" onclick="showAllRepas()" class="btn-secondary" style="display:none; background:#f1f5f9; color:#111; padding:8px 12px; border:1px solid #e2e8f0; border-radius:5px;">Afficher tous les repas</button>
                    <button type="button" onclick="openAddRepasForm()" class="btn-main" style="background: var(--admin-green); color: white; padding: 8px 15px; border: none; border-radius: 5px; cursor: pointer;">+ Nouveau Repas</button>
                </div>
            </div>

            <table id="repasTable" class="admin-table" style="width: 100%; text-align: left; border-collapse: collapse;">
                <thead>
                    <tr style="border-bottom: 2px solid #e2e8f0;">
                        <th style="padding: 8px;">ID Repas</th>
                        <th style="padding: 8px;">Utilisateur</th>
                        <th style="padding: 8px;">Journal</th>
                        <th style="padding: 8px;">Nom Repas</th>
                        <th style="padding: 8px;">Heure</th>
                        <th style="padding: 8px;">Type Repas</th>
                        <th style="padding: 8px;">Quantité</th>
                        <th style="padding: 8px;">Calories (kcal)</th>
                        <th style="padding: 8px;">Protéines (g)</th>
                        <th style="padding: 8px;">Glucides (g)</th>
                        <th style="padding: 8px;">Lipides (g)</th>
                        <th style="padding: 8px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($listeRepas)): ?>
                        <?php foreach ($listeRepas as $repas): ?>
                        <tr style="border-bottom: 1px solid #e2e8f0;" data-id_journal="<?php echo htmlspecialchars($repas['id_journal'] ?? ''); ?>" data-user="<?php echo htmlspecialchars($repas['id_utilisateur'] ?? ''); ?>">
                            <td style="padding: 10px;">#<?php echo htmlspecialchars($repas['id_repas']); ?></td>
                            <td style="padding: 10px;"><strong>Utilisateur #<?php echo htmlspecialchars($repas['id_utilisateur']); ?></strong></td>
                            <td style="padding: 10px;"><span class="badge" style="background: #eef2f5; color: #2d3436; padding: 5px 10px; border-radius: 4px;"> <?php echo date('d/m/Y', strtotime($repas['date_journal'])); ?></span></td>
                            <td style="padding: 10px;"><?php echo htmlspecialchars($repas['nom'] ?? '-'); ?></td>
                            <td style="padding: 10px;"><?php echo htmlspecialchars($repas['heure_repas'] ?? '-'); ?></td>
                            <td style="padding: 10px;"><?php echo htmlspecialchars($repas['type_repas'] ?? '-'); ?></td>
                            <td style="padding: 10px;"><?php echo htmlspecialchars($repas['quantite'] ?? '-'); ?></td>
                            <td style="padding: 10px;"><?php echo htmlspecialchars($repas['nbre_calories'] ?? '-'); ?></td>
                            <td style="padding: 10px;"><?php echo htmlspecialchars($repas['proteine'] ?? '-'); ?></td>
                            <td style="padding: 10px;"><?php echo htmlspecialchars($repas['glucide'] ?? '-'); ?></td>
                            <td style="padding: 10px;"><?php echo htmlspecialchars($repas['lipide'] ?? '-'); ?></td>
                            <td style="padding: 10px;">
                                <button type="button" class="btn-action" style="background:#3498db; color:white; padding:6px 10px; border-radius:6px; border:none; margin-right:8px;"
                                    data-id="<?php echo htmlspecialchars($repas['id_repas']); ?>"
                                    data-id_journal="<?php echo htmlspecialchars($repas['id_journal'] ?? ''); ?>"
                                    data-type="<?php echo htmlspecialchars($repas['type_repas'] ?? ''); ?>"
                                    data-heure="<?php echo htmlspecialchars($repas['heure_repas'] ?? ''); ?>"
                                    data-nom="<?php echo htmlspecialchars($repas['nom'] ?? ''); ?>"
                                    data-quantite="<?php echo htmlspecialchars($repas['quantite'] ?? ''); ?>"
                                    data-calories="<?php echo htmlspecialchars($repas['nbre_calories'] ?? ''); ?>"
                                    data-proteine="<?php echo htmlspecialchars($repas['proteine'] ?? ''); ?>"
                                    data-glucide="<?php echo htmlspecialchars($repas['glucide'] ?? ''); ?>"
                                    data-lipide="<?php echo htmlspecialchars($repas['lipide'] ?? ''); ?>"
                                    onclick="openRepasEdit(this)">Éditer</button>
                                <a href="../../Controller/RepasController.php?action=delete&id=<?php echo urlencode($repas['id_repas']); ?>" style="color: red; text-decoration: none;" onclick="return confirm('Supprimer ce repas ?');">Suppr.</a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="12" style="text-align: center; padding: 20px; color: #7f8c8d;">Aucun repas trouvé dans la base de données.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </main>
    <script src="JavaScript.js"></script>
</body>
</html>
