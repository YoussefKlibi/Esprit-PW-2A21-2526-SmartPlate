<?php
// send_reminders.php
// Script exécuté par le Planificateur de tâches Windows

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../Model/Objectif_Class.php';

// On appelle la logique métier qui est bien rangée dans la classe !
Objectif::verifierEtEnvoyerRappels();

?>