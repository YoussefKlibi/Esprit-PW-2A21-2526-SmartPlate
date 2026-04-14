<?php
require_once __DIR__ . '/../config/auth.php';
requireAuthentication('login.php');
requireRole('user', 'back/admin_dashboard.php');

require_once __DIR__ . '/../controller/ReclamationController.php';
require_once __DIR__ . '/../controller/ResponseController.php';

$pageTitle = 'SmartPlate - Mes réclamations';
$currentPage = 'reclamation-list';
$successMessage = null;
$errorMessage = null;
$currentUser = getCurrentUser();
$userEmail = (string) ($currentUser['email'] ?? '');

$reclamationController = new ReclamationController();
$responseController = new ResponseController();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'delete') {
    $idToDelete = isset($_POST['id']) ? (int) $_POST['id'] : 0;

    if ($idToDelete <= 0) {
        $errorMessage = 'Identifiant de réclamation invalide.';
    } else {
        try {
            $targetReclamation = $reclamationController->getById($idToDelete);
            if ($targetReclamation === null || strcasecmp((string) $targetReclamation->getEmail(), $userEmail) !== 0) {
                $errorMessage = 'Vous ne pouvez supprimer que vos propres réclamations.';
            } else {
                $deleted = $reclamationController->delete($idToDelete);
                if ($deleted) {
                    $successMessage = 'La réclamation a bien été supprimée.';
                } else {
                    $errorMessage = 'La suppression a échoué.';
                }
            }
        } catch (Throwable $exception) {
            $errorMessage = 'Une erreur est survenue pendant la suppression.';
        }
    }
}

$reclamations = $reclamationController->getByEmail($userEmail);
$responsesRecues = [];

foreach ($reclamations as $reclamation) {
    if ($reclamation->getId() !== null) {
        $latestResponse = $responseController->getLatestByReclamationId((int) $reclamation->getId());
        if ($latestResponse !== null) {
            $responsesRecues[] = [
                'reclamation' => $reclamation,
                'response' => $latestResponse,
            ];
        }
    }
}

include __DIR__ . '/header.php';
?>

<main>
    <section class="section" id="mes-reclamations">
        <div class="section-head section-head-inline">
            <div>
                <p class="section-tag">Suivi client</p>
                <h2>Mes réclamations</h2>
                <p class="form-intro">Consultez vos demandes, modifiez-les ou supprimez-les selon vos besoins.</p>
                <p class="form-intro">Compte connecté: <?php echo htmlspecialchars($userEmail, ENT_QUOTES, 'UTF-8'); ?></p>
            </div>
            <a href="Add_reclamation.php" class="btn btn-primary">Nouvelle réclamation</a>
        </div>

        <?php if ($successMessage !== null): ?>
            <p class="alert alert-success"><?php echo htmlspecialchars($successMessage, ENT_QUOTES, 'UTF-8'); ?></p>
        <?php endif; ?>

        <?php if ($errorMessage !== null): ?>
            <p class="alert alert-error"><?php echo htmlspecialchars($errorMessage, ENT_QUOTES, 'UTF-8'); ?></p>
        <?php endif; ?>

        <?php if (empty($reclamations)): ?>
            <div class="empty-state">
                <h3>Aucune réclamation trouvée</h3>
                <p>Ajoutez une nouvelle réclamation pour commencer.</p>
            </div>
        <?php else: ?>
            <div class="table-wrap">
                <table class="reclamation-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Date</th>
                            <th>Sujet</th>
                            <th>Message</th>
                            <th>Réponse admin</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($reclamations as $reclamation): ?>
                            <?php
                            $latestResponse = null;
                            if ($reclamation->getId() !== null) {
                                $latestResponse = $responseController->getLatestByReclamationId($reclamation->getId());
                            }
                            ?>
                            <tr>
                                <td><?php echo (int) $reclamation->getId(); ?></td>
                                <td><?php echo htmlspecialchars((string) $reclamation->getDateCreation(), ENT_QUOTES, 'UTF-8'); ?></td>
                                <td><?php echo htmlspecialchars((string) $reclamation->getSujet(), ENT_QUOTES, 'UTF-8'); ?></td>
                                <td><?php echo nl2br(htmlspecialchars((string) $reclamation->getMessage(), ENT_QUOTES, 'UTF-8')); ?></td>
                                <td>
                                    <?php if ($latestResponse === null): ?>
                                        <span class="status-chip status-pending">Pas encore de réponse</span>
                                    <?php else: ?>
                                        <span class="status-chip status-resolved">Réponse reçue</span>
                                        <p class="response-note"><?php echo htmlspecialchars((string) $latestResponse->getDateReponse(), ENT_QUOTES, 'UTF-8'); ?> - <?php echo htmlspecialchars((string) $latestResponse->getReponse(), ENT_QUOTES, 'UTF-8'); ?></p>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="actions-inline">
                                        <a href="edit_reclamation.php?id=<?php echo (int) $reclamation->getId(); ?>" class="btn btn-light btn-sm">Modifier</a>
                                        <form action="list_reclamation.php" method="post" id="delete-form-<?php echo (int) $reclamation->getId(); ?>">
                                            <input type="hidden" name="action" value="delete">
                                            <input type="hidden" name="id" value="<?php echo (int) $reclamation->getId(); ?>">
                                            <button
                                                type="button"
                                                class="btn btn-danger btn-sm js-delete-btn"
                                                data-form-id="delete-form-<?php echo (int) $reclamation->getId(); ?>"
                                                data-sujet="<?php echo htmlspecialchars((string) $reclamation->getSujet(), ENT_QUOTES, 'UTF-8'); ?>"
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
    </section>

    <section class="section" id="mes-reponses-admin">
        <div class="section-head">
            <p class="section-tag">Réponses administration</p>
            <h2>Réponses reçues de l'administrateur</h2>
            <p class="form-intro">Consultez ici les réponses envoyées à vos réclamations.</p>
        </div>

        <?php if (empty($responsesRecues)): ?>
            <div class="empty-state">
                <h3>Aucune réponse pour le moment</h3>
                <p>Dès qu'un administrateur répondra, vous la verrez dans cette section.</p>
            </div>
        <?php else: ?>
            <div class="response-grid">
                <?php foreach ($responsesRecues as $item): ?>
                    <?php
                    $reclamationItem = $item['reclamation'];
                    $responseItem = $item['response'];
                    ?>
                    <article class="response-card">
                        <p class="response-meta">Réclamation #<?php echo (int) $reclamationItem->getId(); ?> - <?php echo htmlspecialchars((string) $responseItem->getDateReponse(), ENT_QUOTES, 'UTF-8'); ?></p>
                        <h3><?php echo htmlspecialchars((string) $reclamationItem->getSujet(), ENT_QUOTES, 'UTF-8'); ?></h3>
                        <p><?php echo nl2br(htmlspecialchars((string) $responseItem->getReponse(), ENT_QUOTES, 'UTF-8')); ?></p>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </section>
