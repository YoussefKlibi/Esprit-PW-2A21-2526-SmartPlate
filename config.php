<?php
class Config {
    private static $pdo = null;
    
    public static function getConnexion() {
        if (self::$pdo === null) {
            // Essayez ces configurations une par une :
            
            // Configuration 1 : avec 127.0.0.1
            $host = '127.0.0.1';
            $port = '3306';
            
            // Configuration 2 (si 1 ne marche pas) : avec localhost
            // $host = 'localhost';
            // $port = '3307';
            
            $dbname = 'smartplate';
            $username = 'root';
            $password = '';
            
            try {
                self::$pdo = new PDO(
                    "mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4",
                    $username,
                    $password
                );
                self::$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            } catch (PDOException $e) {
                die("Erreur de connexion : " . $e->getMessage());
            }
        }
        return self::$pdo;
    }
}

// Compat: some legacy views expect a $pdo variable.
$pdo = Config::getConnexion();
?>