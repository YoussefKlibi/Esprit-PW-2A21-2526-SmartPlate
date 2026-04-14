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
$reclamationCache = [];
foreach ($recentResponses as $response) {
    $reclamationId = (int) $response->getIdReclamation();
    if (!array_key_exists($reclamationId, $reclamationCache)) {
        $reclamationCache[$reclamationId] = $reclamationController->getById($reclamationId);
    }
}
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
                    <h1>Module : Suivi Nutritionnel</h1>
                    <p>Aperçu des performances globales des entités Objectifs, Journaux et Repas.</p>
                </div>
            </div>

            <div class="kpi-grid">
                <div class="card kpi-card">
                    <div class="kpi-icon yellow">🍽️</div>
                    <div class="kpi-info">
                        <h3>1,450 <span class="trend up">↑ 12%</span></h3>
                        <span>Repas enregistrés (7 jours)</span>
                    </div>
                </div>
                <div class="card kpi-card">
                    <div class="kpi-icon green">🎯</div>
                    <div class="kpi-info">
                        <h3>320</h3>
                        <span>Objectifs "Atteints"</span>
                    </div>
                </div>
                <div class="card kpi-card">
                    <div class="kpi-icon red">⚠️</div>
                    <div class="kpi-info">
                        <h3>14</h3>
                        <span>Journaux avec anomalies</span>
                    </div>
                </div>
            </div>

            <div class="content-grid">
                
                <div class="card">
                    <div class="card-header">
                        <h2>Activité des Journaux Alimentaires</h2>
                    </div>
                    <div class="chart-mockup" style="height: 250px; display: flex; align-items: flex-end; gap: 10px; padding-top: 20px;">
                        <div style="flex: 1; background: #e8f8f5; border-top: 3px solid var(--admin-green); height: 40%; border-radius: 4px 4px 0 0;"></div>
                        <div style="flex: 1; background: #e8f8f5; border-top: 3px solid var(--admin-green); height: 60%; border-radius: 4px 4px 0 0;"></div>
                        <div style="flex: 1; background: #e8f8f5; border-top: 3px solid var(--admin-green); height: 45%; border-radius: 4px 4px 0 0;"></div>
                        <div style="flex: 1; background: #e8f8f5; border-top: 3px solid var(--admin-green); height: 80%; border-radius: 4px 4px 0 0;"></div>
                        <div style="flex: 1; background: #e8f8f5; border-top: 3px solid var(--admin-green); height: 100%; border-radius: 4px 4px 0 0;"></div>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header">
                        <h2>Dernières Alertes (Modération)</h2>
                    </div>
                    <div class="timeline">
                        <div class="timeline-item">
                            <strong>Repas Suspect</strong>
                            <span class="timeline-time">Il y a 10 minutes</span>
                            <p style="color: var(--text-gray); font-size: 0.85rem; margin-top: 5px;">ID Repas #402: 5000 kcal saisis en une fois.</p>
                            <button class="btn-action">Inspecter</button>
                        </div>
                        <div class="timeline-item" style="--marker-color: #f1c40f;">
                            <strong>Objectif Irréaliste</strong>
                            <span class="timeline-time">Hier, 15:00</span>
                            <p style="color: var(--text-gray); font-size: 0.85rem; margin-top: 5px;">ID Objectif #102: Poids cible de 30kg demandé.</p>
                            <button class="btn-action">Inspecter</button>
                        </div>
                        <div class="timeline-item">
                            <strong>Journal Vide</strong>
                            <span class="timeline-time">Hier, 08:30</span>
                            <p style="color: var(--text-gray); font-size: 0.85rem; margin-top: 5px;">Journal ID #892 validé sans aucun repas.</p>
                            <button class="btn-action">Supprimer</button>
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