<?php
session_start();
if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
    header("Location: ../frontend/login.php");
    exit();
}

// Empêcher la mise en cache par le navigateur
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

require_once __DIR__ . '/../../Controllers/UserController.php';

$userC = new UserController();

// Mettre à jour l'activité de l'admin
$userC->updateLastActivity($_SESSION['user_email']);

$search = isset($_GET['q']) ? trim($_GET['q']) : '';
$users = $userC->listeUsers($search);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SmartPlate - Admin Utilisateurs</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../css/Template_BackOffice.css">
</head>
<body>

    <aside class="admin-sidebar">
        <div class="sidebar-header">
            <span>
                <img src="../xpdf/logo.jpg" alt="SmartPlate Logo" style="width:30px; height:30px; object-fit:cover; border-radius:8px; display:block;">
            </span> SmartPlate
        </div>
        <div class="sidebar-menu">
            <div class="menu-category">Menu Principal</div>
            <a href="#" class="menu-item">📊 Vue d'ensemble</a>
            <a href="user_list.php" class="menu-item active">👥 Utilisateurs & Logins</a>
            <a href="#" class="menu-item">🎯 Modération Objectifs</a>
            <a href="#" class="menu-item">🍽️ Journaux Utilisateurs</a>

            <div class="menu-category" style="margin-top: 20px;">Système</div>
            <a href="#" class="menu-item">⚠️ Anomalies (3)</a>
            <a href="#" class="menu-item" style="margin-top: auto;">⚙️ Paramètres du site</a>
            <a href="../frontend/logout.php" class="menu-item" style="color: #ff6b6b;" onclick="return confirm('Voulez-vous vraiment vous déconnecter ?');">🚪 Déconnexion</a>
        </div>
    </aside>

    <main class="main-content">

        <header class="topbar">
            <div class="search-bar">
                <form method="GET" action="user_list.php" style="display: flex; align-items: center; gap: 8px;">
                    <input type="text" name="q" value="<?= htmlspecialchars($search) ?>" placeholder="Rechercher un utilisateur (email, ID, nom)..."
                        style="padding: 8px 15px; border: 1px solid #eef0f5; border-radius: 20px; width: 300px; outline: none;">
                    <button type="submit" style="padding: 8px 14px; border: none; border-radius: 20px; background: #20c997; color: #fff; cursor: pointer; font-weight: 600;">Rechercher</button>
                    <?php if ($search !== ''): ?>
                        <a href="user_list.php" style="padding: 8px 14px; border-radius: 20px; background: #eef0f5; color: #334155; text-decoration: none; font-weight: 600;">Reset</a>
                    <?php endif; ?>
                </form>
            </div>
            <div class="admin-profile">
                <span>Ilyes (Admin)</span>
                <img src="https://ui-avatars.com/api/?name=Ilyes+Gaied&background=20c997&color=fff" alt="Profile">
            </div>
        </header>

        <div class="dashboard-container">

            <div class="page-header">
                <div>
                    <h1>Gestion des Utilisateurs</h1>
                    <p>Liste complète des utilisateurs inscrits sur la plateforme.</p>
                </div>
                <a href="user_create.php" class="btn-action" style="padding: 10px 20px; font-size: 0.9rem; font-weight: 600; text-decoration: none;">+ Ajouter un utilisateur</a>
            </div>

            <div class="content-grid" style="display: block;">
                <div class="card">
                    <div class="card-header">
                        <h2>Liste des Utilisateurs</h2>
                    </div>

                    <div class="users-table-container">
                        <table class="users-table" style="width: 100%;">
                            <thead>
                                <tr>
                                    <th style="text-align: left;">ID</th>
                                    <th style="text-align: left;">Utilisateur</th>
                                    <th style="text-align: left;">Email</th>
                                    <th style="text-align: left;">Date d'inscription</th>
                                    <th style="text-align: left;">Statut</th>
                                    <th style="text-align: left;">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($users)): ?>
                                <tr>
                                    <td colspan="6" style="text-align: center; padding: 3rem; color: var(--text-gray);">Aucun utilisateur inscrit pour le moment.</td>
                                </tr>
                                <?php else: ?>
                                <?php foreach ($users as $user): ?>
                                <tr>
                                    <td><?= htmlspecialchars($user['id']) ?></td>
                                    <td>
                                        <div class="user-cell" style="display: flex; align-items: center; gap: 10px;">
                                            <img src="https://ui-avatars.com/api/?name=<?= urlencode(($user['prenom'] ?? '') . ' ' . $user['nom']) ?>&background=random"
                                                class="user-avatar" style="width: 40px; height: 40px; border-radius: 50%;" alt="Avatar">
                                            <div class="user-name-col" style="display: flex; flex-direction: column;">
                                                <strong style="color: var(--text-dark);"><?= htmlspecialchars(($user['prenom'] ?? '') . ' ' . $user['nom']) ?></strong>
                                            </div>
                                        </div>
                                    </td>
                                    <td style="color: var(--text-gray);"><?= htmlspecialchars($user['email']) ?></td>
                                    <td style="color: var(--text-gray);"><?= $user['created_at'] ?? 'N/A' ?></td>
                                    <td>
                                        <?php 
                                        $statut_db = strtolower(trim($user['statut'] ?? 'inactif'));
                                        
                                        if ($statut_db === 'banni') {
                                            $badgeClass = 'background: #feebeb; color: #e74c3c;';
                                            $badgeText = 'Banni';
                                        } elseif ($statut_db === 'actif') {
                                            $badgeClass = 'background: #e8f8f5; color: #20c997;';
                                            $badgeText = 'Actif';
                                        } else {
                                            $badgeClass = 'background: #f1f5f9; color: #64748b;';
                                            $badgeText = 'Inactif';
                                        }
                                        ?>
                                        <span style="padding: 4px 8px; border-radius: 4px; font-size: 0.8rem; font-weight: 600; <?= $badgeClass ?>"><?= htmlspecialchars($badgeText) ?></span>
                                    </td>
                                    <td>
                                        <a href="user_update.php?id=<?= $user['id'] ?>" style="background: #3498db; color: white; padding: 6px 12px; border-radius: 6px; text-decoration: none; font-size: 0.85rem; margin-right: 5px;">Modifier</a>
                                        
                                        <?php if ($user['email'] !== 'ilyesgaied32@gmail.com'): ?>
                                            <?php if ($statut_db === 'banni'): ?>
                                                <a href="user_ban.php?id=<?= $user['id'] ?>&action=unban" 
                                                   onclick="return confirm('Êtes-vous sûr de vouloir débannir cet utilisateur ?')" 
                                                   style="background: #10b981; color: white; padding: 6px 12px; border-radius: 6px; text-decoration: none; font-size: 0.85rem; margin-right: 5px;">Débannir</a>
                                            <?php else: ?>
                                                <a href="user_ban.php?id=<?= $user['id'] ?>&action=ban" 
                                                   onclick="return confirm('Êtes-vous sûr de vouloir bannir cet utilisateur ?')" 
                                                   style="background: #e74c3c; color: white; padding: 6px 12px; border-radius: 6px; text-decoration: none; font-size: 0.85rem; margin-right: 5px;">Bannir</a>
                                            <?php endif; ?>
                                        <?php endif; ?>

                                        <a href="user_delete.php?id=<?= $user['id'] ?>"  
                                           onclick="return confirm('Êtes-vous sûr de vouloir supprimer cet utilisateur ?')" 
                                           style="background: #e74c3c; color: white; padding: 6px 12px; border-radius: 6px; text-decoration: none; font-size: 0.85rem;">Supprimer</a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <!-- Intégration du Chatbot Assistant -->
    <?php include __DIR__ . '/../frontend/chatbot.php'; ?>
</body>
</html>
