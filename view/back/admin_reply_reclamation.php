<?php
require_once __DIR__ . '/../../config/auth.php';
requireAuthentication('../login.php');
requireRole('admin', '../index.php');

require_once __DIR__ . '/../../controller/ReclamationController.php';
require_once __DIR__ . '/../../controller/ResponseController.php';
require_once __DIR__ . '/../../models/Response.php';

$currentUser = getCurrentUser();
$adminName = (string) ($currentUser['name'] ?? 'Admin');

$reclamationController = new ReclamationController();
$responseController = new ResponseController();

$errorMessage = null;
$successMessage = null;

$responseMinLength = 5;
$responseMaxLength = 2000;

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

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $reclamation !== null && $errorMessage === null) {
    $reponseText = trim($_POST['reponse'] ?? '');
    $reponseLength = strlen($reponseText);

    if ($reponseText === '') {
        $errorMessage = 'La réponse est obligatoire.';
    } elseif ($reponseLength < $responseMinLength) {
        $errorMessage = 'La réponse doit contenir au moins 5 caractères.';
    } elseif ($reponseLength > $responseMaxLength) {
        $errorMessage = 'La réponse ne doit pas dépasser 2000 caractères.';
    } else {
        try {
            $response = new Response();
            $response
                ->setIdReclamation($id)
                ->setReponse($reponseText)
                ->setDateReponse(date('Y-m-d'));

            $responseController->create($response);
            header('Location: admin_reclamations.php?sent=1');
            exit;
        } catch (Throwable $exception) {
            $errorMessage = 'Une erreur est survenue lors de l\'envoi de la réponse.';
        }
    }
}

$existingResponses = [];
if ($reclamation !== null) {
    $existingResponses = $responseController->getByReclamationId($id);
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SmartPlate - Répondre à une réclamation</title>
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
                    <h1>Répondre à une réclamation</h1>
                    <p>Rédigez votre réponse pour informer l'utilisateur.</p>
                </div>
                <a href="admin_reclamations.php" class="btn-action">Retour à la liste</a>
            </div>

            <?php if ($errorMessage !== null): ?>
                <p class="alert-admin error"><?php echo htmlspecialchars($errorMessage, ENT_QUOTES, 'UTF-8'); ?></p>
            <?php endif; ?>

            <?php if ($successMessage !== null): ?>
                <p class="alert-admin success"><?php echo htmlspecialchars($successMessage, ENT_QUOTES, 'UTF-8'); ?></p>
            <?php endif; ?>

            <?php if ($reclamation !== null): ?>
                <div class="card reply-context">
                    <h2>Détails de la réclamation #<?php echo (int) $reclamation->getId(); ?></h2>
                    <p><strong>Client :</strong> <?php echo htmlspecialchars((string) $reclamation->getNomClient(), ENT_QUOTES, 'UTF-8'); ?></p>
                    <p><strong>E-mail :</strong> <?php echo htmlspecialchars((string) $reclamation->getEmail(), ENT_QUOTES, 'UTF-8'); ?></p>
                    <p><strong>Sujet :</strong> <?php echo htmlspecialchars((string) $reclamation->getSujet(), ENT_QUOTES, 'UTF-8'); ?></p>
                    <p><strong>Message :</strong><br><?php echo nl2br(htmlspecialchars((string) $reclamation->getMessage(), ENT_QUOTES, 'UTF-8')); ?></p>
                </div>

                <div class="card">
                    <div class="card-header">
                        <h2>Envoyer une réponse</h2>
                    </div>
                    <form action="admin_reply_reclamation.php?id=<?php echo $id; ?>" method="post" class="reply-form js-admin-response-form" novalidate>
                        <label for="reponse">Réponse administrateur</label>
                        <textarea id="reponse" name="reponse" rows="6" minlength="5" maxlength="2000" required><?php echo htmlspecialchars($_POST['reponse'] ?? '', ENT_QUOTES, 'UTF-8'); ?></textarea>
                        <div class="reply-actions">
                            <button type="submit" class="btn-action">Envoyer la réponse</button>
                        </div>
                    </form>
                </div>

                <div class="card">
                    <div class="card-header">
                        <h2>Historique des réponses</h2>
                    </div>
                    <?php if (empty($existingResponses)): ?>
                        <p class="empty-admin">Aucune réponse envoyée pour cette réclamation.</p>
                    <?php else: ?>
                        <div class="response-list">
                            <?php foreach ($existingResponses as $response): ?>
                                <article class="response-item">
                                    <p class="response-date">Réponse du <?php echo htmlspecialchars((string) $response->getDateReponse(), ENT_QUOTES, 'UTF-8'); ?></p>
                                    <p><?php echo nl2br(htmlspecialchars((string) $response->getReponse(), ENT_QUOTES, 'UTF-8')); ?></p>
                                </article>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
    </main>

    <script src="admin-response-validation.js"></script>

</body>
</html>
