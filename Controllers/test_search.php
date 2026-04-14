<?php
require_once __DIR__ . '/config.php';
try {
    $db = Config::getConnexion();
    $rech = '%';
    $req = $db->prepare('SELECT p.titre, p.description, u.prenom, u.nom, u.email FROM profils p INNER JOIN users u ON p.id_utilisateur = u.id WHERE u.nom LIKE :rech OR u.prenom LIKE :rech OR p.titre LIKE :rech');
    $req->execute(['rech' => $rech]);
    var_dump($req->fetchAll(PDO::FETCH_ASSOC));
} catch(Exception $e){
    echo $e->getMessage();
}
?>
