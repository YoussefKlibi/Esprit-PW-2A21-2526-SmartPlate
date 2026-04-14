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

$responseMinLength = 5;
$responseMaxLength = 2000;

$errorMessage = null;

$responseId = isset($_GET['id']) ? (int) $_GET['id'] : 0;
if ($responseId <= 0) {
    $errorMessage = 'Identifiant de réponse invalide.';
    $response = null;
    $reclamation = null;
} else {
    $response = $responseController->getById($responseId);
    if ($response === null) {
        $errorMessage = 'Réponse introuvable.';
        $reclamation = null;
    } else {
        $reclamation = $reclamationController->getById((int) $response->getIdReclamation());
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $response !== null && $errorMessage === null) {
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
            $response
                ->setReponse($reponseText)
                ->setDateReponse(date('Y-m-d'));

            $responseController->update($response);
            header('Location: admin_responses.php?updated=1');
            exit;
        } catch (Throwable $exception) {
            $errorMessage = 'Une erreur est survenue lors de la modification de la réponse.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SmartPlate - Modifier une réponse</title>
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
                    <h1>Modifier une réponse</h1>
                    <p>Mettez à jour votre réponse puis enregistrez.</p>
                </div>
                <a href="admin_responses.php" class="btn-action">Retour à mes réponses</a>
            </div>

            <?php if ($errorMessage !== null): ?>
                <p class="alert-admin error"><?php echo htmlspecialchars($errorMessage, ENT_QUOTES, 'UTF-8'); ?></p>
            <?php endif; ?>

            <?php if ($response !== null): ?>
                <div class="card reply-context">
                    <h2>Contexte de la réponse #<?php echo (int) $response->getId(); ?></h2>
                    <p><strong>Réclamation liée :</strong> #<?php echo (int) $response->getIdReclamation(); ?></p>
                    <p><strong>Client :</strong> <?php echo htmlspecialchars((string) ($reclamation ? $reclamation->getNomClient() : 'Inconnu'), ENT_QUOTES, 'UTF-8'); ?></p>
                    <p><strong>Sujet :</strong> <?php echo htmlspecialchars((string) ($reclamation ? $reclamation->getSujet() : 'N/A'), ENT_QUOTES, 'UTF-8'); ?></p>
                </div>

                <div class="card">
                    <div class="card-header">
                        <h2>Modifier le contenu de la réponse</h2>
                    </div>
                    <form action="admin_edit_response.php?id=<?php echo (int) $response->getId(); ?>" method="post" class="reply-form js-admin-response-form" novalidate>
                        <label for="reponse">Réponse administrateur</label>
                        <textarea id="reponse" name="reponse" rows="6" minlength="5" maxlength="2000" required><?php echo htmlspecialchars($_POST['reponse'] ?? (string) $response->getReponse(), ENT_QUOTES, 'UTF-8'); ?></textarea>
                        <div class="reply-actions">
                            <button type="submit" class="btn-action">Enregistrer les modifications</button>
                        </div>
                    </form>
                </div>
            <?php endif; ?>
        </div>
    </main>

    <script src="admin-response-validation.js"></script>

</body>
</html>
