<?php
require_once __DIR__ . '/../../Controllers/ProfilController.php';
require_once __DIR__ . '/../../Controllers/UserController.php';

$profilC = new ProfilController();
$resultatsJointure = [];
$rechercheActive = false;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $recherche = trim($_POST['recherche'] ?? '');
    
    if (!empty($recherche)) {
        $db = Config::getConnexion();
        // Correction pro : Utilisation de CONCAT pour permettre la recherche du "prénom ET nom" en même temps
        $req = $db->prepare('
            SELECT p.titre, p.description, u.prenom, u.nom, u.email 
            FROM profils p 
            INNER JOIN users u ON p.id_utilisateur = u.id 
            WHERE CONCAT(u.prenom, " ", u.nom) LIKE :rech 
               OR CONCAT(u.nom, " ", u.prenom) LIKE :rech 
               OR u.email LIKE :rech
               OR p.titre LIKE :rech
        ');
        $req->execute(['rech' => '%' . $recherche . '%']);
        $resultatsJointure = $req->fetchAll(PDO::FETCH_ASSOC);
        $rechercheActive = true;
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SmartPlate - Recherche de Profil</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../css/style.css">
    <style>
        /* Styles spécifiques au design pro de la recherche */
        .search-container {
            background: #ffffff;
            border-radius: 16px;
            padding: 40px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.05);
            margin-bottom: 40px;
        }
        .search-title {
            color: #0f172a;
            font-size: 28px;
            font-weight: 700;
            margin-bottom: 10px;
        }
        .search-subtitle {
            color: #64748b;
            font-size: 15px;
            margin-bottom: 30px;
            line-height: 1.6;
        }
        .search-area {
            display: flex;
            gap: 15px;
            align-items: stretch;
        }
        .search-input {
            flex-grow: 1;
            padding: 16px 20px;
            font-size: 16px;
            border: 2px solid #e2e8f0;
            border-radius: 12px;
            transition: all 0.3s;
        }
        .search-input:focus {
            border-color: var(--primary-color);
            outline: none;
            box-shadow: 0 0 0 4px rgba(79, 70, 229, 0.1);
        }
        .search-btn {
            background: var(--primary-color);
            color: white;
            padding: 0 30px;
            border: none;
            border-radius: 12px;
            font-weight: 600;
            font-size: 16px;
            cursor: pointer;
            transition: transform 0.2s, box-shadow 0.2s;
        }
        .search-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 15px rgba(79, 70, 229, 0.3);
        }
        
        /* Affichage des résultats en Carte */
        .results-wrapper {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
            gap: 25px;
            margin-top: 30px;
        }
        .profile-card {
            background: white;
            border-radius: 16px;
            padding: 25px;
            border: 1px solid #e2e8f0;
            box-shadow: 0 4px 6px rgba(0,0,0,0.02);
            transition: transform 0.3s, box-shadow 0.3s;
        }
        .profile-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 12px 20px rgba(0,0,0,0.08);
            border-color: #cbd5e1;
        }
        .profile-tag {
            display: inline-block;
            background: #eef2ff;
            color: #4f46e5;
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 13px;
            font-weight: 600;
            margin-bottom: 15px;
        }
        .profile-card h3 {
            color: #1e293b;
            font-size: 20px;
            margin: 0 0 10px 0;
            font-weight: 600;
        }
        .profile-card p.desc {
            color: #475569;
            font-size: 14px;
            line-height: 1.6;
            margin-bottom: 20px;
            padding-bottom: 20px;
            border-bottom: 1px dashed #e2e8f0;
        }
        .user-info {
            display: flex;
            align-items: center;
            gap: 12px;
        }
        .user-avatar {
            width: 40px;
            height: 40px;
            background: #f1f5f9;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #64748b;
            font-weight: 600;
        }
        .user-details h4 {
            margin: 0;
            color: #0f172a;
            font-size: 15px;
        }
        .user-details span {
            color: #94a3b8;
            font-size: 13px;
        }
    </style>
