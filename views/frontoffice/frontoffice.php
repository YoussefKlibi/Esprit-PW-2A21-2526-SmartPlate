<?php
require_once "../../config.php";
?>

<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>Recettes Healthy</title>

<link rel="stylesheet" href="templates/stylefront.css">

</head>

<body>

<header class="navbar">

<h1>🍏 Healthy Recipes</h1>

<nav>
<a href="#">Accueil</a>
<a href="#">Recettes</a>
<a href="#">Contact</a>
</nav>

</header>


<section class="hero">

<h2>Découvrez des recettes délicieuses et saines</h2>

<p>Mangez sainement avec des recettes faciles à préparer</p>

</section>


<section class="recipes">

<?php
$sql = "SELECT * FROM recette";
$recettes = $pdo->query($sql);

foreach($recettes as $r){
?>

<div class="card">

<img src="../../images/<?php echo $r['image']; ?>" width="250">

<h3><?php echo $r['nom_recette']; ?></h3>

<p>Calories : <?php echo $r['calories']; ?></p>

<p>Temps : <?php echo $r['temps_preparation']; ?> min</p>

<p>Catégorie : <?php echo $r['categorie']; ?></p>

<button onclick="voirRecette('<?php echo $r['nom_recette']; ?>')">
Voir Recette
</button>

</div>

<?php } ?>

</section>


<footer>

<p>© 2026 Healthy Recipes</p>

</footer>


<script>

function voirRecette(nom){
    alert("Vous avez sélectionné la recette : " + nom);
}

</script>

</body>
</html>