<?php
require_once __DIR__ . '/../../config/auth.php';
requireAuthentication('../login.php');
requireRole('admin', '../index.php');

require_once __DIR__ . '/../../controller/ReclamationController.php';
require_once __DIR__ . '/../../controller/ResponseController.php';

$currentUser = getCurrentUser();
$adminName = (string) ($currentUser['name'] ?? 'Admin');

$responseController = new ResponseController();
$reclamationController = new ReclamationController();

$dashboardSuccessMessage = null;
$dashboardErrorMessage = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'delete_response') {
    $responseId = isset($_POST['response_id']) ? (int) $_POST['response_id'] : 0;

    if ($responseId <= 0) {
        $dashboardErrorMessage = 'Identifiant de réponse invalide.';
    } else {
        try {
            $deleted = $responseController->delete($responseId);
            if ($deleted) {
                header('Location: admin_dashboard.php?response_deleted=1');
                exit;
            }

            $dashboardErrorMessage = 'La suppression de la réponse a échoué.';
        } catch (Throwable $exception) {
            $dashboardErrorMessage = 'Une erreur est survenue pendant la suppression de la réponse.';
        }
    }
}

if (isset($_GET['response_deleted']) && $_GET['response_deleted'] === '1') {
    $dashboardSuccessMessage = 'La réponse a été supprimée avec succès.';
}

$recentResponses = array_slice($responseController->getAll(), 0, 5);
$allReclamations = $reclamationController->getAll();
$allResponses = $responseController->getAll();

$reclamationCache = [];
foreach ($recentResponses as $response) {
    $reclamationId = (int) $response->getIdReclamation();
    if (!array_key_exists($reclamationId, $reclamationCache)) {
        $reclamationCache[$reclamationId] = $reclamationController->getById($reclamationId);
    }
}

// ==========================================
// CALCUL DES STATISTIQUES AVANCÉES
// ==========================================
$totalReclamations = count($allReclamations);
$resolvedReclamations = 0;
$sujetsCount = [];
$prioritesCount = ['Urgent' => 0, 'Moyen' => 0, 'Faible' => 0];
$totalDaysResponse = 0;
$responsesCount = 0;

foreach ($allReclamations as $rec) {
    if ($rec->getStatut() === 'Traité') {
        $resolvedReclamations++;
    }
    
    $sujet = $rec->getSujet() ?: 'Autre';
    if (!isset($sujetsCount[$sujet])) $sujetsCount[$sujet] = 0;
    $sujetsCount[$sujet]++;
    
    $prio = $rec->getPriorite() ?: 'Faible';
    if (isset($prioritesCount[$prio])) {
        $prioritesCount[$prio]++;
    }
}

// Calculate SLA / Overdue
$slaExceededCount = 0;
$now = new DateTime();
$slaDelays = ['Urgent' => '+1 day', 'Moyen' => '+3 days', 'Faible' => '+7 days'];

foreach ($allReclamations as $rec) {
    if ($rec->getStatut() === 'En attente' || $rec->getStatut() === 'En cours') {
        $prio = $rec->getPriorite() ?: 'Faible';
        $delay = $slaDelays[$prio] ?? '+7 days';
        $dateCreation = new DateTime($rec->getDateCreation());
        $deadline = clone $dateCreation;
        $deadline->modify($delay);
        
        if ($now > $deadline) {
            $slaExceededCount++;
        }
    }
}

// Notification Email Admin (Max 1 par jour)
if ($slaExceededCount > 0) {
    $flagFile = __DIR__ . '/../../config/last_reminder.txt';
    $lastSent = file_exists($flagFile) ? trim(file_get_contents($flagFile)) : '';
    $today = date('Y-m-d');
    
    if ($lastSent !== $today) {
        $adminEmail = $currentUser['email'] ?? 'admin@smartplate.test';
        $subject = "Alerte : $slaExceededCount réclamation(s) en retard !";
        $msg = "Bonjour,\r\n\r\n";
        $msg .= "Le système a détecté que $slaExceededCount réclamation(s) ont dépassé leur délai de traitement (SLA).\r\n";
        $msg .= "Veuillez vous connecter au tableau de bord pour les traiter urgemment.\r\n\r\n";
        $msg .= "L'équipe Automatisée SmartPlate.";
        
        $headers = "From: system@smartplate.com\r\nContent-Type: text/plain; charset=UTF-8\r\n";
        @mail($adminEmail, $subject, $msg, $headers);
        @file_put_contents($flagFile, $today);
    }
}

// Calculate Average Response Time
foreach ($allResponses as $resp) {
    $recId = $resp->getIdReclamation();
    $rec = $reclamationController->getById($recId);
    if ($rec && $rec->getDateCreation() && $resp->getDateReponse()) {
        $dStart = new DateTime($rec->getDateCreation());
        $dEnd = new DateTime($resp->getDateReponse());
        $diff = $dStart->diff($dEnd);
        $totalDaysResponse += $diff->days;
        $responsesCount++;
    }
}

