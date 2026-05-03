<?php
require_once __DIR__ . '/../config/auth.php';
requireAuthentication('login.php');
requireRole('user', 'back/admin_dashboard.php');

require_once __DIR__ . '/../controller/ReclamationController.php';
require_once __DIR__ . '/../controller/ResponseController.php';
require_once __DIR__ . '/../models/Reclamation.php';

$pageTitle = 'SmartPlate - Détails de la réclamation';
$currentPage = 'reclamation-list';
$currentUser = getCurrentUser();
$userEmail = (string) ($currentUser['email'] ?? '');

$reclamationController = new ReclamationController();
$responseController = new ResponseController();

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
if ($id <= 0) {
    die("Identifiant de réclamation invalide.");
}

$reclamation = $reclamationController->getById($id);
if ($reclamation === null) {
    die("Réclamation introuvable.");
}

// Sécurité: Vérifier que la réclamation appartient à l'utilisateur connecté
if (strcasecmp((string) $reclamation->getEmail(), $userEmail) !== 0) {
    die("Accès refusé. Vous ne pouvez consulter que vos propres réclamations.");
}

$responses = $responseController->getByReclamationId($id);

include __DIR__ . '/header.php';
?>

<main>
    <section class="section section-narrow" id="details-reclamation">
        <div class="section-head section-head-inline">
            <div>
                <p class="section-tag">Service client</p>
                <h2>Détails de la réclamation #<?php echo $id; ?></h2>
                <p class="form-intro">Historique de traitement de votre demande.</p>
            </div>
            <a href="list_reclamation.php" class="btn btn-light">Retour à la liste</a>
        </div>

        <div class="reclamation-details-card" style="background: white; border-radius: 12px; padding: 24px; box-shadow: 0 4px 6px rgba(0,0,0,0.05); margin-bottom: 32px; border: 1px solid #eaeaea;">
            <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 20px;">
                <div>
                    <h3 style="margin-top: 0; margin-bottom: 8px; font-size: 1.25rem;"><?php echo htmlspecialchars((string) $reclamation->getSujet(), ENT_QUOTES, 'UTF-8'); ?></h3>
                    <p style="color: #666; margin: 0; font-size: 0.9rem;">
                        Soumis le: <?php echo htmlspecialchars((string) $reclamation->getDateCreation(), ENT_QUOTES, 'UTF-8'); ?>
                    </p>
                </div>
                <div>
                    <?php
                    $statut = $reclamation->getStatut();
                    $chipClass = 'status-pending';
                    if ($statut === 'Traitée' || $statut === 'Résolue' || $statut === 'Acceptee') {
                        $chipClass = 'status-resolved';
                    } elseif ($statut === 'Rejetée') {
                        $chipClass = 'status-rejected';
                    }
                    ?>
                    <span class="status-chip <?php echo $chipClass; ?>" style="font-weight: 600; padding: 6px 12px; border-radius: 20px; font-size: 0.85rem; display: inline-block;">
                        <?php echo htmlspecialchars((string) $statut, ENT_QUOTES, 'UTF-8'); ?>
                    </span>
                </div>
            </div>
            
            <div style="background: #f8f9fa; padding: 16px; border-radius: 8px; margin-bottom: 10px;">
                <h4 style="margin-top: 0; font-size: 1rem; margin-bottom: 8px;">Votre message :</h4>
                <p style="margin: 0; line-height: 1.6;"><?php echo nl2br(htmlspecialchars((string) $reclamation->getMessage(), ENT_QUOTES, 'UTF-8')); ?></p>
            </div>
        </div>

        <div class="responses-section">
            <h3 style="margin-bottom: 20px;">Historique de traitement (<?php echo count($responses); ?>)</h3>
            
            <?php if (empty($responses)): ?>
                <div class="empty-state" style="text-align: center; padding: 40px; background: #f8f9fa; border-radius: 12px; border: 1px dashed #ccc;">
                    <p style="color: #666; margin: 0;">Aucune réponse de l'administration pour le moment.</p>
                </div>
            <?php else: ?>
                <div style="display: flex; flex-direction: column; gap: 16px;">
                    <?php foreach (array_reverse($responses) as $resp): ?>
                        <div class="response-item" style="background: #fff; border: 1px solid #e0e0e0; border-radius: 8px; padding: 16px; border-left: 4px solid #4a90e2;">
                            <div style="display: flex; justify-content: space-between; margin-bottom: 8px;">
                                <strong style="color: #333;">Service Client</strong>
                                <span style="font-size: 0.85rem; color: #888;"><?php echo htmlspecialchars((string) $resp->getDateReponse(), ENT_QUOTES, 'UTF-8'); ?></span>
                            </div>
                            <p style="margin: 0; color: #444; line-height: 1.5;">
                                <?php echo nl2br(htmlspecialchars((string) $resp->getReponse(), ENT_QUOTES, 'UTF-8')); ?>
                            </p>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </section>
</main>

<?php include __DIR__ . '/footer.php'; ?>
