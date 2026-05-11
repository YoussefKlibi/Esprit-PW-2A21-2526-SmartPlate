<?php
// Ce script est destiné à être exécuté par une tâche planifiée (Cron Job ou Planificateur de tâches Windows)
// Il doit être exécuté toutes les minutes.

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/Models/Suivi_Nutritionnel/Objectif_Class.php';

echo "Exécution des rappels... " . date('Y-m-d H:i:s') . "\n";
Objectif::verifierEtEnvoyerRappels();
?>