$tauxResolution = $totalReclamations > 0 ? round(($resolvedReclamations / $totalReclamations) * 100) : 0;
$tempsMoyenReponse = $responsesCount > 0 ? round($totalDaysResponse / $responsesCount, 1) : 0;

// Top catégories
arsort($sujetsCount);
$topCategories = array_slice($sujetsCount, 0, 3, true);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SmartPlate - Tableau de bord Admin</title>
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
            <a href="admin_dashboard.php" class="menu-item active">📊 Analyse du tableau de bord</a>
            <a href="#" class="menu-item">🎯 Modération Objectifs</a>
            <a href="#" class="menu-item">🍽️ Journaux Utilisateurs</a>
            <a href="admin_reclamations.php" class="menu-item">📝 Réclamations</a>
            <a href="admin_responses.php" class="menu-item">💬 Mes réponses</a>
            
            <div class="menu-category" style="margin-top: 20px;">Système</div>
            <a href="#" class="menu-item">⚠️ Anomalies (3)</a>
        </div>
    </aside>

    <main class="main-content">
        
        <header class="topbar">
            <div class="search-bar">
                </div>
            <div class="admin-profile">
                <span><?php echo htmlspecialchars($adminName, ENT_QUOTES, 'UTF-8'); ?> (Admin)</span>
                <a href="../logout.php" class="btn-action" style="margin-left:10px;">Deconnexion</a>
                <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($adminName); ?>&background=20c997&color=fff" alt="Profil">
            </div>
        </header>

        <div class="dashboard-container">
            
            <div class="page-header">
                <div>
                    <h1>Module : Gestion des Réclamations</h1>
                    <p>Tableau de bord analytique et suivi des performances de traitement.</p>
                </div>
            </div>

            <?php if ($slaExceededCount > 0): ?>
                <div style="background-color: #fff3f3; border-left: 5px solid #dc3545; padding: 15px; border-radius: 4px; margin-bottom: 20px;">
                    <h3 style="color: #dc3545; margin-top: 0; display: flex; align-items: center; gap: 10px;">
                        ⚠️ ALERTE RELANCE AUTOMATIQUE
                    </h3>
                    <p style="margin-bottom: 0; color: #333;">
                        Le système a détecté <strong><?php echo $slaExceededCount; ?></strong> réclamation(s) dont le délai de traitement (SLA) est dépassé ! Un e-mail de relance a été expédié. Veuillez les traiter en priorité.
                    </p>
                </div>
            <?php endif; ?>

            <div class="kpi-grid">
                <div class="card kpi-card">
                    <div class="kpi-icon green">✅</div>
                    <div class="kpi-info">
                        <h3><?php echo $tauxResolution; ?>%</h3>
                        <span>Taux de résolution global</span>
                    </div>
                </div>
                <div class="card kpi-card">
                    <div class="kpi-icon yellow">⏱️</div>
                    <div class="kpi-info">
                        <h3><?php echo $tempsMoyenReponse; ?> j</h3>
                        <span>Temps moyen de réponse</span>
                    </div>
                </div>
                <div class="card kpi-card">
                    <div class="kpi-icon red">⚠️</div>
                    <div class="kpi-info">
                        <h3><?php echo $prioritesCount['Urgent']; ?></h3>
                        <span>Réclamations Urgentes (En cours/Traitées)</span>
                    </div>
                </div>
            </div>

            <div class="content-grid">
                
                <div class="card">
                    <div class="card-header">
                        <h2>Top Catégories (Sujets)</h2>
                    </div>
                    <div style="padding-top: 10px;">
                        <?php if (empty($topCategories)): ?>
                            <p class="empty-admin">Pas de données.</p>
                        <?php else: ?>
                            <?php foreach ($topCategories as $sujet => $count): ?>
                                <?php $percent = $totalReclamations > 0 ? round(($count / $totalReclamations) * 100) : 0; ?>
                                <div style="margin-bottom: 15px;">
                                    <div style="display: flex; justify-content: space-between; margin-bottom: 5px; font-size: 0.9em; color: #555;">
                                        <strong><?php echo htmlspecialchars((string) $sujet, ENT_QUOTES, 'UTF-8'); ?></strong>
                                        <span><?php echo $count; ?> (<?php echo $percent; ?>%)</span>
                                    </div>
                                    <div style="width: 100%; background-color: #eee; border-radius: 4px; height: 8px;">
                                        <div style="width: <?php echo $percent; ?>%; background-color: #20c997; height: 100%; border-radius: 4px;"></div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header">
                        <h2>Répartition par Priorité</h2>
                    </div>
                    <div class="timeline" style="margin-top: 10px;">
                        <div class="timeline-item" style="--marker-color: #dc3545;">
                            <strong>Haute Priorité (Urgent)</strong>
                            <span class="timeline-time" style="font-size: 1.2em; font-weight: bold; color: #dc3545;"><?php echo $prioritesCount['Urgent']; ?></span>
                            <p style="color: var(--text-gray); font-size: 0.85rem; margin-top: 5px;">Déclenchées par NLP (Gravité forte)</p>
                        </div>
                        <div class="timeline-item" style="--marker-color: #fd7e14;">
                            <strong>Priorité Moyenne</strong>
                            <span class="timeline-time" style="font-size: 1.2em; font-weight: bold; color: #fd7e14;"><?php echo $prioritesCount['Moyen']; ?></span>
                            <p style="color: var(--text-gray); font-size: 0.85rem; margin-top: 5px;">Déclenchées par NLP (Gravité modérée)</p>
                        </div>
                        <div class="timeline-item" style="--marker-color: #28a745;">
                            <strong>Faible Priorité</strong>
                            <span class="timeline-time" style="font-size: 1.2em; font-weight: bold; color: #28a745;"><?php echo $prioritesCount['Faible']; ?></span>
                            <p style="color: var(--text-gray); font-size: 0.85rem; margin-top: 5px;">Messages standards / Positifs</p>
                        </div>
                    </div>
                </div>

            </div>

            <div class="card" style="margin-top: 20px;">
                <div class="card-header">
                    <h2>Liste de mes réponses</h2>
                    <a href="admin_responses.php" class="btn-action">Voir tout</a>
                </div>

                <?php if ($dashboardSuccessMessage !== null): ?>
                    <p class="alert-admin success"><?php echo htmlspecialchars($dashboardSuccessMessage, ENT_QUOTES, 'UTF-8'); ?></p>
                <?php endif; ?>

                <?php if ($dashboardErrorMessage !== null): ?>
                    <p class="alert-admin error"><?php echo htmlspecialchars($dashboardErrorMessage, ENT_QUOTES, 'UTF-8'); ?></p>
                <?php endif; ?>

                <?php if (empty($recentResponses)): ?>
                    <p class="empty-admin">Aucune réponse envoyée pour le moment.</p>
                <?php else: ?>
                    <div class="admin-table-wrap">
                        <table class="admin-table">
                            <thead>
                                <tr>
                                    <th># Réponse</th>
                                    <th>Réclamation</th>
                                    <th>Sujet</th>
                                    <th>Date</th>
                                    <th>Contenu</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($recentResponses as $response): ?>
                                    <?php
                                    $responseId = (int) $response->getId();
                                    $reclamationId = (int) $response->getIdReclamation();
                                    $linkedReclamation = $reclamationCache[$reclamationId] ?? null;
                                    ?>
                                    <tr>
                                        <td><?php echo $responseId; ?></td>
                                        <td>#<?php echo $reclamationId; ?></td>
                                        <td><?php echo htmlspecialchars((string) ($linkedReclamation ? $linkedReclamation->getSujet() : 'N/A'), ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td><?php echo htmlspecialchars((string) $response->getDateReponse(), ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td><?php echo nl2br(htmlspecialchars((string) $response->getReponse(), ENT_QUOTES, 'UTF-8')); ?></td>
                                        <td>
                                            <div class="admin-actions-inline">
                                                <a href="admin_edit_response.php?id=<?php echo $responseId; ?>" class="btn-action">Modifier</a>
                                                <form action="admin_dashboard.php" method="post" id="delete-response-form-<?php echo $responseId; ?>">
                                                    <input type="hidden" name="action" value="delete_response">
                                                    <input type="hidden" name="response_id" value="<?php echo $responseId; ?>">
                                                    <button
                                                        type="button"
                                                        class="btn-action btn-action-danger js-delete-response-btn"
                                                        data-form-id="delete-response-form-<?php echo $responseId; ?>"
                                                        data-label="réponse #<?php echo $responseId; ?>"
                                                    >
                                                        Supprimer
                                                    </button>
                                                </form>
                                            </div>
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

    <div class="admin-modal-backdrop" id="deleteResponseModal" aria-hidden="true">
        <div class="admin-modal-card" role="dialog" aria-modal="true" aria-labelledby="deleteResponseModalTitle" aria-describedby="deleteResponseModalText">
            <h3 class="admin-modal-title" id="deleteResponseModalTitle">Confirmer la suppression</h3>
            <p class="admin-modal-text" id="deleteResponseModalText">Voulez-vous vraiment supprimer cette réponse ?</p>
            <div class="admin-modal-actions">
                <button type="button" class="btn-action" id="cancelDeleteResponseBtn">Annuler</button>
                <button type="button" class="btn-action btn-action-danger" id="confirmDeleteResponseBtn">Supprimer</button>
            </div>
        </div>
    </div>

    <script src="admin-delete-response-modal.js"></script>

</body>
</html>