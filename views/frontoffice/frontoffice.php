<?php
require_once "../../config.php";
require_once __DIR__ . "/../../models/RecommandationMetier.php";

/* ===================== OUTILS ===================== */
function nettoyer($val){
    return trim($val ?? '');
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

try {
    $profilBDD = $pdo->query("SELECT * FROM profil_utilisateur ORDER BY id_profil DESC LIMIT 1")->fetch(PDO::FETCH_ASSOC);
    if ($profilBDD) {
        $profil = $profilBDD;
    }
} catch (Exception $e) {
    // garder le profil par défaut
}

if (isset($_POST['appliquer_profil'])) {
    $profil['objectif'] = $_POST['objectif'] ?? $profil['objectif'];
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

    if($nomUser === "") $nomUser = "Utilisateur";

    if($note >= 1 && $note <= 5 && mb_strlen($commentaire) >= 5 && mb_strlen($commentaire) <= 500){
        $sql = "INSERT INTO avis (nom_user, note, commentaire, date_avis) VALUES (?, ?, ?, NOW())";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$nomUser, $note, $commentaire]);
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

<aside class="sidebar">
    <div class="sidebar-logo">
        <div class="logo-icon">🥗</div>
        <h2>SmartPlate</h2>
    </div>

    <nav class="sidebar-nav">
        <div class="nav-section-title">Menu</div>
        <a href="frontoffice.php" class="nav-item active"><span class="icon">🏠</span>Dashboard</a>
        <a href="planning.php" class="nav-item"><span class="icon">📅</span>Planning</a>
        <a href="#recettes" class="nav-item"><span class="icon">🍽</span>Recettes</a>
        <a href="#avis" class="nav-item"><span class="icon">💬</span>Avis</a>
        <a href="#assistant" class="nav-item"><span class="icon">🤖</span>Chatbot</a>
    </nav>

    <div class="sidebar-footer">
        <div class="user-badge">
            <img src="https://i.pravatar.cc/80?img=32" alt="user">
            <div class="user-info">
                <span class="user-name"><?= htmlspecialchars($profil['nom_utilisateur']) ?></span>
                <span class="user-status">Profil personnalisé</span>
            </div>
        </div>
    </div>
</aside>

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
                    <select name="objectif" id="objectif" class="form-control">
                        <option value="perte_poids" <?= $profil['objectif'] === 'perte_poids' ? 'selected' : '' ?>>Perte de poids</option>
                        <option value="maintien" <?= $profil['objectif'] === 'maintien' ? 'selected' : '' ?>>Maintien</option>
                        <option value="prise_masse" <?= $profil['objectif'] === 'prise_masse' ? 'selected' : '' ?>>Prise de masse</option>
                    </select>
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

        <div class="card">
            <div class="card-header">
                <h2>Apports recommandés</h2>
                <span class="badge blue-light"><?= $progressPercent ?>%</span>
            </div>

            <div class="progress-content">
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
                        <img src="../../images/<?= htmlspecialchars($r['image'] ?: 'default.jpg') ?>" alt="recette">
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
                        <input type="text" id="nom_user" name="nom_user" class="form-control" placeholder="Ex: Amira">
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
                <?php foreach($avisRecents as $avis){ ?>
                    <div class="recent-review-item">
                        <img class="recent-avatar" src="https://i.pravatar.cc/80?u=<?= urlencode($avis['nom_user']) ?>" alt="avatar">
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