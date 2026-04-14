<?php
class Database {
    private $host = "localhost";
    private $db_name = "reclamation_db";
    private $username = "root";
    private $password = "";

    public function connect() {
        try {
            return new PDO(
                "mysql:host=$this->host;dbname=$this->db_name",
                $this->username,
                $this->password
            );
        } catch (Exception $e) {
            die("Erreur connexion: " . $e->getMessage());
        }
    }
}