<?php
include '../../Controllers/ProfilController.php';
$profilC = new ProfilController();
$listeProfils = $profilC->listeProfils();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SmartPlate - Gestion des Profils</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../css/style.css">
    <style>
        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
            background: #ffffff;
            padding: 30px;
            border-radius: 16px;
            box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05);
        }
        .page-header h1 {
            color: #0f172a;
            margin: 0;
            font-size: 26px;
        }
        
        .table-container {
            background: #ffffff;
            border-radius: 16px;
            box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05);
            overflow: hidden;
            margin-bottom: 2rem;
            border: 1px solid #e2e8f0;
        }
        
        .modern-spaced-table {
            width: 100%;
            border-collapse: collapse;
            text-align: left;
        }
        .modern-spaced-table th {
            background: #f8fafc;
            color: #475569;
            font-size: 14px;
            font-weight: 600;
            padding: 20px 25px;
            letter-spacing: 0.5px;
            border-bottom: 2px solid #e2e8f0;
        }
        .modern-spaced-table td {
            padding: 25px;
            color: #1e293b;
            font-size: 15px;
            border-bottom: 1px solid #f1f5f9;
        }
        .modern-spaced-table tr:last-child td {
            border-bottom: none;
        }
        .modern-spaced-table tr:hover {
            background: #fdfdfd;
        }
        
        .info-pill {
            display: inline-block;
            background: #eef2ff;
            color: #4f46e5;
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            margin-left: 10px;
        }
        
        .action-links {
            display: flex;
            gap: 15px;
        }
        .btn-delete {
            background: #fef2f2;
            color: #ef4444;
            padding: 10px 16px;
            border-radius: 8px;
            font-weight: 600;
            font-size: 14px;
            text-decoration: none;
            transition: all 0.2s;
        }
        .btn-delete:hover {
            background: #ef4444;
            color: white;
            box-shadow: 0 4px 6px rgba(239,68,68,0.2);
        }

        .footer-nav {
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: white;
            padding: 20px 30px;
            border-radius: 12px;
            box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05);
        }
    </style>
</head>
<body style="background: #f8fafc; font-family: 'Inter', sans-serif; padding: 3rem 1rem;">
    <div style="max-width: 1100px; margin: 0 auto;">
        
        <!-- Header -->
        <div class="page-header">
            <div>
                <h1>Gestion des Profils</h1>
                <p style="color: #64748b; margin: 5px 0 0 0; font-size: 15px;">Administrez les différents profils nutritionnels associés aux utilisateurs.</p>
            </div>
            <a href="profil_create.php" class="btn-main" style="text-decoration: none; padding: 14px 24px; border-radius: 12px; box-shadow: 0 4px 6px rgba(79, 70, 229, 0.2);">
                <span>+</span> Ajouter un nouveau profil
            </a>
        </div>

        <!-- Table Content -->
        <div class="table-container">
            <?php if (count($listeProfils) > 0): ?>
                <table class="modern-spaced-table">
                    <thead>
                        <tr>
                            <th style="width: 5%">ID</th>
                            <th style="width: 25%">Titre du Profil</th>
                            <th style="width: 35%">Description Détaillée</th>
                            <th style="width: 25%">Utilisateur Rattaché</th>
                            <th style="width: 10%; text-align: center;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($listeProfils as $profil): ?>
                        <tr>
                            <td style="font-weight: 600; color: #94a3b8;">#<?= htmlspecialchars($profil['id']) ?></td>
                            <td>
                                <strong style="font-size: 16px; display: block; color: #0f172a;"><?= htmlspecialchars($profil['titre']) ?></strong>
                            </td>
                            <td style="line-height: 1.6; color: #475569;">
                                <?= htmlspecialchars($profil['description']) ?>
                            </td>
                            <td>
                                <div style="display: flex; align-items: center; gap: 12px;">
                                    <div style="width: 35px; height: 35px; background: #f1f5f9; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: 600; color: #64748b;">
                                        <?= strtoupper(substr($profil['prenom'], 0, 1) . substr($profil['nom'], 0, 1)) ?>
                                    </div>
                                    <div>
                                        <div style="font-weight: 600; color: #1e293b;"><?= htmlspecialchars($profil['prenom'] . ' ' . $profil['nom']) ?></div>
                                        <span class="info-pill">User ID: <?= htmlspecialchars($profil['id_utilisateur']) ?></span>
                                    </div>
                                </div>
                            </td>
                            <td style="text-align: center;">
                                <a href="profil_delete.php?id=<?= $profil['id'] ?>" class="btn-delete" title="Supprimer ce profil" onclick="return confirm('⚠️ Êtes-vous sûr de vouloir supprimer définitivement ce profil ?\nCette action est irréversible.');">
                                    Supprimer
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <div style="text-align: center; padding: 60px 20px;">
                    <div style="font-size: 40px; margin-bottom: 15px;">📭</div>
                    <h3 style="color: #1e293b; margin-bottom: 10px;">La liste est vide</h3>
                    <p style="color: #64748b;">Aucun profil n'a encore été créé dans la base de données.</p>
                </div>
            <?php endif; ?>
        </div>
        
        <!-- Footer Nav -->
        <div class="footer-nav">
            <a href="admin_dashboard.html" style="color: #64748b; text-decoration: none; font-weight: 500; display: flex; align-items: center; gap: 8px;">
                <span>←</span> Retour au tableau de bord
            </a>
            <a href="searchProfils.php" class="btn-secondary" style="text-decoration: none; font-weight: 600; padding: 12px 24px; border-radius: 10px;">
                🔍 Recherche Profil
            </a>
        </div>
        
    </div>
</body>
</html>
