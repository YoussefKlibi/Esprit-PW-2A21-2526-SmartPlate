<?php
session_start();
if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
    header("Location: ../frontend/login.php");
    exit();
}

require_once __DIR__ . '/../../Controllers/UserController.php';

if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: user_list.php");
    exit();
}

$userId = (int) $_GET['id'];
$userC = new UserController();
$user = $userC->getUserById($userId);

if (!$user) {
    header("Location: user_list.php");
    exit();
}

// Récupérer l'historique complet (jusqu'à 50 dernières connexions)
$loginHistory = $userC->getLoginHistory($userId, 50);

?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Historique des connexions - <?= htmlspecialchars($user['prenom'] . ' ' . $user['nom']) ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../css/Template_BackOffice.css">
    <style>
        .history-card {
            background: #fff;
            border-radius: 16px;
            padding: 24px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.05);
            margin-top: 20px;
        }
        .table-header {
            background: #f8fafc;
            color: #64748b;
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.85rem;
            letter-spacing: 0.5px;
        }
        .table-row {
            border-bottom: 1px solid #f1f5f9;
        }
        .table-row:hover {
            background: #f8fafc;
        }
        .badge-success { background: #dcfce7; color: #166534; padding: 4px 10px; border-radius: 9999px; font-size: 0.75rem; font-weight: 600; }
        .badge-fail { background: #fee2e2; color: #991b1b; padding: 4px 10px; border-radius: 9999px; font-size: 0.75rem; font-weight: 600; }
    </style>
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
            <a href="../frontend/logout.php" class="menu-item" style="color: #ff6b6b;">🚪 Déconnexion</a>
        </div>
    </aside>

    <main class="main-content">
        <div class="dashboard-container">
            <div class="page-header" style="margin-bottom: 10px;">
                <div>
                    <h1 style="display:flex; align-items:center; gap:10px;">
                        <a href="user_list.php" style="text-decoration:none; color:#64748b; font-size:1.5rem;">←</a> 
                        Historique des connexions
                    </h1>
                    <p>Journal d'activité de l'utilisateur <strong><?= htmlspecialchars($user['prenom'] . ' ' . $user['nom']) ?></strong> (<?= htmlspecialchars($user['email']) ?>)</p>
                </div>
            </div>

            <div class="history-card">
                <?php if (empty($loginHistory)): ?>
                    <div style="padding: 3rem; text-align: center; color: #64748b; border: 1px dashed #cbd5e1; border-radius: 12px;">
                        <div style="font-size: 2rem; margin-bottom: 10px;">📭</div>
                        Aucun historique de connexion enregistré pour cet utilisateur.
                    </div>
                <?php else: ?>
                    <table style="width: 100%; border-collapse: collapse; text-align: left;">
                        <thead>
                            <tr class="table-header">
                                <th style="padding: 14px 16px;">Date & Heure</th>
                                <th style="padding: 14px 16px;">Lieu de connexion (IP)</th>
                                <th style="padding: 14px 16px;">Adresse IP</th>
                                <th style="padding: 14px 16px;">Navigateur / Appareil</th>
                                <th style="padding: 14px 16px;">Statut</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($loginHistory as $log): ?>
                                <tr class="table-row">
                                    <td style="padding: 16px; color: #334155; font-weight: 500;">
                                        <?= date('d/m/Y H:i:s', strtotime($log['login_time'])) ?>
                                    </td>
                                    <td style="padding: 16px; color: #334155;">
                                        <?php if ($log['city'] && $log['country']): ?>
                                            📍 <?= htmlspecialchars($log['city'] . ', ' . $log['country']) ?>
                                            <?php if ($log['latitude'] && $log['longitude']): ?>
                                                <br><a href="https://www.openstreetmap.org/?mlat=<?= urlencode($log['latitude']) ?>&mlon=<?= urlencode($log['longitude']) ?>#map=16" target="_blank" style="font-size: 0.8rem; color: #3b82f6; text-decoration: none;">Voir sur la carte ⍈</a>
                                            <?php endif; ?>
                                        <?php else: ?>
                                            <span style="color: #94a3b8; font-style: italic;">Inconnu (IP Locale ou API bloquée)</span>
                                        <?php endif; ?>
                                    </td>
                                    <td style="padding: 16px; color: #64748b; font-family: monospace;">
                                        <?= htmlspecialchars($log['ip_address']) ?>
                                    </td>
                                    <td style="padding: 16px; color: #64748b; max-width: 200px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;" title="<?= htmlspecialchars($log['device_info']) ?>">
                                        <?php
                                            $ua = $log['device_info'];
                                            if (strpos($ua, 'Chrome') !== false) echo 'Chrome';
                                            elseif (strpos($ua, 'Firefox') !== false) echo 'Firefox';
                                            elseif (strpos($ua, 'Safari') !== false) echo 'Safari';
                                            elseif (strpos($ua, 'Edge') !== false) echo 'Edge';
                                            else echo 'Autre';
                                            
                                            if (strpos($ua, 'Mobile') !== false) echo ' (Mobile)';
                                            else echo ' (PC)';
                                        ?>
                                    </td>
                                    <td style="padding: 16px;">
                                        <?php if ($log['status'] === 'Success'): ?>
                                            <span class="badge-success">Réussie</span>
                                        <?php else: ?>
                                            <span class="badge-fail">Échouée</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
        </div>
    </main>

</body>
</html>
