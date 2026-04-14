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

$successMessage = null;
$errorMessage = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'delete') {
    $responseId = isset($_POST['response_id']) ? (int) $_POST['response_id'] : 0;

    if ($responseId <= 0) {
        $errorMessage = 'Identifiant de réponse invalide.';
    } else {
        try {
            $deleted = $responseController->delete($responseId);
            if ($deleted) {
                header('Location: admin_responses.php?deleted=1');
                exit;
            }

            $errorMessage = 'La suppression de la réponse a échoué.';
        } catch (Throwable $exception) {
            $errorMessage = 'Une erreur est survenue pendant la suppression de la réponse.';
        }
    }
}

if (isset($_GET['deleted']) && $_GET['deleted'] === '1') {
    $successMessage = 'La réponse a été supprimée avec succès.';
}

if (isset($_GET['updated']) && $_GET['updated'] === '1') {
    $successMessage = 'La réponse a été modifiée avec succès.';
}

$responses = $responseController->getAll();

$reclamationCache = [];
foreach ($responses as $response) {
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
    <title>SmartPlate - Mes réponses admin</title>
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
            <a href="admin_reclamations.php" class="menu-item">📝 Réclamations</a>
            <a href="admin_responses.php" class="menu-item active">💬 Mes réponses</a>

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
                    <h1>Liste de mes réponses</h1>
                    <p>Modifiez ou supprimez les réponses déjà envoyées aux utilisateurs.</p>
                </div>
            </div>

            <?php if ($successMessage !== null): ?>
                <p class="alert-admin success"><?php echo htmlspecialchars($successMessage, ENT_QUOTES, 'UTF-8'); ?></p>
            <?php endif; ?>

            <?php if ($errorMessage !== null): ?>
                <p class="alert-admin error"><?php echo htmlspecialchars($errorMessage, ENT_QUOTES, 'UTF-8'); ?></p>
            <?php endif; ?>

            <div class="card">
                <div class="card-header">
                    <h2>Réponses envoyées</h2>
                </div>

                <?php if (empty($responses)): ?>
                    <p class="empty-admin">Aucune réponse envoyée pour le moment.</p>
                <?php else: ?>
                    <div class="admin-table-wrap">
                        <table class="admin-table">
                            <thead>
                                <tr>
                                    <th># Réponse</th>
                                    <th>Date</th>
                                    <th>Réclamation</th>
                                    <th>Client</th>
                                    <th>Sujet</th>
                                    <th>Contenu de la réponse</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($responses as $response): ?>
                                    <?php
                                    $reclamationId = (int) $response->getIdReclamation();
                                    $linkedReclamation = $reclamationCache[$reclamationId] ?? null;
                                    ?>
                                    <tr>
                                        <td><?php echo (int) $response->getId(); ?></td>
                                        <td><?php echo htmlspecialchars((string) $response->getDateReponse(), ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td>#<?php echo $reclamationId; ?></td>
                                        <td><?php echo htmlspecialchars((string) ($linkedReclamation ? $linkedReclamation->getNomClient() : 'Inconnu'), ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td><?php echo htmlspecialchars((string) ($linkedReclamation ? $linkedReclamation->getSujet() : 'N/A'), ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td><?php echo nl2br(htmlspecialchars((string) $response->getReponse(), ENT_QUOTES, 'UTF-8')); ?></td>
                                        <td>
                                            <div class="admin-actions-inline">
                                                <a href="admin_edit_response.php?id=<?php echo (int) $response->getId(); ?>" class="btn-action">Modifier</a>
                                                <form action="admin_responses.php" method="post" id="delete-response-form-<?php echo (int) $response->getId(); ?>">
                                                    <input type="hidden" name="action" value="delete">
                                                    <input type="hidden" name="response_id" value="<?php echo (int) $response->getId(); ?>">
                                                    <button
                                                        type="button"
                                                        class="btn-action btn-action-danger js-delete-response-btn"
                                                        data-form-id="delete-response-form-<?php echo (int) $response->getId(); ?>"
                                                        data-label="réponse #<?php echo (int) $response->getId(); ?>"
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
