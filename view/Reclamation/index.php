<?php
require_once __DIR__ . '/../config/auth.php';

$currentUser = getCurrentUser();
if ($currentUser !== null) {
    $isAdmin = (($currentUser['role'] ?? '') === 'admin') || (($_SESSION['is_admin'] ?? false) === true);
    if ($isAdmin) {
        header('Location: back/admin_dashboard.php');
    } else {
        header('Location: ../User/frontend/dashboard.php');
    }
    exit;
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SmartPlate - Accueil</title>
    <meta name="description" content="SmartPlate vous aide a mieux manger, suivre vos objectifs nutritionnels et gerer vos reclamations simplement.">
    <link rel="stylesheet" href="../User/css/TemplateFrontFinale.css">
    <style>
        .container {
            width: min(1180px, 94%);
            margin: 0 auto;
        }

        .topbar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 24px 0;
        }

        .brand {
            font-size: 1.5rem;
            font-weight: 700;
            letter-spacing: 0.2px;
        }

        .topbar-actions {
            display: flex;
            gap: 12px;
        }

        .btn {
            display: inline-block;
            padding: 11px 20px;
            border-radius: 10px;
            text-decoration: none;
            font-weight: 600;
            border: 1px solid transparent;
            transition: 0.2s ease;
        }

        .btn-login {
            background: transparent;
            color: var(--text);
            border-color: #becfc8;
        }

        .btn-login:hover {
            background: #edf4f1;
        }

        .btn-signup {
            background: #1fae84;
            color: #ffffff;
        }

        .btn-signup:hover {
            background: #138166;
        }

        .hero {
            display: grid;
            grid-template-columns: 1.1fr 1fr;
            gap: 28px;
            margin-top: 24px;
            align-items: stretch;
        }

        .hero-card,
        .features-card {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: 20px;
            padding: 32px;
            box-shadow: 0 12px 26px rgba(22, 45, 37, 0.06);
        }

        .food-showcase {
            margin-top: 24px;
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 18px;
        }

        .food-card {
            background: #fff;
            border: 1px solid #dce8e3;
            border-radius: 18px;
            overflow: hidden;
            box-shadow: 0 8px 20px rgba(22, 45, 37, 0.08);
        }

        .food-card img {
            width: 100%;
            height: 190px;
            object-fit: cover;
            display: block;
        }

        .food-card .content {
            padding: 14px;
        }

        .food-card h3 {
            font-size: 1.04rem;
            margin: 0 0 6px;
        }

        .food-card p {
            margin: 0;
            color: #5b6b66;
            line-height: 1.5;
            font-size: 0.93rem;
        }

        .stats {
            margin-top: 24px;
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 14px;
        }

        .stat {
            background: #ffffff;
            border: 1px solid #dce8e3;
            border-radius: 14px;
            padding: 16px;
            text-align: center;
        }

        .stat strong {
            display: block;
            font-size: 1.4rem;
            margin-bottom: 4px;
        }

        .stat span {
            color: #61726d;
            font-size: 0.9rem;
        }

        h1 {
            margin: 0 0 14px;
            font-size: 2.2rem;
            line-height: 1.2;
        }

        .lead {
            margin: 0 0 22px;
            color: var(--muted);
            line-height: 1.65;
            font-size: 1.02rem;
        }

        .hero-cta {
            display: flex;
            gap: 12px;
            flex-wrap: wrap;
            margin-top: 10px;
        }

        .features-title {
            margin-top: 0;
            margin-bottom: 12px;
            font-size: 1.2rem;
        }

        .feature-list {
            list-style: none;
            margin: 0;
            padding: 0;
            display: grid;
            gap: 10px;
        }

        .feature-list li {
            border: 1px solid var(--border);
            border-radius: 12px;
            padding: 12px 14px;
            background: #f9fcfa;
            color: #31433d;
        }

        .about {
            margin-top: 26px;
            background: #0f3f34;
            color: #e8fffa;
            border-radius: 16px;
            padding: 22px 26px;
            line-height: 1.7;
        }

        .about strong {
            color: #ffffff;
        }

        @media (max-width: 920px) {
            .hero {
                grid-template-columns: 1fr;
            }

            h1 {
                font-size: 1.9rem;
            }

            .food-showcase {
                grid-template-columns: 1fr;
            }

            .stats {
                grid-template-columns: 1fr 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <header class="topbar">
            <div class="brand">SmartPlate</div>
            <div class="topbar-actions">
                <a class="btn btn-login" href="../User/frontend/login.php">Se connecter</a>
                <a class="btn btn-signup" href="../User/frontend/register.php">S'inscrire</a>
            </div>
        </header>

        <main>
            <section class="hero">
                <article class="hero-card">
                    <h1>Mangez mieux avec SmartPlate</h1>
                    <p class="lead">
                        SmartPlate est une plateforme qui vous accompagne dans votre quotidien nutritionnel:
                        suivi des repas, progression de vos objectifs, decouverte de recettes
                        et communication simple avec l'equipe via les reclamations.
                    </p>
                    <div class="hero-cta">
                        <a class="btn btn-signup" href="../User/frontend/register.php">Creer un compte</a>
                        <a class="btn btn-login" href="../User/frontend/login.php">J'ai deja un compte</a>
                    </div>
                </article>

                <aside class="features-card">
                    <h2 class="features-title">Fonctionnalites SmartPlate</h2>
                    <ul class="feature-list">
                        <li>Journal alimentaire quotidien avec suivi des repas</li>
                        <li>Objectifs nutritionnels personnalises par profil</li>
                        <li>Suivi de progression avec evolution sur plusieurs semaines</li>
                        <li>Suggestions de recettes et idees d'assiettes equilibrees</li>
                        <li>Reclamations et retours clients avec suivi des reponses admin</li>
                        <li>Espace profile et gestion simple des informations du compte</li>
                    </ul>
                </aside>
            </section>

            <section class="food-showcase" aria-label="Exemples de repas SmartPlate">
                <article class="food-card">
                    <img src="https://images.unsplash.com/photo-1498837167922-ddd27525d352?auto=format&fit=crop&w=900&q=80" alt="Bol sain avec legumes frais">
                    <div class="content">
                        <h3>Repas equilibres</h3>
                        <p>Construisez des assiettes riches en legumes, proteines et bonnes graisses.</p>
                    </div>
                </article>
                <article class="food-card">
                    <img src="https://images.unsplash.com/photo-1512621776951-a57141f2eefd?auto=format&fit=crop&w=900&q=80" alt="Salade coloree">
                    <div class="content">
                        <h3>Recettes variees</h3>
                        <p>Decouvrez des recettes simples pour manger mieux sans perdre de temps.</p>
                    </div>
                </article>
                <article class="food-card">
                    <img src="https://images.unsplash.com/photo-1482049016688-2d3e1b311543?auto=format&fit=crop&w=900&q=80" alt="Petit-dejeuner sain">
                    <div class="content">
                        <h3>Routine nutritionnelle</h3>
                        <p>Suivez vos habitudes quotidiennes pour adopter une alimentation durable.</p>
                    </div>
                </article>
            </section>

            <section class="stats">
                <div class="stat">
                    <strong>24/7</strong>
                    <span>Acces a votre espace personnel</span>
                </div>
                <div class="stat">
                    <strong>+6</strong>
                    <span>Modules nutrition et bien-etre</span>
                </div>
                <div class="stat">
                    <strong>100%</strong>
                    <span>Suivi digital de vos reclamations</span>
                </div>
                <div class="stat">
                    <strong>Simple</strong>
                    <span>Interface claire pour debutants</span>
                </div>
            </section>

            <section class="about">
                <strong>Pourquoi SmartPlate ?</strong><br>
                SmartPlate combine education nutritionnelle, suivi personnel et assistance.
                Inscrivez-vous pour commencer votre parcours. Si vous etes deja connecte,
                votre page d'accueil devient automatiquement votre tableau de bord.
            </section>
        </main>
    </div>
</body>
</html>
