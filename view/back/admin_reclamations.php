<?php
require_once __DIR__ . '/../../config/auth.php';
requireAuthentication('../login.php');
requireRole('admin', '../index.php');

require_once __DIR__ . '/../../controller/ReclamationController.php';
require_once __DIR__ . '/../../controller/ResponseController.php';

$currentUser = getCurrentUser();
$adminName = (string) ($currentUser['name'] ?? 'Admin');

$reclamationController = new ReclamationController();
$responseController = new ResponseController();
$reclamations = $reclamationController->getAll();

$totalCount = count($reclamations);
$resolvedCount = 0;
$pendingCount = 0;

$latestResponses = [];
foreach ($reclamations as $reclamation) {
    $id = (int) $reclamation->getId();
    $latestResponses[$id] = $responseController->getLatestByReclamationId($id);

    if ($latestResponses[$id] === null) {
        $pendingCount++;
    } else {
        $resolvedCount++;
    }
}

$successMessage = null;
if (isset($_GET['sent']) && $_GET['sent'] === '1') {
    $successMessage = 'La réponse a été envoyée avec succès.';
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SmartPlate - Réclamations Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="template-backoffice.css">
</head>
<body>

    <aside class="admin-sidebar">
        <div class="sidebar-header">
            <span><img src="C:\Youssef\2A\ProjetWeb\SmartPlate\Logo\logo.png" height="80%" width="50%"></span> Administration SmartPlate
        </div>
        <div class="sidebar-menu">
            <div class="menu-category">Menu Principal</div>
            <a href="admin_dashboard.php" class="menu-item">📊 Analyse du tableau de bord</a>
            <a href="#" class="menu-item">🎯 Modération Objectifs</a>
            <a href="#" class="menu-item">🍽️ Journaux Utilisateurs</a>
            <a href="admin_reclamations.php" class="menu-item active">📝 Réclamations</a>
            <a href="admin_responses.php" class="menu-item">💬 Mes réponses</a>

            <div class="menu-category" style="margin-top: 20px;">Système</div>
            <a href="#" class="menu-item">⚠️ Anomalies (3)</a>
        </div>
    </aside>

    <main class="main-content">
        <header class="topbar">
            <div class="search-bar"></div>
            <div class="admin-profile">
                <span><?php echo htmlspecialchars($adminName, ENT_QUOTES, 'UTF-8'); ?> (Admin)</span>
                <a href="../logout.php" class="btn-action" style="margin-left:10px;">Deconnexion</a>
                <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($adminName); ?>&background=20c997&color=fff" alt="Profil">
            </div>
        </header>

        <div class="dashboard-container">
            <div class="page-header">
                <div>
                    <h1>Gestion des réclamations utilisateurs</h1>
                    <p>Consultez chaque demande et répondez directement depuis le back office.</p>
                </div>
            </div>

            <div class="kpi-grid">
                <div class="card kpi-card">
                    <div class="kpi-icon yellow">📝</div>
                    <div class="kpi-info">
                        <h3><?php echo $totalCount; ?></h3>
                        <span>Réclamations totales</span>
                    </div>
                </div>
                <div class="card kpi-card">
                    <div class="kpi-icon red">⏳</div>
                    <div class="kpi-info">
                        <h3><?php echo $pendingCount; ?></h3>
                        <span>En attente de réponse</span>
                    </div>
                </div>
                <div class="card kpi-card">
                    <div class="kpi-icon green">✅</div>
                    <div class="kpi-info">
                        <h3><?php echo $resolvedCount; ?></h3>
                        <span>Répondues</span>
                    </div>
                </div>
            </div>

            <?php if ($successMessage !== null): ?>
                <p class="alert-admin success"><?php echo htmlspecialchars($successMessage, ENT_QUOTES, 'UTF-8'); ?></p>
            <?php endif; ?>

            <div class="card">
                <div class="card-header">
                    <h2>Liste des réclamations reçues</h2>
                </div>

                <?php if (empty($reclamations)): ?>
                    <p class="empty-admin">Aucune réclamation disponible pour le moment.</p>
                <?php else: ?>
                    <div class="admin-table-wrap">
                        <table class="admin-table">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Date</th>
                                    <th>Client</th>
                                    <th>E-mail</th>
                                    <th>Sujet</th>
                                    <th>Message</th>
                                    <th>Statut</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($reclamations as $reclamation): ?>
                                    <?php
                                    $id = (int) $reclamation->getId();
                                    $latestResponse = $latestResponses[$id] ?? null;
                                    ?>
                                    <tr>
                                        <td><?php echo $id; ?></td>
                                        <td><?php echo htmlspecialchars((string) $reclamation->getDateCreation(), ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td><?php echo htmlspecialchars((string) $reclamation->getNomClient(), ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td><?php echo htmlspecialchars((string) $reclamation->getEmail(), ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td><?php echo htmlspecialchars((string) $reclamation->getSujet(), ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td><?php echo nl2br(htmlspecialchars((string) $reclamation->getMessage(), ENT_QUOTES, 'UTF-8')); ?></td>
                                        <td>
                                            <?php if ($latestResponse === null): ?>
                                                <span class="status-pill pending">En attente</span>
                                            <?php else: ?>
                                                <span class="status-pill resolved">Répondu</span>
                                                <p class="status-note"><?php echo htmlspecialchars((string) $latestResponse->getDateReponse(), ENT_QUOTES, 'UTF-8'); ?></p>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <a href="admin_reply_reclamation.php?id=<?php echo $id; ?>" class="btn-action">
                                                <?php echo $latestResponse === null ? 'Répondre' : 'Voir / Répondre'; ?>
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </main>

</body>
</html>
