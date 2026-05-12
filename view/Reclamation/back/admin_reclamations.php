<?php
require_once __DIR__ . '/../../config/auth.php';
requireAuthentication('../login.php');
requireRole('admin', '../index.php');

require_once __DIR__ . '/../../../controller/Reclamation/ReclamationController.php';
require_once __DIR__ . '/../../../controller/Reclamation/ResponseController.php';

$currentUser = getCurrentUser();
$adminName = (string) ($currentUser['name'] ?? 'Admin');

$reclamationController = new ReclamationController();
$responseController = new ResponseController();

// Use dynamic SQL filtering
$reclamations = $reclamationController->filter($_GET);

$searchDateDebut = $_GET['date_debut'] ?? '';
$searchDateFin = $_GET['date_fin'] ?? '';
$searchSujet = $_GET['sujet'] ?? '';
$searchPriorite = $_GET['priorite'] ?? '';
$searchStatut = $_GET['statut'] ?? '';
$sortOrder = $_GET['sort_date'] ?? 'desc';

$totalCount = count($reclamations);
$resolvedCount = 0;
$pendingCount = 0;

$latestResponses = [];
foreach ($reclamations as $reclamation) {
    $id = (int) $reclamation->getId();
    $latestResponses[$id] = $responseController->getLatestByReclamationId($id);

    if ($reclamation->getStatut() === 'Traité') {
        $resolvedCount++;
    } elseif ($reclamation->getStatut() === 'En attente') {
        $pendingCount++;
    }
}

