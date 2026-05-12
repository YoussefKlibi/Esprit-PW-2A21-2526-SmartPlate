<?php
require_once __DIR__ . '/config.php';
try {
    $db = Config::getConnexion();
    $db->exec("INSERT INTO profils (titre, description, id_utilisateur) VALUES ('Objectif Prise de Masse', 'Profil avec focus excessif en protéines', 1)");
    $db->exec("INSERT INTO profils (titre, description, id_utilisateur) VALUES ('Profil Maintien', 'Sans déficit', 1)");
    $db->exec("INSERT INTO profils (titre, description, id_utilisateur) VALUES ('Végétarien', 'Aucune viande rouge ou blanche', 2)");
    echo "DONE";
} catch(Exception $e){
    echo $e->getMessage();
}
?>
