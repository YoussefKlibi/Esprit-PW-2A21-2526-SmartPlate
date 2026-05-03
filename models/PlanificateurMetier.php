<?php
require_once __DIR__ . "/RecommandationMetier.php";

class PlanificateurMetier
{
    public static function genererPlanning($pdo, $profil, $nbJours = 7)
    {
        $stmt = $pdo->query("SELECT * FROM recette ORDER BY id_recette DESC");
        $recettes = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $recettesRecommandees = RecommandationMetier::recommanderRecettes($recettes, $profil);

        if (empty($recettesRecommandees)) {
            return [
                'planning' => [],
                'courses' => [],
                'budget_total' => 0
            ];
        }

        $jours = ['Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi', 'Samedi', 'Dimanche'];
        $planning = [];
        $indexRecette = 0;

        for ($i = 0; $i < $nbJours; $i++) {
            if (!isset($recettesRecommandees[$indexRecette])) {
                $indexRecette = 0;
            }

            $planning[] = [
                'jour' => $jours[$i],
                'recette' => $recettesRecommandees[$indexRecette]
            ];

            $indexRecette++;
        }

        $courses = self::genererListeCourses($pdo, $planning);
        $budgetTotal = self::calculerBudgetTotal($courses);

        return [
            'planning' => $planning,
            'courses' => $courses,
            'budget_total' => $budgetTotal
        ];
    }

    public static function genererListeCourses($pdo, $planning)
    {
        $liste = [];

        foreach ($planning as $item) {
            $recetteId = $item['recette']['id_recette'];

            $sql = "
                SELECT 
                    i.id_ingredient,
                    i.nom_ingredient,
                    i.prix_unitaire,
                    i.unite,
                    ri.quantite
                FROM recette_ingredient ri
                INNER JOIN ingredient i ON i.id_ingredient = ri.ingredient_id
                WHERE ri.recette_id = ?
            ";

            $stmt = $pdo->prepare($sql);
            $stmt->execute([$recetteId]);
            $ingredients = $stmt->fetchAll(PDO::FETCH_ASSOC);

            foreach ($ingredients as $ing) {
                $id = $ing['id_ingredient'];

                if (!isset($liste[$id])) {
                    $liste[$id] = [
                        'nom_ingredient' => $ing['nom_ingredient'],
                        'quantite_totale' => 0,
                        'unite' => $ing['unite'],
                        'prix_unitaire' => (float)$ing['prix_unitaire'],
                        'cout_total' => 0
                    ];
                }

                $liste[$id]['quantite_totale'] += (float)$ing['quantite'];
            }
        }

        foreach ($liste as $id => $ing) {
            $liste[$id]['cout_total'] = $ing['quantite_totale'] * $ing['prix_unitaire'];
        }

        return $liste;
    }

    public static function calculerBudgetTotal($courses)
    {
        $total = 0;

        foreach ($courses as $ing) {
            $total += (float)$ing['cout_total'];
        }

        return round($total, 2);
    }

    public static function sauvegarderPlanning($pdo, $profil, $nbJours, $resultat)
    {
        $nomPlanning = "Planning " . date('d/m/Y H:i');

        $sql = "INSERT INTO planning_hebdo (nom_planning, objectif, nb_jours, temps_max, calories_max, budget_total)
                VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            $nomPlanning,
            $profil['objectif'],
            $nbJours,
            $profil['temps_max'],
            $profil['calories_max'],
            $resultat['budget_total']
        ]);

        $planningId = $pdo->lastInsertId();

        foreach ($resultat['planning'] as $item) {
            $stmt2 = $pdo->prepare("
                INSERT INTO planning_hebdo_recette (planning_id, jour_semaine, recette_id)
                VALUES (?, ?, ?)
            ");
            $stmt2->execute([
                $planningId,
                $item['jour'],
                $item['recette']['id_recette']
            ]);
        }

        return $planningId;
    }
}