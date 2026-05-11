<?php
session_start();
require_once __DIR__ . "/../../../config.php";
require_once __DIR__ . "/../../../Models/Recette/RecommandationMetier.php";
require_once __DIR__ . "/../../../controller/User/UserController.php";
require_once __DIR__ . '/../../../Models/Suivi_Nutritionnel/Objectif_Class.php';
require_once __DIR__ . '/../../../Models/Suivi_Nutritionnel/Journal_Class.php';
require_once __DIR__ . '/../../../Models/Suivi_Nutritionnel/Repas_Class.php';

/* ===================== OUTILS ===================== */
function nettoyer($val){
    return trim($val ?? '');
}

// Fonction utilitaire pour retrouver une photo de profil dans /view/User/uploads/profile_pictures
function getProfilePhotoWebPath(int $userId): ?string {
    $baseDir = __DIR__ . '/../../User/uploads/profile_pictures';
    $baseWeb = '../../User/uploads/profile_pictures';
    $extensions = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
    foreach ($extensions as $ext) {
        $filePath = $baseDir . '/user_' . $userId . '.' . $ext;
        if (is_file($filePath)) {
            return $baseWeb . '/user_' . $userId . '.' . $ext . '?v=' . filemtime($filePath);
        }
    }
    return null;
}

function calculerScoreSante($calories, $proteines, $lipides, $glucides){
    $score = 100;

    if($calories > 800) $score -= 20;
    elseif($calories > 600) $score -= 10;

    if($lipides > 30) $score -= 15;
    elseif($lipides > 20) $score -= 8;

    if($glucides > 80) $score -= 10;
    elseif($glucides > 60) $score -= 5;

    if($proteines >= 20) $score += 10;
    elseif($proteines >= 10) $score += 5;

    if($score > 100) $score = 100;
    if($score < 0) $score = 0;

    return $score;
}

function badgeRecette($calories, $proteines, $glucides){
    if($proteines >= 20) return "Riche en protéines";
    if($glucides < 25) return "Faible en glucides";
    if($calories < 450) return "Healthy";
    return "Équilibré";
}

/* ===================== PROFIL UTILISATEUR ===================== */
$profil = [
    'nom_utilisateur' => 'Utilisateur Test',
    'objectif' => 'perte_poids',
    'temps_max' => 20,
    'calories_max' => 450
];

$isObjectifLocked = false;
$cibleCalories = 0;
$cibleProteines = 0;
$cibleLipides = 0;
$cibleGlucides = 0;

$totalCaloriesConsommees = 0;
$totalProteinesConsommees = 0;
$totalGlucidesConsommees = 0;
$totalLipidesConsommees = 0;
$percentCalories = 0;