</head>
<body style="background: #f8fafc; font-family: 'Inter', sans-serif; padding: 3rem 1rem;">
    <div style="max-width: 1100px; margin: 0 auto;">
        
        <!-- Header Actions -->
        <div style="display: flex; justify-content: space-between; margin-bottom: 25px;">
            <a href="profil_list.php" style="color: #64748b; text-decoration: none; font-weight: 500; display: flex; align-items: center; gap: 8px;">
                <span>←</span> Retour à la gestion
            </a>
            <a href="profil_create.php" style="color: var(--primary-color); font-weight: 600; text-decoration: none;">
                + Créer un nouveau profil
            </a>
        </div>

        <!-- Search Bar Area -->
        <div class="search-container">
            <h1 class="search-title">Recherche croisée intelligente</h1>
            <p class="search-subtitle">
                Effectuez une recherche précise. 
                Tapez le <strong>prénom et nom</strong> complet d'un utilisateur, son email, ou bien le <strong>titre d'un profil</strong> sportif/nutritionnel.
            </p>

            <form id="searchForm" method="POST" action="">
                <div class="search-area">
                    <input type="text" id="rechercheInput" name="recherche" class="search-input" 
                           placeholder="Exemple : Ilyes Gaied, Prise de masse, etc." 
                           value="<?= htmlspecialchars($_POST['recherche'] ?? '') ?>">
                    <button type="submit" class="search-btn">Rechercher</button>
                </div>
                <span id="errorSearch" style="color: #ef4444; font-size: 14px; display: block; margin-top: 10px; font-weight: 500;"></span>
            </form>
        </div>

        <!-- Results Area -->
        <?php if ($rechercheActive): ?>
            <div>
                <h2 style="color: #0f172a; font-size: 22px; margin-bottom: 5px;">
                    Résultats pour "<?= htmlspecialchars($_POST['recherche'] ?? '') ?>"
                </h2>
                <p style="color: #64748b; font-size: 15px; margin-bottom: 0;">
                    <?= count($resultatsJointure) ?> profil(s) trouvé(s) via la requête de jointure.
                </p>
                
                <?php if (count($resultatsJointure) > 0): ?>
                    <div class="results-wrapper">
                        <?php foreach ($resultatsJointure as $row): ?>
                            <div class="profile-card">
                                <span class="profile-tag">Profil Nutrition / Sport</span>
                                <h3><?= htmlspecialchars($row['titre']) ?></h3>
                                <p class="desc"><?= htmlspecialchars($row['description']) ?></p>
                                
                                <div class="user-info">
                                    <div class="user-avatar">
                                        <?= strtoupper(substr($row['prenom'], 0, 1) . substr($row['nom'], 0, 1)) ?>
                                    </div>
                                    <div class="user-details">
                                        <h4><?= htmlspecialchars($row['prenom'] . ' ' . $row['nom']) ?></h4>
                                        <span><?= htmlspecialchars($row['email']) ?></span>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div style="background: white; border-radius: 16px; padding: 40px; text-align: center; margin-top: 30px; border: 1px dashed #cbd5e1;">
                        <div style="font-size: 40px; margin-bottom: 15px;">🔍</div>
                        <h4 style="color: #1e293b; font-size: 18px; margin-bottom: 10px;">Aucun profil trouvé</h4>
                        <p style="color: #64748b; max-width: 500px; margin: 0 auto; line-height: 1.6;">
                            Soit le profil n'existe pas, soit vous recherchez un utilisateur qui ne possède pas encore de profil. 
                            <strong>La jointure stricte ignore les utilisateurs sans profils rattachés.</strong>
                        </p>
                    </div>
                <?php endif; ?>
            </div>
        <?php endif; ?>

    </div>
    
    <script>
    document.addEventListener('DOMContentLoaded', function () {
        const searchForm = document.getElementById('searchForm');
        if (searchForm) {
            searchForm.addEventListener('submit', function (e) {
                const textEl = document.getElementById('rechercheInput');
                const errorEl = document.getElementById('errorSearch');
                errorEl.textContent = '';
                
                if (textEl.value.trim() === '') {
                    errorEl.textContent = '❌ Veuillez fournir un texte pour lancer la recherche.';
                    e.preventDefault();
                }
            });
        }
    });
    </script>
</body>
</html>
