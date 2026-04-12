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

        <div class="card full-width-card" style="background: white; padding: 20px; border-radius: 8px; box-shadow: var(--shadow);">
            <div class="card-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                <h2>Historique des journaux</h2>
                
                <input type="text" id="searchId" placeholder="🔍 Rechercher par ID..." 
                       style="padding: 8px 15px; border-radius: 5px; border: 1px solid #ccc; width: 250px; font-family: inherit;">
                
                <a href="form_ajout_journal.php" class="btn-main" style="background: var(--admin-green); color: white; padding: 8px 15px; text-decoration: none; border-radius: 5px;">+ Nouveau Journal</a>
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
                                <a href="admin_repas.php?id_journal=<?php echo $journal['id_journal']; ?>" style="text-decoration: none; margin-right: 10px; color: #3498db;">👁️ Voir Repas</a>
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
    </main>

    <script>
        document.getElementById('searchId').addEventListener('input', function() {
            let filter = this.value.toLowerCase().replace('#', ''); 
            let rows = document.querySelectorAll('.admin-table tbody tr');

            rows.forEach(row => {
                // On vérifie que la ligne n'est pas le message "Aucun journal trouvé"
                if (row.cells.length > 1) {
                    let idCell = row.cells[0].textContent.toLowerCase(); 
                    if (idCell.includes(filter)) {
                        row.style.display = ''; 
                    } else {
                        row.style.display = 'none'; 
                    }
                }
            });
        });
    </script>
</body>
</html>