// Intégration User -> Recette (si user connecté via module User)
if (!empty($_SESSION['user_email'])) {
    try {
        $userC = new UserController();
        $userInfo = $userC->getUserByEmail((string) $_SESSION['user_email']);
        if ($userInfo) {
            $id_utilisateur_connecte = (int)$userInfo['id'];
            $profil['nom_utilisateur'] = trim(($userInfo['prenom'] ?? '') . ' ' . ($userInfo['nom'] ?? '')) ?: ($userInfo['email'] ?? $profil['nom_utilisateur']);
            
            // On vérifie s'il y a un objectif nutritionnel actif
            $objectifActif = Objectif::getActif($id_utilisateur_connecte);
            if ($objectifActif) {
                $isObjectifLocked = true;
                $profil['objectif'] = $objectifActif['type_objectif'];
                
                $poidsRef = Journal::getFirstWeight($id_utilisateur_connecte) ?? 75;
                $typeObj = $profil['objectif'];
                
                if ($typeObj == 'perte_poids') {
                    $cibleCalories = $poidsRef * 22;
                    $cibleProteines = $poidsRef * 2.2;
                    $cibleLipides = $poidsRef * 0.8;
                } elseif ($typeObj == 'prise_masse') {
                    $cibleCalories = $poidsRef * 30;
                    $cibleProteines = $poidsRef * 1.8;
                    $cibleLipides = $poidsRef * 1;
                } else {
                    $cibleCalories = $poidsRef * 25;
                    $cibleProteines = $poidsRef * 1.5;
                    $cibleLipides = $poidsRef * 0.9;
                }
                
                $caloriesRestantes = $cibleCalories - ($cibleProteines * 4) - ($cibleLipides * 9);
                $cibleGlucides = $caloriesRestantes / 4;

                $cibleCalories = round($cibleCalories);
                $cibleProteines = round($cibleProteines);
                $cibleGlucides = round($cibleGlucides);
                $cibleLipides = round($cibleLipides);

                // Récupération des repas du jour pour afficher la consommation
                $latestJournal = Journal::getLatest($id_utilisateur_connecte);
                if ($latestJournal && !empty($latestJournal['id_journal'])) {
                    $repasList = Repas::listeParJournal((int)$latestJournal['id_journal']);
                    foreach ($repasList as $rp) {
                        $totalCaloriesConsommees += floatval($rp['nbre_calories'] ?? 0);
                        $totalProteinesConsommees += floatval($rp['proteine'] ?? 0);
                        $totalGlucidesConsommees += floatval($rp['glucide'] ?? 0);
                        $totalLipidesConsommees += floatval($rp['lipide'] ?? 0);
                    }
                }
                $percentCalories = $cibleCalories > 0 ? round(min(100, ($totalCaloriesConsommees / $cibleCalories) * 100)) : 0;
            }
        }
    } catch (Exception $e) {
        // fallback profil par défaut
    }
}

// On écrase le profil en BDD si l'objectif n'est pas "locké" par le module Suivi_Nutritionnel
if (!$isObjectifLocked) {
    try {
        $profilBDD = $pdo->query("SELECT * FROM profil_utilisateur ORDER BY id_profil DESC LIMIT 1")->fetch(PDO::FETCH_ASSOC);
        if ($profilBDD) {
            $profil = $profilBDD;
        }
    } catch (Exception $e) {
        // garder le profil par défaut
    }
}

if (isset($_POST['appliquer_profil'])) {
    if (!$isObjectifLocked) {
        $profil['objectif'] = $_POST['objectif'] ?? $profil['objectif'];
    }
    $profil['temps_max'] = (int)($_POST['temps_max'] ?? $profil['temps_max']);
    $profil['calories_max'] = (int)($_POST['calories_max'] ?? $profil['calories_max']);
}

/* ===================== TABLE AVIS ===================== */
$messageAvis = "";

try{
    $pdo->query("SELECT 1 FROM avis LIMIT 1");
    $tableAvisExiste = true;
}catch(Exception $e){
    $tableAvisExiste = false;
}

/* ===================== AJOUT AVIS ===================== */
if(isset($_POST['envoyer_avis']) && $tableAvisExiste){
    $nomUser = nettoyer($_POST['nom_user'] ?? 'Utilisateur');
    $note = (int)($_POST['note'] ?? 0);
    $commentaire = nettoyer($_POST['commentaire'] ?? '');

    $userIdAvis = null;
    if (!empty($_SESSION['user_email']) && isset($userInfo) && $userInfo) {
        $userIdAvis = (int)$userInfo['id'];
        $nomUserTemp = trim(($userInfo['prenom'] ?? '') . ' ' . ($userInfo['nom'] ?? ''));
        $nomUser = $nomUserTemp ?: ($userInfo['email'] ?? $nomUser);
    }

    if($nomUser === "") $nomUser = "Utilisateur";

    if($note >= 1 && $note <= 5 && mb_strlen($commentaire) >= 5 && mb_strlen($commentaire) <= 500){
        $sql = "INSERT INTO avis (nom_user, note, commentaire, date_avis, id_user) VALUES (?, ?, ?, NOW(), ?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$nomUser, $note, $commentaire, $userIdAvis]);
        $messageAvis = "Avis envoyé avec succès.";
    } else {
        $messageAvis = "Veuillez saisir une note entre 1 et 5 et un commentaire entre 5 et 500 caractères.";
    }
}

