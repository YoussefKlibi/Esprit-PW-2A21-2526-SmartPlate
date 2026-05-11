<?php
require_once __DIR__ . '/../../config/auth.php';
requireAuthentication('../login.php');
requireRole('admin', '../index.php');

require_once __DIR__ . '/../../../controller/Reclamation/ReclamationController.php';
require_once __DIR__ . '/../../../controller/Reclamation/ResponseController.php';
require_once __DIR__ . '/../../../Models/Reclamation/Response.php';

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
    } else {
        // Changement automatique de statut à la consultation
        if ($reclamation->getStatut() === 'En attente') {
            $reclamation->setStatut('En cours');
            $reclamationController->update($reclamation);

            // Envoi de l'e-mail informant de la consultation
            $to = $reclamation->getEmail();
            $emailSubject = "Votre réclamation est en cours de traitement";
            $messageBody = "Bonjour " . $reclamation->getNomClient() . ",\r\n\r\n";
            $messageBody .= "Nous vous informons qu'un administrateur vient de consulter et de prendre en charge votre réclamation concernant : " . $reclamation->getSujet() . ".\r\n\r\n";
            $messageBody .= "Le statut de votre demande est désormais : En cours.\r\n";
            $messageBody .= "Vous recevrez un nouvel e-mail dès qu'une réponse officielle vous sera apportée.\r\n\r\n";
            $messageBody .= "L'équipe SmartPlate";
            
            $headers = "From: noreply@smartplate.com\r\n";
            $headers .= "Reply-To: support@smartplate.com\r\n";
            $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";
            $headers .= "X-Mailer: PHP/" . phpversion();

            @mail($to, $emailSubject, $messageBody, $headers);
        }
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
            
            // Mise à jour automatique du statut
            if ($reclamation->getStatut() === 'En attente' || $reclamation->getStatut() === 'En cours') {
                $reclamation->setStatut('Traité');
                $reclamationController->update($reclamation);
            }

            // Envoi de l'e-mail au client
            $to = $reclamation->getEmail();
            $subject = "Mise à jour de votre réclamation : " . $reclamation->getSujet();
            $messageBody = "Bonjour " . $reclamation->getNomClient() . ",\r\n\r\n";
            $messageBody .= "L'administration SmartPlate a répondu à votre réclamation concernant : " . $reclamation->getSujet() . "\r\n\r\n";
            $messageBody .= "Voici la réponse :\r\n";
            $messageBody .= "--------------------------------------------------\r\n";
            $messageBody .= $reponseText . "\r\n";
            $messageBody .= "--------------------------------------------------\r\n\r\n";
            $messageBody .= "Vous pouvez consulter l'historique complet depuis votre espace personnel.\r\n\r\n";
            $messageBody .= "Merci de votre confiance.\r\nL'équipe SmartPlate";
            
            $headers = "From: noreply@smartplate.com\r\n";
            $headers .= "Reply-To: support@smartplate.com\r\n";
            $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";
            $headers .= "X-Mailer: PHP/" . phpversion();

            @mail($to, $subject, $messageBody, $headers);

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
            <span>
                <img src="../xpdf/logo.jpg" alt="SmartPlate Logo" style="width:30px; height:30px; object-fit:cover; border-radius:8px; display:block;">
            </span> SmartPlate
        </div>
        <div class="sidebar-menu">
            <div class="menu-category">Menu Principal</div>
            <a href="../../User/backend/admin_welcome.php" class="menu-item">📊 Vue d'ensemble</a>
            <a href="../../User/backend/user_list.php" class="menu-item active">👥 Utilisateurs & Logins</a>
            
            <!-- Nouvelles pages simples -->
            <a href="#" class="menu-item">📦 Produit</a>
            <div class="nav-dropdown">
                <a href="#" class="menu-item" onclick="toggleSubMenu(event, 'recettesMenu')">
                    🍲 Recettes
                    <span id="arrow-recettesMenu" style="float: right; font-size: 0.8em; margin-top: 4px; transition: transform 0.3s;">▼</span>
                </a>
                <div id="recettesMenu" style="display: none; background: rgba(0,0,0,0.03); border-radius: 8px; margin-bottom: 5px; padding-top: 5px; padding-bottom: 5px;">
                    <a href="../../Recette/backoffice/recettes.php" class="menu-item" style="padding-left: 40px; font-size: 0.9em; margin-bottom: 2px;">🍽 Gestion Recettes</a>
                    <a href="../../Recette/backoffice/ingredients.php" class="menu-item" style="padding-left: 40px; font-size: 0.9em; margin-bottom: 2px;">🥕 Gestion Ingrédients</a>
                    <a href="../../Recette/backoffice/dashboard.php" class="menu-item" style="padding-left: 40px; font-size: 0.9em; margin-bottom: 2px;">📊 Dashboard</a>
                </div>
            </div>

            <!-- Menu déroulant : Suivi nutritionnel -->
            <div class="nav-dropdown">
                <a href="#" class="menu-item" onclick="toggleSubMenu(event, 'suiviNutritionnelMenu')">
                    📈 Suivi nutritionnel 
                    <span id="arrow-suiviNutritionnelMenu" style="float: right; font-size: 0.8em; margin-top: 4px; transition: transform 0.3s;">▼</span>
                </a>
                <!-- Sous-menu (masqué par défaut) -->
                <div id="suiviNutritionnelMenu" style="display: none; background: rgba(0,0,0,0.03); border-radius: 8px; margin-bottom: 5px; padding-top: 5px; padding-bottom: 5px;">
                    <a href="../../Suivi_Nutritionnel/BackOffice/admin_dashboard.php" class="menu-item" style="padding-left: 40px; font-size: 0.9em; margin-bottom: 2px;">📊 Dashboard Analytics</a>
                    <a href="../../Suivi_Nutritionnel/BackOffice/admin_objectifs.php" class="menu-item" style="padding-left: 40px; font-size: 0.9em; margin-bottom: 2px;">🎯 Modération Objectifs</a>
                    <a href="../../Suivi_Nutritionnel/BackOffice/admin_journaux.php" class="menu-item" style="padding-left: 40px; font-size: 0.9em; margin-bottom: 2px;">🍽️ Journaux Utilisateurs</a>
                </div>
            </div>

            <!-- Nouvelles pages simples (suite) -->
            <a href="../../Forum/backoffice/admin_forum.php" class="menu-item">💬 Forum</a>
            <a href="../../Reclamation/back/admin_reclamations.php" class="menu-item">📝 Réclamation</a>

            <div class="menu-category" style="margin-top: 20px;">Système</div>
            <a href="#" class="menu-item">⚠️ Anomalies (3)</a>
            <a href="#" class="menu-item" id="openAdminSettingsBtn" style="margin-top: auto;">⚙️ Paramètres du site</a>
            <a href="#" class="menu-item" style="color: #ff6b6b;" onclick="event.preventDefault(); showCustomConfirm('Déconnexion', 'Voulez-vous vraiment vous déconnecter de la session administrateur ?', '🚪', 'Déconnexion', 'orange', () => window.location.href='../frontend/logout.php')">🚪 Déconnexion</a>
        </div>
    </aside>
    <script>
        // Fonction pour gérer l'ouverture/fermeture des sous-menus de la sidebar
        function toggleSubMenu(event, menuId) {
            event.preventDefault(); // Empêche le lien de remonter en haut de la page
            const menu = document.getElementById(menuId);
            const arrow = document.getElementById('arrow-' + menuId);
            
            if (menu.style.display === 'none' || menu.style.display === '') {
                menu.style.display = 'block';
                arrow.style.transform = 'rotate(180deg)'; // Tourne la flèche vers le haut
            } else {
                menu.style.display = 'none';
                arrow.style.transform = 'rotate(0deg)'; // Remet la flèche vers le bas
            }
        }
    </script>

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

                    <?php 
                    $sujet = $reclamation->getSujet();
                    $predefinedResponses = [
                        'Prix' => "Bonjour,\n\nNous comprenons votre préoccupation concernant nos tarifs. Nos prix reflètent la qualité de nos ingrédients sourcés localement. Toutefois, nous tenons compte de votre retour pour nos futures offres promotionnelles.\n\nCordialement,\nL'équipe SmartPlate",
                        'Livraison' => "Bonjour,\n\nNous vous présentons nos sincères excuses pour le désagrément rencontré lors de votre livraison. Nous avons remonté l'information à notre partenaire logistique pour éviter que cela ne se reproduise.\n\nCordialement,\nL'équipe SmartPlate",
                        'Goût' => "Bonjour,\n\nNous sommes navrés d'apprendre que votre commande n'a pas été à la hauteur de vos attentes gustatives. Nous transmettons immédiatement vos remarques à notre équipe en cuisine.\n\nCordialement,\nL'équipe SmartPlate",
                        'Service' => "Bonjour,\n\nNous vous prions de nous excuser pour cette expérience avec notre service. Votre retour a été transmis au manager pour un rappel de nos standards de qualité auprès des équipes.\n\nCordialement,\nL'équipe SmartPlate",
                        'Déception' => "Bonjour,\n\nNous sommes sincèrement désolés que votre expérience n'ait pas été satisfaisante. Nous mettons tout en œuvre pour nous améliorer et espérons regagner votre confiance très bientôt.\n\nCordialement,\nL'équipe SmartPlate",
                        'Satisfaction' => "Bonjour,\n\nMerci beaucoup pour votre retour positif ! Nous sommes ravis que votre expérience vous ait donné entière satisfaction. À très vite !\n\nCordialement,\nL'équipe SmartPlate"
                    ];
                    
                    $suggestion = $predefinedResponses[$sujet] ?? "Bonjour,\n\nMerci pour votre message. Nous traitons actuellement votre demande.\n\nCordialement,\nL'équipe SmartPlate";
                    ?>
                    
                    <div style="background-color: #f1f8ff; border: 1px solid #cce5ff; padding: 15px; border-radius: 4px; margin-bottom: 20px;">
                        <h4 style="margin-top: 0; color: #004085; font-size: 0.95em;">💡 Suggestion intelligente (Basée sur le sujet : <?php echo htmlspecialchars((string) $sujet, ENT_QUOTES, 'UTF-8'); ?>)</h4>
                        <p style="font-size: 0.85em; color: #555; margin-bottom: 10px; font-style: italic;">"<?php echo nl2br(htmlspecialchars(substr($suggestion, 0, 100), ENT_QUOTES, 'UTF-8')) . '...'; ?>"</p>
                        <button type="button" id="use-suggestion-btn" style="background-color: #007bff; color: white; border: none; padding: 6px 12px; border-radius: 3px; cursor: pointer; font-size: 0.85em;">Utiliser cette réponse pré-rédigée</button>
                        <textarea id="suggestion-text" style="display:none;"><?php echo htmlspecialchars($suggestion, ENT_QUOTES, 'UTF-8'); ?></textarea>
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
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const useBtn = document.getElementById('use-suggestion-btn');
            const suggestionText = document.getElementById('suggestion-text');
            const textarea = document.getElementById('reponse');
            
            if (useBtn && suggestionText && textarea) {
                useBtn.addEventListener('click', function() {
                    textarea.value = suggestionText.value;
                    textarea.focus();
                });
            }
        });
    </script>
</body>
</html>
