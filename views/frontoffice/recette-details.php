<?php
require_once "../../config.php";

function nettoyer($val){
    return trim($val ?? '');
}

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if($id <= 0){
    die("Recette introuvable.");
}

/* ===== RECETTE ===== */
$stmt = $pdo->prepare("SELECT * FROM recette WHERE id_recette = ?");
$stmt->execute([$id]);
$recette = $stmt->fetch(PDO::FETCH_ASSOC);

if(!$recette){
    die("Recette introuvable.");
}

/* ===== INGREDIENTS DE LA RECETTE ===== */
$stmtIng = $pdo->prepare("
    SELECT i.*
    FROM ingredient i
    INNER JOIN recette_ingredient ri ON i.id_ingredient = ri.ingredient_id
    WHERE ri.recette_id = ?
    ORDER BY i.nom_ingredient ASC
");
$stmtIng->execute([$id]);
$ingredients = $stmtIng->fetchAll(PDO::FETCH_ASSOC);

/* ===== AVIS SIMPLE SUR CETTE RECETTE ===== */
$messageAvisRecette = "";

try{
    $pdo->query("SELECT 1 FROM avis_recette LIMIT 1");
    $tableAvisRecetteExiste = true;
}catch(Exception $e){
    $tableAvisRecetteExiste = false;
}

if(isset($_POST['envoyer_avis_recette']) && $tableAvisRecetteExiste){
    $nomUser = nettoyer($_POST['nom_user'] ?? 'Utilisateur');
    $note = (int)($_POST['note'] ?? 0);
    $commentaire = nettoyer($_POST['commentaire'] ?? '');

    if($nomUser === "") $nomUser = "Utilisateur";

    if($note >= 1 && $note <= 5 && mb_strlen($commentaire) >= 5 && mb_strlen($commentaire) <= 500){
        $sql = "INSERT INTO avis_recette (recette_id, nom_user, note, commentaire, date_avis)
                VALUES (?, ?, ?, ?, NOW())";
        $stmtAvis = $pdo->prepare($sql);
        $stmtAvis->execute([$id, $nomUser, $note, $commentaire]);
        $messageAvisRecette = "Votre avis sur cette recette a été envoyé.";
    } else {
        $messageAvisRecette = "Veuillez saisir une note entre 1 et 5 et un commentaire entre 5 et 500 caractères.";
    }
}

/* ===== AVIS RECENTS SUR CETTE RECETTE ===== */
$avisRecette = [];
if($tableAvisRecetteExiste){
    $stmtAvisList = $pdo->prepare("
        SELECT *
        FROM avis_recette
        WHERE recette_id = ?
        ORDER BY id_avis DESC
        LIMIT 5
    ");
    $stmtAvisList->execute([$id]);
    $avisRecette = $stmtAvisList->fetchAll(PDO::FETCH_ASSOC);
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($recette['nom_recette']) ?> - SmartPlate</title>
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
        <a href="frontoffice.php" class="nav-item"><span class="icon">🏠</span>Dashboard</a>
        <a href="frontoffice.php#recettes" class="nav-item active"><span class="icon">🍽</span>Recettes</a>
        <a href="frontoffice.php#avis" class="nav-item"><span class="icon">💬</span>Avis</a>
        <a href="frontoffice.php#assistant" class="nav-item"><span class="icon">🤖</span>Assistant</a>
    </nav>

    <div class="sidebar-footer">
        <div class="user-badge">
            <img src="https://i.pravatar.cc/80?img=32" alt="user">
            <div class="user-info">
                <span class="user-name">Utilisateur</span>
                <span class="user-status">Voir mon profil</span>
            </div>
        </div>
    </div>
</aside>

<main class="dashboard">
    <div class="dashboard-header">
        <div>
            <h1>Détail de la recette</h1>
            <p>Consultez les informations nutritionnelles et les ingrédients.</p>
        </div>
        <span class="date-badge">
            <a href="frontoffice.php" style="text-decoration:none; color:inherit;">← Retour</a>
        </span>
    </div>

    <section class="recipe-detail-layout">
        <div class="card recipe-hero-card">
            <div class="recipe-hero-media">
                <img src="../../images/<?= htmlspecialchars($recette['image'] ?: 'default.jpg') ?>" alt="<?= htmlspecialchars($recette['nom_recette']) ?>">
            </div>

            <div class="recipe-hero-content">
                <div class="meal-tags">
                    <span class="badge green-light"><?= htmlspecialchars($recette['categorie']) ?></span>
                    <span class="time-info"><?= (int)$recette['temps_preparation'] ?> min</span>
                </div>

                <h2 class="recipe-detail-title"><?= htmlspecialchars($recette['nom_recette']) ?></h2>
                <p class="recipe-detail-desc"><?= htmlspecialchars($recette['description']) ?></p>

                <div class="recipe-detail-stats">
                    <div class="detail-stat-box">
                        <span class="detail-stat-label">Calories</span>
                        <span class="detail-stat-value"><?= round($recette['calories']) ?> kcal</span>
                    </div>

                    <div class="detail-stat-box">
                        <span class="detail-stat-label">Protéines</span>
                        <span class="detail-stat-value"><?= round($recette['proteines']) ?> g</span>
                    </div>

                    <div class="detail-stat-box">
                        <span class="detail-stat-label">Lipides</span>
                        <span class="detail-stat-value"><?= round($recette['lipides']) ?> g</span>
                    </div>

                    <div class="detail-stat-box">
                        <span class="detail-stat-label">Glucides</span>
                        <span class="detail-stat-value"><?= round($recette['glucides']) ?> g</span>
                    </div>
                </div>

                <div class="recipe-detail-actions">
                    <a href="frontoffice.php" class="btn-secondary">Retour</a>
                    <button type="button" class="btn-main">Ajouter aux favoris</button>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h2>Ingrédients</h2>
                <span class="badge blue-light"><?= count($ingredients) ?> ingrédient(s)</span>
            </div>

            <?php if(!empty($ingredients)){ ?>
                <div class="ingredients-list-front">
                    <?php foreach($ingredients as $ing){ ?>
                        <div class="ingredient-pill-front">
                            <span class="ingredient-name-front"><?= htmlspecialchars($ing['nom_ingredient']) ?></span>
                            <span class="ingredient-type-front"><?= htmlspecialchars($ing['type_ingredient']) ?></span>
                        </div>
                    <?php } ?>
                </div>
            <?php } else { ?>
                <p>Aucun ingrédient lié à cette recette.</p>
            <?php } ?>
        </div>

        <div class="front-review-layout single-column">
            <div class="card form-section">
                <div class="card-header">
                    <h2>Donner votre avis sur cette recette</h2>
                    <span class="badge yellow">Recette</span>
                </div>

                <?php if($messageAvisRecette !== ""){ ?>
                    <div class="front-alert"><?= htmlspecialchars($messageAvisRecette) ?></div>
                <?php } ?>

                <form method="POST" class="modern-form" id="avisRecetteForm">
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
                            maxlength="500"
                            placeholder="Partagez votre avis sur cette recette..."
                        ></textarea>
                        <div class="small-muted"><span id="countAvis">0</span>/500</div>
                    </div>

                    <div class="form-actions">
                        <div></div>
                        <div class="right-actions">
                            <button type="button" class="btn-secondary" id="resetAvis">Annuler</button>
                            <button type="submit" name="envoyer_avis_recette" class="btn-main">Envoyer avis</button>
                        </div>
                    </div>
                </form>
            </div>

            <div class="card">
                <div class="card-header">
                    <h2>Avis récents sur cette recette</h2>
                    <span class="badge blue-light">5 derniers</span>
                </div>

                <?php if($tableAvisRecetteExiste && !empty($avisRecette)){ ?>
                    <?php foreach($avisRecette as $avis){ ?>
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
                <?php } elseif(!$tableAvisRecetteExiste) { ?>
                    <p class="small-muted">La table <strong>avis_recette</strong> n’existe pas encore dans la base.</p>
                <?php } else { ?>
                    <p class="small-muted">Aucun avis pour cette recette pour le moment.</p>
                <?php } ?>
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
    const avisForm = document.getElementById("avisRecetteForm");

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
        });
    }
});
</script>

</body>
</html>