/* ===================== RECUPERATION RECETTES ===================== */
$recettes = [];
$recettesRecommandees = [];

try{
    $recettes = $pdo->query("
        SELECT *
        FROM recette
        ORDER BY id_recette DESC
    ")->fetchAll(PDO::FETCH_ASSOC);

    $recettesRecommandees = RecommandationMetier::recommanderRecettes($recettes, $profil);
    $recettesRecommandees = array_slice($recettesRecommandees, 0, 4);

}catch(Exception $e){
    $recettes = [];
    $recettesRecommandees = [];
}

/* ===================== RECUPERATION AVIS ===================== */
$avisRecents = [];
if($tableAvisExiste){
    try{
        $avisRecents = $pdo->query("
            SELECT *
            FROM avis
            ORDER BY id_avis DESC
            LIMIT 3
        ")->fetchAll(PDO::FETCH_ASSOC);
    }catch(Exception $e){
        $avisRecents = [];
    }
}

/* ===================== STATS FRONT ===================== */
$totalCalories = 0;
$totalProteines = 0;
$totalLipides = 0;
$totalGlucides = 0;

foreach($recettesRecommandees as $r){
    $totalCalories += (float)$r['calories'];
    $totalProteines += (float)$r['proteines'];
    $totalLipides += (float)$r['lipides'];
    $totalGlucides += (float)$r['glucides'];
}

$scoreSante = calculerScoreSante($totalCalories, $totalProteines, $totalLipides, $totalGlucides);
$progressPercent = min(100, round(($totalCalories / 2100) * 100));

$dateAuj = date('d/m/Y');
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FrontOffice SmartPlate</title>
    <link rel="stylesheet" href="templates/stylefront.css">
</head>
<body>

<?php include __DIR__ . '/../../front_sidebar.php'; ?>

<main class="dashboard">
    <div class="dashboard-header">
        <div>
            <h1>Mon espace nutrition</h1>
            <p>Bonjour 👋 Prenez soin de vous aujourd’hui !</p>
        </div>
        <span class="date-badge"><?= htmlspecialchars($dateAuj) ?></span>
    </div>

    <div class="card">
        <div class="card-header">
            <h2>Mon profil de recommandation</h2>
            <span class="badge blue-light">Personnalisation</span>
        </div>

        <form method="POST" class="modern-form">
            <div class="form-row">
                <div class="form-group">
                    <label for="objectif">Objectif</label>
                    <select name="objectif" id="objectif" class="form-control" <?= $isObjectifLocked ? 'disabled' : '' ?>>
                        <option value="perte_poids" <?= $profil['objectif'] === 'perte_poids' ? 'selected' : '' ?>>Perte de poids</option>
                        <option value="maintien" <?= $profil['objectif'] === 'maintien' ? 'selected' : '' ?>>Maintien</option>
                        <option value="prise_masse" <?= $profil['objectif'] === 'prise_masse' ? 'selected' : '' ?>>Prise de masse</option>
                    </select>
                    <?php if ($isObjectifLocked): ?>
                        <input type="hidden" name="objectif" value="<?= htmlspecialchars($profil['objectif']) ?>">
                        <small style="color:var(--admin-green);font-size:0.75rem;">Verrouillé par votre suivi nutritionnel</small>
                    <?php endif; ?>
                </div>

                <div class="form-group">
                    <label for="temps_max">Temps max (min)</label>
                    <input type="text" name="temps_max" id="temps_max" class="form-control" value="<?= htmlspecialchars($profil['temps_max']) ?>">
                </div>

                <div class="form-group">
                    <label for="calories_max">Calories max</label>
                    <input type="text" name="calories_max" id="calories_max" class="form-control" value="<?= htmlspecialchars($profil['calories_max']) ?>">
                </div>
            </div>

            <div class="form-actions">
                <div></div>
                <div class="right-actions">
                    <button type="submit" name="appliquer_profil" class="btn-main">Appliquer mes critères</button>
                </div>
            </div>
        </form>
    </div>

    <div class="journal-grid">

        <div class="card health-card">
            <div class="card-header">
                <h2>Score santé</h2>
                <span class="badge white">Vue globale</span>
            </div>

            <div class="score-display">
                <div class="score-number"><?= $scoreSante ?></div>
                <div>
                    <div class="badge green-light">
                        <?= $scoreSante >= 80 ? 'Excellent' : ($scoreSante >= 60 ? 'Bien' : 'À améliorer') ?>
                    </div>
                </div>
            </div>

            <div class="progress-bar-container">
                <div class="progress-bar" style="width: <?= $scoreSante ?>%;"></div>
            </div>

            <p class="encouragement">
                Votre score est calculé selon les recettes recommandées pour votre profil.
            </p>
        </div>

        <div class="card <?= $isObjectifLocked ? 'progress-card' : '' ?>">
            <div class="card-header">
                <h2>Apports recommandés</h2>
                <?php if ($isObjectifLocked): ?>
                    <span class="badge yellow">Objectif : <?= $cibleCalories ?> kcal</span>
                <?php else: ?>
                    <span class="badge blue-light"><?= $progressPercent ?>%</span>
                <?php endif; ?>
            </div>

            <div class="progress-content">
                <?php if ($isObjectifLocked): ?>
                    <div class="circle-chart">
                        <div class="circle-inner">
                            <span class="percentage"><?= $percentCalories ?>%</span>
                            <span class="label">Complété</span>
                        </div>
                    </div>
                    <div class="macros-summary">
                        <div class="macro-item"><span class="dot blue"></span> Glucides <span><?= round($totalGlucidesConsommees) ?>g / <?= $cibleGlucides ?>g</span></div>
                        <div class="macro-item"><span class="dot yellow"></span> Protéines <span><?= round($totalProteinesConsommees) ?>g / <?= $cibleProteines ?>g</span></div>
                        <div class="macro-item"><span class="dot green"></span> Lipides <span><?= round($totalLipidesConsommees) ?>g / <?= $cibleLipides ?>g</span></div>
                    </div>
                <?php else: ?>
                    <div class="circle-chart">
                        <div class="circle-inner">
                            <span class="percentage"><?= $progressPercent ?>%</span>
                            <span class="label">objectif</span>
                        </div>
                    </div>

                    <div class="macros-summary">
                        <div class="macro-item">
                            <div class="macro-title"><span class="dot blue"></span>Calories</div>
                            <span><?= round($totalCalories) ?> kcal</span>
                        </div>
                        <div class="macro-item">
                            <div class="macro-title"><span class="dot green"></span>Protéines</div>
                            <span><?= round($totalProteines) ?> g</span>
                        </div>
                        <div class="macro-item">
                            <div class="macro-title"><span class="dot yellow"></span>Lipides</div>
                            <span><?= round($totalLipides) ?> g</span>
                        </div>
                        <div class="macro-item">
                            <div class="macro-title"><span class="dot blue"></span>Glucides</div>
                            <span><?= round($totalGlucides) ?> g</span>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <section class="meals-section" id="recettes">
        <div class="section-header">
            <h2>Recettes recommandées pour vous</h2>
            <button class="btn-add-main" type="button">Profil : <?= htmlspecialchars(str_replace('_', ' ', $profil['objectif'])) ?></button>
        </div>

        <?php if(!empty($recettesRecommandees)){ ?>
            <?php foreach($recettesRecommandees as $r){ ?>
                <div class="meal-card">
                    <div class="meal-image">
                        <?php if (!empty($r['image'])) { ?>
                            <img src="../../../images/<?= htmlspecialchars($r['image']) ?>" alt="recette">
                        <?php } else { ?>
                            <img src="https://via.placeholder.com/140x100?text=Recette" alt="recette">
                        <?php } ?>
                    </div>

                    <div class="meal-info">
                        <div class="meal-tags">
                            <span class="badge green-light"><?= htmlspecialchars(badgeRecette($r['calories'], $r['proteines'], $r['glucides'])) ?></span>
                            <span class="time-info"><?= (int)$r['temps_preparation'] ?> min</span>
                        </div>

                        <h3><?= htmlspecialchars($r['nom_recette']) ?></h3>
                        <p><?= htmlspecialchars($r['description']) ?></p>
                        <p><strong>Score de recommandation :</strong> <?= (int)$r['score_recommandation'] ?></p>
                        <p class="small-muted"><?= htmlspecialchars($r['explication_recommandation']) ?></p>
                    </div>

                    <div class="meal-stats">
                        <div class="stat-col">
                            <div class="stat-val">🔥 <?= round($r['calories']) ?> kcal</div>
                            <div class="stat-val">P <?= round($r['proteines']) ?> g</div>
                        </div>

                        <div class="stat-col">
                            <div class="stat-val">L <?= round($r['lipides']) ?> g</div>
                            <div class="stat-val">G <?= round($r['glucides']) ?> g</div>
                        </div>
                    </div>

                    <div class="meal-actions">
                        <a class="btn-action" href="recette-details.php?id=<?= $r['id_recette'] ?>">Voir la recette</a>
                        <button class="btn-icon" type="button">♡</button>
                    </div>
                </div>
            <?php } ?>
        <?php } else { ?>
            <div class="card">
                <p>Aucune recette ne correspond à votre profil pour le moment.</p>
            </div>
        <?php } ?>
    </section>

    <section class="front-review-layout" id="avis">

        <div class="card form-section">
            <div class="card-header">
                <h2>Donner votre avis</h2>
                <span class="badge yellow">Feedback utilisateur</span>
            </div>

            <?php if($messageAvis !== ""){ ?>
                <div class="front-alert"><?= htmlspecialchars($messageAvis) ?></div>
            <?php } ?>

            <form method="POST" class="modern-form" id="avisForm" novalidate>
                <div class="form-row">
                    <div class="form-group">
                        <label for="nom_user">Votre nom</label>
                        <?php 
                        $defaultName = "";
                        $isReadOnly = "";
                        if (!empty($userInfo)) {
                            $defaultName = trim(($userInfo['prenom'] ?? '') . ' ' . ($userInfo['nom'] ?? '')) ?: ($userInfo['email'] ?? '');
                            $isReadOnly = 'readonly style="background-color: #f1f5f9; cursor: not-allowed;"';
                        }
                        ?>
                        <input type="text" id="nom_user" name="nom_user" class="form-control" placeholder="Ex: Amira" value="<?= htmlspecialchars($defaultName) ?>" <?= $isReadOnly ?>>
                    </div>
                </div>

                <div class="form-group">
                    <label>Votre note</label>
                    <div class="review-stars" id="reviewStars">
                        <span data-value="1">★</span>
                        <span data-value="2">★</span>
                        <span data-value="3">★</span>
                        <span data-value="4">★</span>
                        <span data-value="5">★</span>
                    </div>
                    <input type="hidden" name="note" id="note" value="0">
                </div>

                <div class="form-group">
                    <label for="commentaire">Votre commentaire</label>
                    <textarea
                        id="commentaire"
                        name="commentaire"
                        class="form-control"
                        rows="5"
                        placeholder="Partagez votre expérience avec SmartPlate..."
                    ></textarea>
                    <div class="small-muted"><span id="countAvis">0</span>/500</div>
                </div>

                <div class="form-actions">
                    <div></div>
                    <div class="right-actions">
                        <button type="button" class="btn-secondary" id="resetAvis">Annuler</button>
                        <button type="submit" name="envoyer_avis" class="btn-main">Envoyer avis</button>
                    </div>
                </div>
            </form>
        </div>

        <div class="card">
            <div class="card-header">
                <h2>Avis récents</h2>
                <span class="badge blue-light">Utilisateurs</span>
            </div>

            <?php if($tableAvisExiste && !empty($avisRecents)){ ?>
                <?php foreach($avisRecents as $avis){ 
                    $avisAvatar = 'https://i.pravatar.cc/80?u=' . urlencode($avis['nom_user']);
                    if (!empty($avis['id_user'])) {
                        $profilePhotoPath = getProfilePhotoWebPath((int)$avis['id_user']);
                        if ($profilePhotoPath) {
                            $avisAvatar = $profilePhotoPath;
                        }
                    }
                ?>
                    <div class="recent-review-item">
                        <img class="recent-avatar" src="<?= htmlspecialchars($avisAvatar) ?>" alt="avatar">
                        <div class="recent-review-content">
                            <div class="recent-review-top">
                                <strong><?= htmlspecialchars($avis['nom_user']) ?></strong>
                                <span class="small-muted">
                                    <?= !empty($avis['date_avis']) ? date('d/m/Y', strtotime($avis['date_avis'])) : '' ?>
                                </span>
                            </div>
                            <div class="recent-stars">
                                <?= str_repeat('★', (int)$avis['note']) . str_repeat('☆', 5 - (int)$avis['note']) ?>
                            </div>
                            <div class="recent-comment">
                                <?= htmlspecialchars($avis['commentaire']) ?>
                            </div>
                        </div>
                    </div>
                <?php } ?>
            <?php } elseif(!$tableAvisExiste) { ?>
                <p class="small-muted">La table <strong>avis</strong> n’existe pas encore dans la base.</p>
            <?php } else { ?>
                <p class="small-muted">Aucun avis pour le moment.</p>
            <?php } ?>
        </div>

        <div class="assistant-card" id="assistant">
            <div class="assistant-title">
                <h3>Chatbot nutritionnel</h3>
                <span class="badge green-light">En ligne</span>
            </div>

            <div class="assistant-bubble">
                Bonjour 👋 Je peux te proposer une recette rapide, healthy, riche en protéines, selon tes ingrédients, ou t’aider pour le planning.
            </div>

            <div class="assistant-quick">
                <a href="#" class="quick-question" data-question="Je veux une recette rapide">Recette rapide</a>
                <a href="#" class="quick-question" data-question="Je veux une recette healthy">Recette healthy</a>
                <a href="#" class="quick-question" data-question="Je veux une recette riche en protéines">Recette protéinée</a>
                <a href="#" class="quick-question" data-question="Je veux le planning de la semaine">Planning semaine</a>
            </div>

            <div class="sp-chat">
                <div class="sp-chat-messages" id="chatMessages">
                    <div class="sp-chat-row bot">
                        <div class="sp-chat-bubble">
                            Bonjour, je suis ton assistant nutritionnel SmartPlate. Pose-moi une question.
                            <div class="sp-chat-meta">Assistant</div>
                        </div>
                    </div>
                </div>

                <div class="sp-chat-composer">
                    <input type="text" id="chatInput" class="form-control sp-chat-input" placeholder="Exemple : je veux une recette avec tomate">
                    <button type="button" id="chatSendBtn" class="btn-main sp-chat-send">Envoyer</button>
                </div>

                <div class="sp-chat-hint">
                    Exemples : recette rapide, recette healthy, recette avec tomate, coût estimé, planning semaine.
                </div>
            </div>
        </div>
    </section>
</main>

<script>
document.addEventListener("DOMContentLoaded", function(){
    // sidebar recipes submenu toggle
    document.querySelectorAll('.nav-toggle').forEach(btn => {
        btn.addEventListener('click', function(){
            const parent = btn.closest('.nav-has-children');
            if(!parent) return;
            const sub = parent.querySelector('.sub-nav');
            const expanded = btn.getAttribute('aria-expanded') === 'true';
            btn.setAttribute('aria-expanded', expanded ? 'false' : 'true');
            if(sub) sub.style.display = expanded ? 'none' : 'block';
        });
    });
    const stars = document.querySelectorAll("#reviewStars span");
    const noteInput = document.getElementById("note");
    const commentaire = document.getElementById("commentaire");
    const countAvis = document.getElementById("countAvis");
    const resetAvis = document.getElementById("resetAvis");
    const avisForm = document.getElementById("avisForm");

    function updateStars(value){
        stars.forEach(star => {
            const starValue = parseInt(star.dataset.value);
            star.classList.toggle("active", starValue <= value);
        });
        noteInput.value = value;
    }

    stars.forEach(star => {
        star.addEventListener("click", function(){
            updateStars(parseInt(this.dataset.value));
        });
    });

    if(commentaire && countAvis){
        commentaire.addEventListener("input", function(){
            countAvis.textContent = this.value.length;
        });
    }

    if(resetAvis){
        resetAvis.addEventListener("click", function(){
            updateStars(0);
            if(commentaire){
                commentaire.value = "";
                countAvis.textContent = "0";
            }
            const nom = document.getElementById("nom_user");
            if(nom) nom.value = "";
        });
    }

    if(avisForm){
        avisForm.addEventListener("submit", function(e){
            const note = parseInt(noteInput.value);
            const text = commentaire.value.trim();

            if(note < 1 || note > 5){
                e.preventDefault();
                alert("Veuillez sélectionner une note entre 1 et 5.");
                return;
            }

            if(text.length < 5){
                e.preventDefault();
                alert("Votre commentaire doit contenir au moins 5 caractères.");
            }

            if(text.length > 500){
                e.preventDefault();
                alert("Votre commentaire ne doit pas dépasser 500 caractères.");
            }
        });
    }

    const chatInput = document.getElementById("chatInput");
    const chatSendBtn = document.getElementById("chatSendBtn");
    const chatMessages = document.getElementById("chatMessages");
    const quickQuestions = document.querySelectorAll(".quick-question");

    function ajouterMessage(role, texte) {
        const row = document.createElement("div");
        row.className = "sp-chat-row " + role;

        const bubble = document.createElement("div");
        bubble.className = "sp-chat-bubble";
        bubble.innerHTML = `
            ${texte}
            <div class="sp-chat-meta">${role === "user" ? "Vous" : "Assistant"}</div>
        `;

        row.appendChild(bubble);
        chatMessages.appendChild(row);
        chatMessages.scrollTop = chatMessages.scrollHeight;
    }

    function envoyerQuestion(question) {
        const texte = (question || chatInput.value).trim();
        if (texte === "") return;

        ajouterMessage("user", texte);
        if (!question) chatInput.value = "";

        const formData = new FormData();
        formData.append("message", texte);

        fetch("chatbot_nutrition.php", {
            method: "POST",
            body: formData
        })
        .then(res => res.json())
        .then(data => {
            ajouterMessage("bot", data.message || "Je n’ai pas compris la demande.");
        })
        .catch(() => {
            ajouterMessage("bot", "Erreur de connexion avec le chatbot.");
        });
    }

    if (chatSendBtn) {
        chatSendBtn.addEventListener("click", function () {
            envoyerQuestion();
        });
    }

    if (chatInput) {
        chatInput.addEventListener("keydown", function (e) {
            if (e.key === "Enter") {
                e.preventDefault();
                envoyerQuestion();
            }
        });
    }

    quickQuestions.forEach(link => {
        link.addEventListener("click", function (e) {
            e.preventDefault();
            envoyerQuestion(this.dataset.question);
        });
    });
});
</script>

</body>
</html>