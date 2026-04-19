<?php
    // 1. Connexion et récupération des données
    include_once '../../Controller/ObjectifController.php'; 
    $listeObjectifsTousUtilisateurs = Objectif::liste();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SmartPlate - Admin Objectifs</title>
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
            <a href="admin_objectifs.php" class="menu-item active">🎯 Modération Objectifs</a>
            <a href="admin_journaux.php" class="menu-item">🍽️ Journaux Utilisateurs</a>
        </div>
    </aside>

    <main class="main-content" style="padding: 20px; overflow-y: auto; width: 100%;">
        
        <header style="margin-bottom: 20px;">
            <h1>Administration des Objectifs</h1>
            <p style="color: var(--text-gray);">Gérez les objectifs de tous les utilisateurs de la plateforme.</p>
        </header>

        <div id="sectionAjout" style="display: none; margin-bottom: 25px;">
            <div class="card" style="background: white; padding: 20px; border-radius: 8px; box-shadow: var(--shadow); border-top: 4px solid var(--admin-green);">
                <h3 id="formTitle">➕ Ajouter un nouvel objectif</h3>
<form id="objectifForm" action="../../Controller/ObjectifController.php?action=add" method="POST">
                
                <form action="../../Controller/ObjectifController.php?action=add" method="POST">
                    <div style="display: flex; gap: 15px; margin-bottom: 15px;">
                        <div style="flex: 1;">
                            <label for="id_utilisateur" style="display: block; margin-bottom: 5px; font-weight: 500; color: var(--admin-green-dark);">👤 ID Utilisateur</label>
                            <input type="number" id="id_utilisateur" name="id_utilisateur" placeholder="Ex: 1" style="width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px;">
                        </div>
                        <div style="flex: 1;">
                            <label for="type" style="display: block; margin-bottom: 5px; font-weight: 500;">Type d'objectif</label>
                            <select id="type" name="type" style="width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px;">
                                <option value="perte_poids">Perte de poids</option>
                                <option value="maintien">Maintien</option>
                                <option value="prise_masse">Prise de masse</option>
                            </select>
                        </div>
                        <div style="flex: 1;">
                            <label for="poids_cible" style="display: block; margin-bottom: 5px; font-weight: 500;">Poids cible (kg)</label>
                            <input type="number" id="poids_cible" name="poids_cible" step="0.1" style="width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px;">
                        </div>
                    </div>

                    <div style="display: flex; gap: 15px; margin-bottom: 20px;">
                        <div style="flex: 1;">
                            <label for="Date_Debut" style="display: block; margin-bottom: 5px; font-weight: 500;">Date de début</label>
                            <input type="date" id="Date_Debut" name="Date_Debut" style="width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px;">
                        </div>
                        <div style="flex: 1;">
                            <label for="Date_Fin" style="display: block; margin-bottom: 5px; font-weight: 500;">Date de fin</label>
                            <input type="date" id="Date_Fin" name="Date_Fin" style="width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px;">
                        </div>
                        <div style="flex: 1;">
    <label for="statut" style="display: block; margin-bottom: 5px; font-weight: 500;">Statut</label>
    <select id="statut" name="statut" style="width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px;">
        <option value="en_cours">En cours</option>
        <option value="Atteint">Atteint</option>
        <option value="Abandonné">Abandonné</option>
    </select>
</div>
                    </div>

                    <div style="display: flex; gap: 10px;">
                        <button type="submit" style="background: var(--admin-green); color: white; padding: 8px 15px; border: none; border-radius: 4px; cursor: pointer; font-weight: 600;">💾 Enregistrer</button>
                        <button type="button" onclick="toggleForm()" class="btn-action">Annuler</button>
                    </div>
                </form>
            </div>
        </div>
        <div class="card full-width-card" style="background: white; padding: 20px; border-radius: 8px; box-shadow: var(--shadow);">
            <div class="card-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                <h2>Liste globale</h2>
                
                <input type="text" id="searchId" placeholder="🔍 Rechercher par ID..." 
                       style="padding: 8px 15px; border-radius: 5px; border: 1px solid #ccc; width: 250px; font-family: inherit;">
                
                <button onclick="toggleForm()" class="btn-main" style="background: var(--admin-green); color: white; padding: 8px 15px; border: none; border-radius: 5px; cursor: pointer;">+ Nouvel Objectif</button>
            </div>
            
            <table class="admin-table" style="width: 100%; text-align: left; border-collapse: collapse;">
                <thead>
    <tr>
        <th style="padding: 10px; text-align: left;">ID Objectif</th>
        <th style="padding: 10px; text-align: left;">Utilisateur</th>
        <th style="padding: 10px; text-align: left;">Type</th>
        <th style="padding: 10px; text-align: left;">Poids Cible</th>
        <th style="padding: 10px; text-align: left;">Date Début</th>
        <th style="padding: 10px; text-align: left;">Date Fin</th>
        <th style="padding: 10px; text-align: left;">Statut</th>
        <th style="padding: 10px; text-align: left;">Actions</th>
    </tr>
</thead>
                <tbody>
                    <?php foreach($listeObjectifsTousUtilisateurs as $obj): ?>
                    <tr style="border-bottom: 1px solid #eee;">
    <td style="padding: 10px;">#<?php echo $obj['id_objectif']; ?></td>
    <td style="padding: 10px;"><strong>Utilisateur #<?php echo $obj['id_utilisateur'] ?? 'Inconnu'; ?></strong></td>
    <td style="padding: 10px;"><?php echo $obj['type_objectif']; ?></td>
    <td style="padding: 10px;"><?php echo $obj['poids_cible']; ?> kg</td>
    
    <td style="padding: 10px; font-size: 0.9rem; color: #666;">
        <?php echo date('d/m/Y', strtotime($obj['date_debut'])); ?>
    </td>
    <td style="padding: 10px; font-size: 0.9rem; color: #666;">
        <?php echo $obj['date_fin'] ? date('d/m/Y', strtotime($obj['date_fin'])) : '---'; ?>
    </td>

    <td style="padding: 10px;"><span class="badge yellow"><?php echo $obj['statut']; ?></span></td>
    
    <td style="padding: 10px; display: flex; gap: 8px; align-items: center;">
         <button type="button" class="btn-action" 
                onclick="openEditForm('<?php echo $obj['id_objectif']; ?>', '<?php echo $obj['id_utilisateur']; ?>', '<?php echo $obj['type_objectif']; ?>', '<?php echo $obj['poids_cible']; ?>', '<?php echo $obj['date_debut']; ?>', '<?php echo $obj['date_fin']; ?>', '<?php echo $obj['statut']; ?>')">
            ✏️Editer
        </button>
        <a href="../../Controller/ObjectifController.php?action=delete&id=<?php echo $obj['id_objectif']; ?>" 
           style="color: white; text-decoration: none;" 
           class="btn-action btn-danger-outline" 
           onclick="return confirm('Supprimer cet objectif ?');">
           🗑️ Suppr.
        </a>
    </td>
</tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </main>
    <script src="JavaScript.js"></script>
</body>
</html>