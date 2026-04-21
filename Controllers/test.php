<?php
require_once __DIR__ . '/config.php';

try {
    $db = Config::getConnexion();
    $req = $db->query("SELECT * FROM users");
    $users = $req->fetchAll(PDO::FETCH_ASSOC);
    print_r($users);
    
    echo "--- PROIFS --- \n";
    $req = $db->query("SELECT * FROM profils");
    $profils = $req->fetchAll(PDO::FETCH_ASSOC);
    print_r($profils);
} catch(Exception $e){
    echo $e->getMessage();
}
?>
