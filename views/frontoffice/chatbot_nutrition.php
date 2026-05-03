<?php
require_once "../../config.php";

header('Content-Type: application/json; charset=utf-8');

function reponseJson($success, $message){
    echo json_encode([
        'success' => $success,
        'message' => $message
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

function nettoyerTexte($txt){
    $txt = trim(mb_strtolower($txt ?? '', 'UTF-8'));
    return $txt;
}

function contient($texte, $mots){
    foreach($mots as $mot){
        if(mb_strpos($texte, $mot) !== false){
            return true;
        }
    }
    return false;
}

function formatRecette($r){
    return "Je te conseille : " . $r['nom_recette'] .
        " | " . $r['calories'] . " kcal | " .
        $r['proteines'] . " g protéines | " .
        $r['temps_preparation'] . " min.";
}

function getRecetteParCondition($pdo, $sql, $params = []){
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

$message = nettoyerTexte($_POST['message'] ?? '');

if($message === ''){
    reponseJson(false, "Écris une question pour que je puisse t’aider.");
}

/* ===================== RECETTE RAPIDE ===================== */
if(contient($message, ['rapide', 'vite', 'quick', 'facile'])){
    $r = getRecetteParCondition($pdo,
        "SELECT * FROM recette ORDER BY temps_preparation ASC, calories ASC LIMIT 1"
    );

    if($r){
        reponseJson(true, "Pour une recette rapide, " . formatRecette($r));
    }

    reponseJson(false, "Je n’ai pas trouvé de recette rapide.");
}

/* ===================== RECETTE HEALTHY ===================== */
if(contient($message, ['healthy', 'sain', 'saine', 'legere', 'léger', 'light'])){
    $r = getRecetteParCondition($pdo,
        "SELECT * FROM recette ORDER BY calories ASC, proteines DESC LIMIT 1"
    );

    if($r){
        reponseJson(true, "Pour une recette healthy, " . formatRecette($r));
    }

    reponseJson(false, "Je n’ai pas trouvé de recette healthy.");
}

/* ===================== RICHE EN PROTEINES ===================== */
if(contient($message, ['proteine', 'protéine', 'protein', 'muscle'])){
    $r = getRecetteParCondition($pdo,
        "SELECT * FROM recette ORDER BY proteines DESC, calories ASC LIMIT 1"
    );

    if($r){
        reponseJson(true, "Si tu veux une recette riche en protéines, " . formatRecette($r));
    }

    reponseJson(false, "Je n’ai pas trouvé de recette riche en protéines.");
}

/* ===================== PLANNING ===================== */
if(contient($message, ['planning', 'plan', 'semaine', 'hebdomadaire', 'liste de courses', 'courses'])){
    reponseJson(true, "Tu peux utiliser le module Planning pour générer automatiquement un menu hebdomadaire, une liste de courses et un budget estimé.");
}

/* ===================== EXPLIQUER UNE RECETTE ===================== */
if(contient($message, ['expliquer', 'explique', 'details', 'détails', 'detail', 'recette'])){
    $stmt = $pdo->query("SELECT * FROM recette ORDER BY id_recette DESC LIMIT 1");
    $r = $stmt->fetch(PDO::FETCH_ASSOC);

    if($r){
        $msg = "La recette " . $r['nom_recette'] .
            " contient environ " . $r['calories'] . " kcal, " .
            $r['proteines'] . " g de protéines, " .
            $r['lipides'] . " g de lipides et " .
            $r['glucides'] . " g de glucides. Temps de préparation : " .
            $r['temps_preparation'] . " minutes.";
        reponseJson(true, $msg);
    }

    reponseJson(false, "Je n’ai pas trouvé de recette à expliquer.");
}

/* ===================== PAR INGREDIENT ===================== */
$stmtIng = $pdo->query("SELECT id_ingredient, nom_ingredient FROM ingredient");
$ingredients = $stmtIng->fetchAll(PDO::FETCH_ASSOC);

foreach($ingredients as $ing){
    $nomIng = mb_strtolower($ing['nom_ingredient'], 'UTF-8');

    if(mb_strpos($message, $nomIng) !== false){
        $sql = "
            SELECT r.*
            FROM recette r
            INNER JOIN recette_ingredient ri ON r.id_recette = ri.recette_id
            WHERE ri.ingredient_id = ?
            ORDER BY r.calories ASC, r.temps_preparation ASC
            LIMIT 1
        ";
        $r = getRecetteParCondition($pdo, $sql, [$ing['id_ingredient']]);

        if($r){
            reponseJson(true, "Avec l’ingrédient " . $ing['nom_ingredient'] . ", " . formatRecette($r));
        } else {
            reponseJson(false, "Je n’ai trouvé aucune recette avec " . $ing['nom_ingredient'] . ".");
        }
    }
}

/* ===================== COUT ESTIME ===================== */
if(contient($message, ['cout', 'coût', 'prix', 'budget'])){
    $sql = "
        SELECT 
            r.nom_recette,
            COALESCE(SUM(COALESCE(i.prix_unitaire,0) * COALESCE(ri.quantite,1)),0) AS cout_estime
        FROM recette r
        LEFT JOIN recette_ingredient ri ON r.id_recette = ri.recette_id
        LEFT JOIN ingredient i ON i.id_ingredient = ri.ingredient_id
        GROUP BY r.id_recette
        ORDER BY cout_estime ASC
        LIMIT 1
    ";

    $stmt = $pdo->query($sql);
    $r = $stmt->fetch(PDO::FETCH_ASSOC);

    if($r){
        reponseJson(true, "La recette la moins chère estimée est " . $r['nom_recette'] . " avec un coût d’environ " . number_format($r['cout_estime'], 2) . " DT.");
    }

    reponseJson(false, "Je n’ai pas pu calculer le coût estimé.");
}

/* ===================== PAR DEFAUT ===================== */
reponseJson(true, "Je peux t’aider à trouver une recette rapide, healthy, riche en protéines, selon un ingrédient, expliquer une recette, estimer un coût ou t’orienter vers le planning.");