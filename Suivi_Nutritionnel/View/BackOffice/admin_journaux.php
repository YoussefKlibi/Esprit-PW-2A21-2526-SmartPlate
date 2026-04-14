<?php
    // 1. Connexion et récupération des données
    // (Assure-toi d'avoir créé le JournalController et la méthode liste() dans ton modèle Journal)
    include_once '../../Controller/JournalController.php'; 
    
    // On récupère tous les journaux de la base de données
    $listeJournaux = Journal::liste(); 
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
            <span><img src="../../Logo/logo.png" alt="Logo" height="30" style="vertical-align: middle;"></span> SmartPlate Admin
        </div>
        <div class="sidebar-menu">
            <div class="menu-category">Menu Principal</div>
            <a href="admin_dashboard.php" class="menu-item">📊 Dashboard Analytics</a>
            <a href="admin_objectifs.php" class="menu-item">🎯 Modération Objectifs</a>
            <a href="admin_journaux.php" class="menu-item active">🍽️ Journaux Utilisateurs</a>
            
            <div class="menu-category" style="margin-top: 20px;">Système</div>
            <a href="#" class="menu-item">⚠️ Anomalies (3)</a>
        </div>
    </aside>

    <main class="main-content" style="padding: 20px; overflow-y: auto; width: 100%;">
        
        <header style="margin-bottom: 20px;">
            <h1>Administration des Journaux</h1>
            <p style="color: var(--text-gray);">Consultez et modérez les journaux de suivi quotidien des utilisateurs.</p>
        </header>

         <div id="sectionAjout" style="display: none; background-color: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 20px; margin-bottom: 20px; border-left: 4px solid #20c997;">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
        <h3 id="formTitle" style="color: #2d3748;">➕ Ajouter un Journal</h3>
    </div>
    
    <form id="adminJournalForm" action="../../Controller/JournalController.php?action=add" method="POST" class="modern-form">
        <div class="form-row">
            <div style="flex: 1;">
               <label for="id_utilisateur" style="display: block; margin-bottom: 5px; font-weight: 500; color: var(--admin-green-dark);">👤 ID Utilisateur</label>
                <input type="number" id="id_utilisateur" name="id_utilisateur" placeholder="Ex: 1" style="width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px;">
            </div>
           <div style="display: flex; gap: 15px; margin-bottom: 20px;">
                        <div style="flex: 1;">
                            <label for="Date" style="display: block; margin-bottom: 5px; font-weight: 500;">Date du journal</label>
                            <input type="date" id="Date_Journal" name="Date_Journal" style="width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px;">
                        </div>
</div>
        </div>
        
        <div class="form-row">
           <div style="flex: 1;">
                            <label for="poids_actuel" style="display: block; margin-bottom: 5px; font-weight: 500;">Poids actuel (kg)</label>
                            <input type="number" id="poids_cible" name="poids_cible" step="0.1" style="width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px;">
                        </div>
            <div style="flex: 1;">
                            <label for="Heure_Sommeil" style="display: block; margin-bottom: 5px; font-weight: 500;">Heures de Sommeil</label>
                            <input type="time" id="Heure_Sommeil" name="Sommeil" step="0.1" style="width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px;">
                        </div>
        </div>

        <div style="display: flex; gap: 10px;">
                        <button type="submit" style="background: var(--admin-green); color: white; padding: 8px 15px; border: none; border-radius: 4px; cursor: pointer; font-weight: 600;">💾 Enregistrer</button>
                        <button type="button" onclick="closeForm()" class="btn-action">Annuler</button>
                    </div>
    </form>