$successMessage = null;
if (isset($_GET['sent']) && $_GET['sent'] === '1') {
    $successMessage = 'La réponse a été envoyée avec succès.';
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SmartPlate - Réclamations Admin</title>
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
            <a href="../../Produit/Admin_Produits.php" class="menu-item">📦 Produits & Boutiques</a>
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
                    <h1>Gestion des réclamations utilisateurs</h1>
                    <p>Consultez chaque demande et répondez directement depuis le back office.</p>
                </div>
            </div>

            <div class="kpi-grid">
                <div class="card kpi-card">
                    <div class="kpi-icon yellow">📝</div>
                    <div class="kpi-info">
                        <h3><?php echo $totalCount; ?></h3>
                        <span>Réclamations totales</span>
                    </div>
                </div>
                <div class="card kpi-card">
                    <div class="kpi-icon red">⏳</div>
                    <div class="kpi-info">
                        <h3><?php echo $pendingCount; ?></h3>
                        <span>En attente de réponse</span>
                    </div>
                </div>
                <div class="card kpi-card">
                    <div class="kpi-icon green">✅</div>
                    <div class="kpi-info">
                        <h3><?php echo $resolvedCount; ?></h3>
                        <span>Répondues</span>
                    </div>
                </div>
            </div>

            <?php if ($successMessage !== null): ?>
                <p class="alert-admin success"><?php echo htmlspecialchars($successMessage, ENT_QUOTES, 'UTF-8'); ?></p>
            <?php endif; ?>

            <div class="card">
                <div class="card-header" style="display: flex; flex-direction: column; gap: 15px;">
                    <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap;">
                        <h2>Liste des réclamations reçues</h2>
                    </div>
                    <form method="GET" action="admin_reclamations.php" style="display: flex; gap: 10px; align-items: center; flex-wrap: wrap; background-color: #f8f9fa; padding: 15px; border-radius: 8px;">
                        
                        <div style="display: flex; flex-direction: column;">
                            <label style="font-size: 0.8em; margin-bottom: 2px;">De</label>
                            <input type="date" name="date_debut" value="<?php echo htmlspecialchars($searchDateDebut, ENT_QUOTES, 'UTF-8'); ?>" style="padding: 6px; border: 1px solid #ddd; border-radius: 4px;" title="Date de début">
                        </div>

                        <div style="display: flex; flex-direction: column;">
                            <label style="font-size: 0.8em; margin-bottom: 2px;">À</label>
                            <input type="date" name="date_fin" value="<?php echo htmlspecialchars($searchDateFin, ENT_QUOTES, 'UTF-8'); ?>" style="padding: 6px; border: 1px solid #ddd; border-radius: 4px;" title="Date de fin">
                        </div>
                        
                        <div style="display: flex; flex-direction: column;">
                            <label style="font-size: 0.8em; margin-bottom: 2px;">Sujet</label>
                            <select name="sujet" style="padding: 6px; border: 1px solid #ddd; border-radius: 4px;" title="Filtrer par sujet">
                                <option value="">Tous les sujets</option>
                                <option value="Prix" <?php echo $searchSujet === 'Prix' ? 'selected' : ''; ?>>Prix</option>
                                <option value="Livraison" <?php echo $searchSujet === 'Livraison' ? 'selected' : ''; ?>>Livraison</option>
                                <option value="Goût" <?php echo $searchSujet === 'Goût' ? 'selected' : ''; ?>>Goût</option>
                                <option value="Service" <?php echo $searchSujet === 'Service' ? 'selected' : ''; ?>>Service</option>
                                <option value="Déception" <?php echo $searchSujet === 'Déception' ? 'selected' : ''; ?>>Déception</option>
                                <option value="Satisfaction" <?php echo $searchSujet === 'Satisfaction' ? 'selected' : ''; ?>>Satisfaction</option>
                            </select>
                        </div>

                        <div style="display: flex; flex-direction: column;">
                            <label style="font-size: 0.8em; margin-bottom: 2px;">Priorité</label>
                            <select name="priorite" style="padding: 6px; border: 1px solid #ddd; border-radius: 4px;" title="Filtrer par priorité">
                                <option value="">Toutes</option>
                                <option value="Urgent" <?php echo $searchPriorite === 'Urgent' ? 'selected' : ''; ?>>Urgent</option>
                                <option value="Moyen" <?php echo $searchPriorite === 'Moyen' ? 'selected' : ''; ?>>Moyen</option>
                                <option value="Faible" <?php echo $searchPriorite === 'Faible' ? 'selected' : ''; ?>>Faible</option>
                            </select>
                        </div>

                        <div style="display: flex; flex-direction: column;">
                            <label style="font-size: 0.8em; margin-bottom: 2px;">Statut</label>
                            <select name="statut" style="padding: 6px; border: 1px solid #ddd; border-radius: 4px;" title="Filtrer par statut">
                                <option value="">Tous</option>
                                <option value="En attente" <?php echo $searchStatut === 'En attente' ? 'selected' : ''; ?>>En attente</option>
                                <option value="En cours" <?php echo $searchStatut === 'En cours' ? 'selected' : ''; ?>>En cours</option>
                                <option value="Traité" <?php echo $searchStatut === 'Traité' ? 'selected' : ''; ?>>Traité</option>
                                <option value="Rejeté" <?php echo $searchStatut === 'Rejeté' ? 'selected' : ''; ?>>Rejeté</option>
                            </select>
                        </div>
                        
                        <div style="display: flex; flex-direction: column;">
                            <label style="font-size: 0.8em; margin-bottom: 2px;">Trier par</label>
                            <select name="sort_date" style="padding: 6px; border: 1px solid #ddd; border-radius: 4px;" title="Trier par date">
                                <option value="desc" <?php echo $sortOrder === 'desc' ? 'selected' : ''; ?>>Plus récentes</option>
                                <option value="asc" <?php echo $sortOrder === 'asc' ? 'selected' : ''; ?>>Plus anciennes</option>
                            </select>
                        </div>

                        <div style="display: flex; align-items: flex-end; padding-bottom: 2px; gap: 5px;">
                            <button type="submit" style="padding: 7px 16px; background-color: #20c997; color: white; border: none; border-radius: 4px; cursor: pointer; height: 32px;">Appliquer</button>
                            <?php if ($searchDateDebut !== '' || $searchDateFin !== '' || $searchSujet !== '' || $searchPriorite !== '' || $searchStatut !== '' || $sortOrder !== 'desc'): ?>
                                <a href="admin_reclamations.php" style="padding: 6px 16px; background-color: #fff; color: #333; border: 1px solid #ddd; border-radius: 4px; text-decoration: none; height: 32px; box-sizing: border-box; display: inline-flex; align-items: center;">Réinitialiser</a>
                            <?php endif; ?>
                        </div>
                    </form>
                </div>

                <?php if (empty($reclamations)): ?>
                    <p class="empty-admin">Aucune réclamation disponible pour le moment.</p>
                <?php else: ?>
                    <?php 
                    $groupedReclamations = [];
                    foreach ($reclamations as $r) {
                        $sujetGroup = $r->getSujet() ?: 'Non spécifié';
                        // Clean up legacy "[Sujet]" prefixes if any
                        if (preg_match('/^\[(.*?)\]/', $sujetGroup, $matches)) {
                            $sujetGroup = $matches[1];
                        }
                        if (!isset($groupedReclamations[$sujetGroup])) {
                            $groupedReclamations[$sujetGroup] = [];
                        }
                        $groupedReclamations[$sujetGroup][] = $r;
                    }
                    ?>
                    
                    <?php foreach ($groupedReclamations as $sujetGroup => $recs): ?>
                        <div style="background-color: #f8f9fa; padding: 10px 15px; margin-top: 30px; margin-bottom: 15px; border-left: 4px solid #20c997; border-radius: 4px;">
                            <h3 style="margin: 0; color: #333; font-size: 1.1em;">Réclamations : <?php echo htmlspecialchars($sujetGroup, ENT_QUOTES, 'UTF-8'); ?> <span style="font-size: 0.8em; color: #666; font-weight: normal;">(<?php echo count($recs); ?>)</span></h3>
                        </div>
                        <div class="admin-table-wrap">
                            <table class="admin-table">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Date</th>
                                        <th>Client</th>
                                        <th>Priorité & SLA</th>
                                        <th>Message</th>
                                        <th>Statut</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($recs as $reclamation): ?>
                                        <?php
                                        $id = (int) $reclamation->getId();
                                        $latestResponse = $latestResponses[$id] ?? null;
                                        $prio = $reclamation->getPriorite();
                                        $prioColor = $prio === 'Urgent' ? '#dc3545' : ($prio === 'Moyen' ? '#fd7e14' : '#28a745');
                                        
                                        // Calcul du SLA
                                        $dateCreation = new DateTime($reclamation->getDateCreation());
                                        $slaDelays = ['Urgent' => '+1 day', 'Moyen' => '+3 days', 'Faible' => '+7 days'];
                                        $delay = $slaDelays[$prio] ?? '+7 days';
                                        
                                        $deadline = clone $dateCreation;
                                        $deadline->modify($delay);
                                        $now = new DateTime();
                                        
                                        $isSlaExceeded = ($reclamation->getStatut() === 'En attente' && $now > $deadline);
                                        $rowStyle = $isSlaExceeded ? 'background-color: #fff3f3; border-left: 3px solid #dc3545;' : '';
                                        ?>
                                        <tr style="<?php echo $rowStyle; ?>">
                                            <td>
                                                <?php echo $id; ?>
                                                <?php if ($isSlaExceeded): ?>
                                                    <br><span style="color: #dc3545; font-size: 1.2em;" title="SLA Dépassé !">⚠️</span>
                                                <?php endif; ?>
                                            </td>
                                            <td><?php echo htmlspecialchars((string) $reclamation->getDateCreation(), ENT_QUOTES, 'UTF-8'); ?></td>
                                            <td>
                                                <?php echo htmlspecialchars((string) $reclamation->getNomClient(), ENT_QUOTES, 'UTF-8'); ?><br>
                                                <small style="color: #666;"><?php echo htmlspecialchars((string) $reclamation->getEmail(), ENT_QUOTES, 'UTF-8'); ?></small>
                                            </td>
                                            <td>
                                                <span style="background-color: <?php echo $prioColor; ?>; color: white; padding: 2px 6px; border-radius: 4px; font-size: 0.85em;"><?php echo htmlspecialchars((string) $prio, ENT_QUOTES, 'UTF-8'); ?></span><br>
                                                <small style="color: <?php echo $isSlaExceeded ? '#dc3545' : '#666'; ?>; font-weight: <?php echo $isSlaExceeded ? 'bold' : 'normal'; ?>;">
                                                    Échéance: <?php echo $deadline->format('Y-m-d'); ?>
                                                </small>
                                            </td>
                                            <td><?php echo nl2br(htmlspecialchars((string) $reclamation->getMessage(), ENT_QUOTES, 'UTF-8')); ?></td>
                                            <td>
                                                <?php if ($reclamation->getStatut() === 'En attente'): ?>
                                                    <span class="status-pill pending">En attente</span>
                                                <?php elseif ($reclamation->getStatut() === 'En cours'): ?>
                                                    <span class="status-pill" style="background-color: #fff3cd; color: #856404; padding: 4px 8px; border-radius: 20px; font-size: 0.85em; font-weight: 500;">En cours</span>
                                                <?php else: ?>
                                                    <span class="status-pill resolved"><?php echo htmlspecialchars((string) $reclamation->getStatut(), ENT_QUOTES, 'UTF-8'); ?></span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <a href="admin_reply_reclamation.php?id=<?php echo $id; ?>" class="btn-action">
                                                    <?php echo $latestResponse === null ? 'Répondre' : 'Voir / Répondre'; ?>
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </main>

</body>
</html>
