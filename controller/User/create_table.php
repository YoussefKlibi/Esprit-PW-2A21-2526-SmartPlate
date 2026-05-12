<?php
require_once __DIR__ . '/config.php';

try {
    $db = Config::getConnexion();
    $sql = "CREATE TABLE IF NOT EXISTS profils (
        id INT AUTO_INCREMENT PRIMARY KEY,
        titre VARCHAR(100) NOT NULL,
        description TEXT NOT NULL,
        id_utilisateur INT(11) NOT NULL,
        FOREIGN KEY (id_utilisateur) REFERENCES users(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
    
    $db->exec($sql);
    echo "SUCCESS";
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage();
}
?>
