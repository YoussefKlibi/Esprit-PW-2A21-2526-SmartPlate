<?php
$pageTitle = 'SmartPlate - Accueil';
$currentPage = 'index';
include __DIR__ . '/header.php';
?>

<main>
    <section class="section" style="text-align: center; padding-top: 100px; padding-bottom: 100px;">
        <h1>Bienvenue sur SmartPlate</h1>
        <p class="form-intro" style="font-size: 1.2rem; margin-top: 20px;">Veuillez choisir votre espace de navigation :</p>
        
        <div style="display: flex; justify-content: center; gap: 30px; margin-top: 50px;">
            <a href="login.php?role=client" class="btn btn-primary" style="padding: 20px 50px; font-size: 1.3rem;">Espace Client</a>
            <a href="login.php?role=admin" class="btn btn-light" style="padding: 20px 50px; font-size: 1.3rem; border: 2px solid #333;">Espace Admin</a>
        </div>
    </section>
</main>

<?php include __DIR__ . '/footer.php'; ?>
