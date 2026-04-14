<?php
$pageTitle = 'SmartPlate - Front Office';
$currentPage = 'index';
include __DIR__ . '/header.php';
?>

    <main>
        <section class="hero" id="home">
            <div class="hero-content">
                <p class="eyebrow">Plateforme nutrition pour les clients modernes</p>
                <h1>Un écran d'accueil front office clair pour démarrer de bonnes habitudes plus vite.</h1>
                <p class="hero-copy">
                    Suivez vos objectifs, découvrez des plans repas et gardez une routine quotidienne simple depuis une interface unique.
                    Tout est conçu pour être lisible en un coup d'oeil.
                </p>
                <div class="hero-actions">
                    <a href="#plans" class="btn btn-primary">Commencer</a>
                    <a href="#services" class="btn btn-light">Découvrir les services</a>
                </div>
                <ul class="hero-highlights">
                    <li>Accompagnement repas personnalisé</li>
                    <li>Vue hebdomadaire de votre progression</li>
                    <li>Expérience client simple et fluide</li>
                </ul>
            </div>

            <aside class="hero-panel" aria-label="Aperçu du jour">
                <h2>Aujourd'hui en un coup d'oeil</h2>
                <div class="stat-card">
                    <p>Hydratation</p>
                    <strong>6 / 8 verres</strong>
                    <span class="progress"><span style="width: 75%;"></span></span>
                </div>
                <div class="stat-card">
                    <p>Objectif activité</p>
                    <strong>7 200 pas</strong>
                    <span class="progress"><span style="width: 68%;"></span></span>
                </div>
                <div class="stat-card">
                    <p>Score d'équilibre alimentaire</p>
                    <strong>82 / 100</strong>
                    <span class="progress"><span style="width: 82%;"></span></span>
                </div>
            </aside>
        </section>

        <section class="section" id="services">
            <div class="section-head">
                <p class="section-tag">Services essentiels</p>
                <h2>Tout ce dont un client a besoin dans une seule vue front office</h2>
            </div>
            <div class="feature-grid">
                <article class="feature-card">
                    <h3>Créateur de repas intelligent</h3>
                    <p>Créez des repas équilibrés avec des suggestions de calories et de macros selon votre profil.</p>
                </article>
                <article class="feature-card">
                    <h3>Suivi de progression</h3>
                    <p>Suivez vos statistiques quotidiennes et hebdomadaires avec des résumés clairs.</p>
                </article>
                <article class="feature-card">
                    <h3>Recommandations du coach</h3>
                    <p>Recevez des conseils pratiques et des rappels actionnables de votre coach.</p>
                </article>
            </div>
        </section>

        <section class="section" id="plans">
            <div class="section-head">
                <p class="section-tag">Offres</p>
                <h2>Offres front office les plus choisies par les clients</h2>
            </div>
            <div class="plan-grid">
                <article class="plan-card">
                    <h3>Essentiel</h3>
                    <p>Pour les clients qui veulent un démarrage simple avec un suivi de base.</p>
                    <strong>19 $ / mois</strong>
                </article>
                <article class="plan-card featured">
                    <p class="plan-badge">Le plus choisi</p>
                    <h3>Équilibre</h3>
                    <p>Idéal pour un suivi régulier, des plans nutrition et des objectifs hebdomadaires.</p>
                    <strong>39 $ / mois</strong>
                </article>
                <article class="plan-card">
                    <h3>Performance</h3>
                    <p>Accompagnement avancé avec des suivis fréquents et des objectifs personnalisés.</p>
                    <strong>59 $ / mois</strong>
                </article>
            </div>
        </section>
    </main>

<?php include __DIR__ . '/footer.php'; ?>
