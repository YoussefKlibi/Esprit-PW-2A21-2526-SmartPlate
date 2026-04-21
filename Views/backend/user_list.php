<?php
require_once __DIR__ . '/../../Controllers/UserController.php';

$userC = new UserController();
$users = $userC->listeUsers();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SmartPlate - Liste des Utilisateurs</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../css/style.css">
</head>
<body style="background: #f1f5f9; display: block; padding: 2rem;">
    <div class="container" style="max-width: 1200px; margin: 0 auto;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
            <h1 style="font-size: 1.8rem; font-weight: 600; color: #1e293b;">👥 Liste des Utilisateurs</h1>
            <a href="user_create.php" class="btn-main" style="text-decoration: none;">+ Ajouter un utilisateur</a>
        </div>
        
        <table class="users-table" style="width: 100%; border-collapse: collapse; background: white; border-radius: 12px; overflow: hidden; box-shadow: 0 2px 10px rgba(0,0,0,0.05);">
            <thead>
                <tr style="background: #f8fafc; border-bottom: 2px solid #e2e8f0;">
                    <th style="padding: 1rem; text-align: left; font-weight: 600;">ID</th>
                    <th style="padding: 1rem; text-align: left; font-weight: 600;">Prénom</th>
                    <th style="padding: 1rem; text-align: left; font-weight: 600;">Nom</th>
                    <th style="padding: 1rem; text-align: left; font-weight: 600;">Email</th>
                    <th style="padding: 1rem; text-align: left; font-weight: 600;">Date d'inscription</th>
                    <th style="padding: 1rem; text-align: left; font-weight: 600;">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($users)): ?>
                <tr>
                    <td colspan="6" style="text-align: center; padding: 3rem;">Aucun utilisateur inscrit pour le moment.</td>
                </tr>
                <?php else: ?>
                <?php foreach ($users as $user): ?>
                <tr style="border-bottom: 1px solid #e2e8f0;">
                    <td style="padding: 1rem;"><?= htmlspecialchars($user['id']) ?></td>
                    <td style="padding: 1rem;"><?= htmlspecialchars($user['prenom'] ?? '') ?></td>
                    <td style="padding: 1rem;"><?= htmlspecialchars($user['nom']) ?></td>
                    <td style="padding: 1rem;"><?= htmlspecialchars($user['email']) ?></td>
                    <td style="padding: 1rem;"><?= $user['created_at'] ?? 'N/A' ?></td>
                    <td style="padding: 1rem;">
                        <a href="user_update.php?id=<?= $user['id'] ?>" style="background: #3b82f6; color: white; padding: 0.4rem 0.8rem; border-radius: 6px; text-decoration: none; font-size: 0.85rem; margin-right: 0.5rem;">✏️ Modifier</a>
                        <a href="user_delete.php?id=<?= $user['id'] ?>" 
                           onclick="return confirm('Êtes-vous sûr de vouloir supprimer cet utilisateur ?')" 
                           style="background: #ef4444; color: white; padding: 0.4rem 0.8rem; border-radius: 6px; text-decoration: none; font-size: 0.85rem;">🗑️ Supprimer</a>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
        
        <a href="admin_dashboard.html" style="display: inline-block; margin-top: 1.5rem; color: #64748b; text-decoration: none;">← Retour au tableau de bord</a>
    </div>
</body>
</html>