</main>

<div class="modal-backdrop" id="deleteModal" aria-hidden="true">
    <div class="modal-card" role="dialog" aria-modal="true" aria-labelledby="deleteModalTitle" aria-describedby="deleteModalText">
        <h3 class="modal-title" id="deleteModalTitle">Confirmer la suppression</h3>
        <p class="modal-text" id="deleteModalText">Voulez-vous vraiment supprimer cette réclamation ?</p>
        <div class="modal-actions">
            <button type="button" class="btn btn-light" id="cancelDeleteBtn">Annuler</button>
            <button type="button" class="btn btn-danger" id="confirmDeleteBtn">Supprimer</button>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const modal = document.getElementById('deleteModal');
    const modalText = document.getElementById('deleteModalText');
    const cancelDeleteBtn = document.getElementById('cancelDeleteBtn');
    const confirmDeleteBtn = document.getElementById('confirmDeleteBtn');
    const deleteButtons = document.querySelectorAll('.js-delete-btn');

    let selectedForm = null;

    function openModal() {
        modal.classList.add('is-open');
        modal.setAttribute('aria-hidden', 'false');
    }

    function closeModal() {
        modal.classList.remove('is-open');
        modal.setAttribute('aria-hidden', 'true');
        selectedForm = null;
    }

    deleteButtons.forEach(function (button) {
        button.addEventListener('click', function () {
            const formId = button.getAttribute('data-form-id');
            const sujet = button.getAttribute('data-sujet') || 'cette réclamation';
            selectedForm = document.getElementById(formId);

            modalText.textContent = 'Voulez-vous vraiment supprimer la réclamation : "' + sujet + '" ?';
            openModal();
        });
    });

    cancelDeleteBtn.addEventListener('click', closeModal);

    confirmDeleteBtn.addEventListener('click', function () {
        if (selectedForm) {
            selectedForm.submit();
        }
        closeModal();
    });

    modal.addEventListener('click', function (event) {
        if (event.target === modal) {
            closeModal();
        }
    });

    document.addEventListener('keydown', function (event) {
        if (event.key === 'Escape' && modal.classList.contains('is-open')) {
            closeModal();
        }
    });
});
</script>

<?php include __DIR__ . '/footer.php'; ?>