</div>

        <div class="card full-width-card" style="background: white; padding: 20px; border-radius: 8px; box-shadow: var(--shadow);">
            <div class="card-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                <h2>Historique des journaux</h2>
                
                <input type="text" id="searchId" placeholder="🔍 Rechercher par ID..." 
                       style="padding: 8px 15px; border-radius: 5px; border: 1px solid #ccc; width: 250px; font-family: inherit;">
                
                 <button onclick="openAddForm()" class="btn-main" style="background: var(--admin-green); color: white; padding: 8px 15px; border: none; border-radius: 5px; cursor: pointer;">+ Nouvel Objectif</button>
            </div>
            
            <table class="admin-table" style="width: 100%; text-align: left; border-collapse: collapse;">
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
                        <?php foreach($listeJournaux as $journal): ?>
                        <tr style="border-bottom: 1px solid #e2e8f0;">
                            <td style="padding: 10px;">#<?php echo $journal['id_journal']; ?></td>
                            <td style="padding: 10px;"><strong>Utilisateur #<?php echo $journal['id_utilisateur']; ?></strong></td>

                            <td style="padding: 10px;">
                                <span class="badge" style="background: #eef2f5; color: #2d3436; padding: 5px 10px; border-radius: 4px;">
                                    📅 <?php echo date("d/m/Y", strtotime($journal['date_journal'])); ?>
                                </span>
                            </td>

                            <td style="padding: 10px;">
                                <?php echo !empty($journal['poids_actuel']) ? $journal['poids_actuel'] . ' kg' : '<span style="color: #95a5a6; font-style: italic;">Non renseigné</span>'; ?>
                            </td>

                            <td style="padding: 10px;">
                                <?php echo !empty($journal['heures_sommeil']) ? htmlspecialchars($journal['heures_sommeil']) : '<span style="color: #95a5a6; font-style: italic;">—</span>'; ?>
                            </td>

                            <td style="padding: 10px;">
                                <?php echo !empty($journal['humeur']) ? htmlspecialchars($journal['humeur']) : '<span style="color: #95a5a6; font-style: italic;">—</span>'; ?>
                            </td>

                            <td style="padding: 10px;">
                                <a href="#" class="voir-repas" data-id="<?php echo $journal['id_journal']; ?>" style="text-decoration: none; margin-right: 10px; color: #3498db;">👁️ Voir Repas</a>
                                <a href="../../Controller/JournalController.php?action=delete&id=<?php echo $journal['id_journal']; ?>" style="color: red; text-decoration: none;" onclick="return confirm('Supprimer ce journal et tous les repas associés ?');">🗑️ Suppr.</a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" style="text-align: center; padding: 20px; color: #7f8c8d;">Aucun journal trouvé dans la base de données.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        
            <h1 style="margin-bottom: 20px;">Administration des Repas</h1>
            <p style="color: var(--text-gray);">Consultez et modérez les journaux de suivi quotidien des utilisateurs.</p>

        <div class="card full-width-card" style="background: white; padding: 20px; border-radius: 8px; box-shadow: var(--shadow);">
            <div class="card-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                <h2>Historique des journaux</h2>
                
                <input type="text" id="searchId" placeholder="🔍 Rechercher par ID..." 
                       style="padding: 8px 15px; border-radius: 5px; border: 1px solid #ccc; width: 250px; font-family: inherit;">
                
                <a href="form_ajout_journal.php" class="btn-main" style="background: var(--admin-green); color: white; padding: 8px 15px; text-decoration: none; border-radius: 5px;">+ Nouveau Repas</a>
            </div>
            
            <table class="admin-table" style="width: 100%; text-align: left; border-collapse: collapse;">
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
                         <th style="padding: 8px;">Proteines (g)</th>
                         <th style="padding: 8px;">Glucides (g)</th>
                         <th style="padding: 8px;">Lipides (g)</th>
                        <th style="padding: 8px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($listeJournaux)): ?>
                        <?php foreach($listeJournaux as $journal): ?>
                        <tr style="border-bottom: 1px solid #e2e8f0;">
                            <td style="padding: 10px;">-</td>
                            <td style="padding: 10px;"><strong>Utilisateur #<?php echo $journal['id_utilisateur']; ?></strong></td>
                            <td style="padding: 10px;"><span class="badge" style="background: #eef2f5; color: #2d3436; padding: 5px 10px; border-radius: 4px;">📅 <?php echo date("d/m/Y", strtotime($journal['date_journal'])); ?></span></td>
                            <td style="padding: 10px;">-</td>
                            <td style="padding: 10px;">-</td>
                            <td style="padding: 10px;">-</td>
                            <td style="padding: 10px;">-</td>
                            <td style="padding: 10px;"><?php echo isset($journal['total_calories']) ? $journal['total_calories'] : '-'; ?></td>
                            <td style="padding: 10px;">-</td>
                            <td style="padding: 10px;">-</td>
                            <td style="padding: 10px;">-</td>
                            <td style="padding: 10px;">
                                <a href="#" class="voir-repas" data-id="<?php echo $journal['id_journal']; ?>" style="text-decoration: none; margin-right: 10px; color: #3498db;">👁️ Voir Repas</a>
                                <a href="../../Controller/JournalController.php?action=delete&id=<?php echo $journal['id_journal']; ?>" style="color: red; text-decoration: none;" onclick="return confirm('Supprimer ce journal et tous les repas associés ?');">🗑️ Suppr.</a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" style="text-align: center; padding: 20px; color: #7f8c8d;">Aucun Repas trouvé dans la base de données.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </main>
    <script src="JavaScript.js"></script>
</body>
</html>