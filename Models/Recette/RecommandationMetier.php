<?php

class RecommandationMetier
{
    public static function recetteCompatible($recette, $profil)
    {
        if ($recette['temps_preparation'] > $profil['temps_max']) {
            return false;
        }

        if ($recette['calories'] > $profil['calories_max']) {
            return false;
        }

        return true;
    }

    public static function calculerScoreRecommandation($recette, $profil)
    {
        $score = 0;

        // Base : recette compatible
        $score += 20;

        // Objectif nutritionnel
        if ($profil['objectif'] === 'perte_poids') {
            if ($recette['calories'] <= 450) {
                $score += 25;
            }
            if ($recette['proteines'] >= 20) {
                $score += 20;
            }
            if ($recette['lipides'] <= 15) {
                $score += 10;
            }
        }

        if ($profil['objectif'] === 'maintien') {
            if ($recette['calories'] >= 400 && $recette['calories'] <= 650) {
                $score += 20;
            }
            if ($recette['proteines'] >= 15) {
                $score += 15;
            }
            if ($recette['glucides'] <= 70) {
                $score += 10;
            }
        }

        if ($profil['objectif'] === 'prise_masse') {
            if ($recette['calories'] >= 600) {
                $score += 25;
            }
            if ($recette['proteines'] >= 25) {
                $score += 20;
            }
            if ($recette['glucides'] >= 50) {
                $score += 10;
            }
        }

        // Bonus temps rapide
        if ($recette['temps_preparation'] <= 15) {
            $score += 10;
        } elseif ($recette['temps_preparation'] <= 25) {
            $score += 5;
        }

        return $score;
    }

    public static function getExplication($recette, $profil)
    {
        $explications = [];

        if ($profil['objectif'] === 'perte_poids' && $recette['calories'] <= 450) {
            $explications[] = "adaptée à la perte de poids";
        }

        if ($profil['objectif'] === 'prise_masse' && $recette['calories'] >= 600) {
            $explications[] = "adaptée à la prise de masse";
        }

        if ($recette['proteines'] >= 20) {
            $explications[] = "riche en protéines";
        }

        if ($recette['temps_preparation'] <= 20) {
            $explications[] = "rapide à préparer";
        }

        if (empty($explications)) {
            return "recette compatible avec votre profil";
        }

        return implode(", ", $explications);
    }

    public static function recommanderRecettes($recettes, $profil)
    {
        $resultats = [];

        foreach ($recettes as $recette) {
            if (self::recetteCompatible($recette, $profil)) {
                $recette['score_recommandation'] = self::calculerScoreRecommandation($recette, $profil);
                $recette['explication_recommandation'] = self::getExplication($recette, $profil);
                $resultats[] = $recette;
            }
        }

        usort($resultats, function($a, $b) {
            return $b['score_recommandation'] <=> $a['score_recommandation'];
        });

        return $resultats;
    }
}