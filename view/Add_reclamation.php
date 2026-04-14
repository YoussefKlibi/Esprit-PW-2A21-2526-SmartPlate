<?php
require_once __DIR__ . '/../config/auth.php';
requireAuthentication('login.php');
requireRole('user', 'back/admin_dashboard.php');

require_once __DIR__ . '/../controller/ReclamationController.php';
require_once __DIR__ . '/../models/Reclamation.php';

$pageTitle = 'SmartPlate - Ajouter une réclamation';
$currentPage = 'reclamation-add';
$successMessage = null;
$errorMessage = null;
$currentUser = getCurrentUser();
$userEmail = (string) ($currentUser['email'] ?? '');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
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
            $controller = new ReclamationController();
            $reclamation = new Reclamation();
            $reclamation
                ->setNomClient($nomClient)
                ->setEmail($email)
                ->setSujet($sujet)
                ->setMessage($message)
                ->setDateCreation(date('Y-m-d'));

            $id = $controller->create($reclamation);
            $successMessage = 'Votre réclamation a été envoyée avec succès. Numéro de suivi: #' . $id;
            $_POST = [];
        } catch (Throwable $exception) {
            $errorMessage = 'Une erreur est survenue lors de l\'envoi de votre réclamation.';
        }
    }
}

include __DIR__ . '/header.php';
?>

<main>
    <section class="section section-narrow" id="reclamation">
        <div class="section-head">
            <p class="section-tag">Service client</p>
            <h2>Ajouter une réclamation</h2>
            <p class="form-intro">
                Décrivez votre demande en quelques lignes. Notre équipe vous répondra dans les meilleurs délais.
            </p>
        </div>

        <?php if ($successMessage !== null): ?>
            <p class="alert alert-success"><?php echo htmlspecialchars($successMessage, ENT_QUOTES, 'UTF-8'); ?></p>
        <?php endif; ?>

        <?php if ($errorMessage !== null): ?>
            <p class="alert alert-error"><?php echo htmlspecialchars($errorMessage, ENT_QUOTES, 'UTF-8'); ?></p>
        <?php endif; ?>

        <form action="Add_reclamation.php" method="post" class="reclamation-form js-reclamation-form" novalidate>
            <div class="form-grid">
                <label class="field">
                    <span>Nom complet</span>
                    <input
                        type="text"
                        name="nom_client"
                        minlength="3"
                        maxlength="100"
                        value="<?php echo htmlspecialchars($_POST['nom_client'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
                        required
                    >
                </label>

                <label class="field">
                    <span>Adresse e-mail</span>
                    <input
                        type="email"
                        name="email"
                        maxlength="100"
                        value="<?php echo htmlspecialchars($_POST['email'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
                        required
                    >
                </label>
            </div>

            <label class="field">
                <span>Sujet</span>
                <input
                    type="text"
                    name="sujet"
                    minlength="5"
                    maxlength="255"
                    value="<?php echo htmlspecialchars($_POST['sujet'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
                    required
                >
            </label>

            <label class="field">
                <span>Message</span>
                <textarea name="message" rows="6" minlength="10" required><?php echo htmlspecialchars($_POST['message'] ?? '', ENT_QUOTES, 'UTF-8'); ?></textarea>
            </label>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary">Envoyer la réclamation</button>
                <a href="list_reclamation.php" class="btn btn-light">Voir mes réclamations</a>
                <a href="index.php" class="btn btn-light">Retour à l'accueil</a>
            </div>
        </form>
    </section>
</main>

<script src="reclamation-form-validation.js"></script>

<?php include __DIR__ . '/footer.php'; ?>
