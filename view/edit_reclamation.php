<?php
require_once __DIR__ . '/../config/auth.php';
requireAuthentication('login.php');
requireRole('user', 'back/admin_dashboard.php');

require_once __DIR__ . '/../controller/ReclamationController.php';
require_once __DIR__ . '/../controller/ResponseController.php';
require_once __DIR__ . '/../models/Reclamation.php';

$pageTitle = 'SmartPlate - Modifier une réclamation';
$currentPage = 'reclamation-edit';
$successMessage = null;
$errorMessage = null;
$currentUser = getCurrentUser();
$userEmail = (string) ($currentUser['email'] ?? '');

$reclamationController = new ReclamationController();
$responseController = new ResponseController();

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
if ($id <= 0) {
    $errorMessage = 'Identifiant de réclamation invalide.';
    $reclamation = null;
} else {
    $reclamation = $reclamationController->getById($id);
    if ($reclamation === null) {
        $errorMessage = 'Réclamation introuvable.';
    }
}

if ($reclamation !== null && $userEmail !== '') {
    if (strcasecmp((string) $reclamation->getEmail(), $userEmail) !== 0) {
        $errorMessage = 'Vous ne pouvez modifier que vos propres réclamations.';
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $reclamation !== null && $errorMessage === null) {
    $nomClient = trim($_POST['nom_client'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $sujet = trim($_POST['sujet'] ?? '');
    $message = trim($_POST['message'] ?? '');

    if ($nomClient === '' || $email === '' || $sujet === '' || $message === '') {
        $errorMessage = 'Veuillez remplir tous les champs obligatoires.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errorMessage = 'Veuillez saisir une adresse e-mail valide.';
    } elseif (strcasecmp($email, $userEmail) !== 0) {
        $errorMessage = 'L\'adresse e-mail doit correspondre au compte connecte.';
    } else {
        try {
            $reclamation
                ->setNomClient($nomClient)
                ->setEmail($email)
                ->setSujet($sujet)
                ->setMessage($message);

            $reclamationController->update($reclamation);
            $successMessage = 'La réclamation a été modifiée avec succès.';

            $reclamation = $reclamationController->getById($id);
        } catch (Throwable $exception) {
            $errorMessage = 'Une erreur est survenue pendant la modification.';
        }
    }
}

$latestResponse = null;
if ($reclamation !== null && $reclamation->getId() !== null) {
    $latestResponse = $responseController->getLatestByReclamationId($reclamation->getId());
}

$nomClientValue = $_POST['nom_client'] ?? ($reclamation ? (string) $reclamation->getNomClient() : '');
$emailValue = $_POST['email'] ?? ($reclamation ? (string) $reclamation->getEmail() : '');
$sujetValue = $_POST['sujet'] ?? ($reclamation ? (string) $reclamation->getSujet() : '');
$messageValue = $_POST['message'] ?? ($reclamation ? (string) $reclamation->getMessage() : '');

include __DIR__ . '/header.php';
?>

<main>
    <section class="section section-narrow" id="modifier-reclamation">
        <div class="section-head section-head-inline">
            <div>
                <p class="section-tag">Service client</p>
                <h2>Modifier une réclamation</h2>
                <p class="form-intro">Mettez à jour votre sujet ou votre message avant traitement complet.</p>
            </div>
            <a href="list_reclamation.php" class="btn btn-light">Retour à la liste</a>
        </div>

        <?php if ($successMessage !== null): ?>
            <p class="alert alert-success"><?php echo htmlspecialchars($successMessage, ENT_QUOTES, 'UTF-8'); ?></p>
        <?php endif; ?>

        <?php if ($errorMessage !== null): ?>
            <p class="alert alert-error"><?php echo htmlspecialchars($errorMessage, ENT_QUOTES, 'UTF-8'); ?></p>
        <?php endif; ?>

        <?php if ($reclamation !== null && $errorMessage === null): ?>
            <div class="response-box">
                <h3>Statut de la réponse administrateur</h3>
                <?php if ($latestResponse === null): ?>
                    <p><span class="status-chip status-pending">Pas encore de réponse</span></p>
                <?php else: ?>
                    <p><span class="status-chip status-resolved">Réponse reçue le <?php echo htmlspecialchars((string) $latestResponse->getDateReponse(), ENT_QUOTES, 'UTF-8'); ?></span></p>
                    <p class="response-note"><?php echo nl2br(htmlspecialchars((string) $latestResponse->getReponse(), ENT_QUOTES, 'UTF-8')); ?></p>
                <?php endif; ?>
            </div>

            <form action="edit_reclamation.php?id=<?php echo $id; ?>" method="post" class="reclamation-form js-reclamation-form" novalidate>
                <div class="form-grid">
                    <label class="field">
                        <span>Nom complet</span>
                        <input
                            type="text"
                            name="nom_client"
                            minlength="3"
                            maxlength="100"
                            value="<?php echo htmlspecialchars($nomClientValue, ENT_QUOTES, 'UTF-8'); ?>"
                            required
                        >
                    </label>

                    <label class="field">
                        <span>Adresse e-mail</span>
                        <input
                            type="email"
                            name="email"
                            maxlength="100"
                            value="<?php echo htmlspecialchars($userEmail, ENT_QUOTES, 'UTF-8'); ?>"
                            readonly
                            required
                            style="background-color: #f5f5f5; cursor: not-allowed;"
                        >
                    </label>
                </div>

                <label class="field">
                    <span>Sujet</span>
                    <select name="sujet" required>
                        <option value="">Sélectionnez un sujet</option>
                        <option value="Prix" <?php echo $sujetValue === 'Prix' ? 'selected' : ''; ?>>Prix</option>
                        <option value="Livraison" <?php echo $sujetValue === 'Livraison' ? 'selected' : ''; ?>>Livraison</option>
                        <option value="Goût" <?php echo $sujetValue === 'Goût' ? 'selected' : ''; ?>>Goût</option>
                        <option value="Service" <?php echo $sujetValue === 'Service' ? 'selected' : ''; ?>>Service</option>
                        <option value="Déception" <?php echo $sujetValue === 'Déception' ? 'selected' : ''; ?>>Déception</option>
                        <option value="Satisfaction" <?php echo $sujetValue === 'Satisfaction' ? 'selected' : ''; ?>>Satisfaction</option>
                    </select>
                </label>

                <label class="field">
                    <span>Message</span>
                    <textarea name="message" rows="6" minlength="10" required><?php echo htmlspecialchars($messageValue, ENT_QUOTES, 'UTF-8'); ?></textarea>
                </label>

                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">Enregistrer les modifications</button>
                    <a href="list_reclamation.php" class="btn btn-light">Annuler</a>
                </div>
            </form>
        <?php endif; ?>
    </section>
</main>

<script src="reclamation-form-validation.js"></script>

<?php include __DIR__ . '/footer.php'; ?>
