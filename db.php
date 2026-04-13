<?php
class Database
{
    private $host = 'localhost';
    private $db_name = 'smart_plate';
    private $username = 'root';
    private $password = '';
    private $charset = 'utf8mb4';
    public $conn = null;

    public function __construct($host = null, $db_name = null, $username = null, $password = null)
    {
        if ($host !== null) $this->host = $host;
        if ($db_name !== null) $this->db_name = $db_name;
        if ($username !== null) $this->username = $username;
        if ($password !== null) $this->password = $password;
    }

    public function getConnection()
    {
        if ($this->conn) {
            return $this->conn;
        }

        $dsn = "mysql:host={$this->host};dbname={$this->db_name};charset={$this->charset}";
        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ];

        try {
            $this->conn = new PDO($dsn, $this->username, $this->password, $options);
        } catch (PDOException $e) {
            echo "Erreur de connexion : " . $e->getMessage();
            $this->conn = null;
        }

        return $this->conn;
    }

    /**
     * Create required tables if they do not exist.
     * Returns true on success, false on failure.
     */
    public function createTables()
    {
        $pdo = $this->getConnection();
        if (!$pdo) {
            echo "Aucune connexion disponible pour créer les tables.";
            return false;
        }

        $sql_comments = "CREATE TABLE IF NOT EXISTS `comments` (
            `id` INT NOT NULL AUTO_INCREMENT,
            `article_id` INT NOT NULL,
            `username` VARCHAR(100) NOT NULL,
            `comment` TEXT NOT NULL,
            `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            `status` TINYINT(1) NOT NULL DEFAULT 0,
            PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";

        $sql_articles = "CREATE TABLE IF NOT EXISTS `articles` (
            `id` INT NOT NULL AUTO_INCREMENT,
            `name` VARCHAR(255) NOT NULL,
            `type` VARCHAR(100) DEFAULT NULL,
            `image_url` VARCHAR(255) DEFAULT NULL,
            `content` TEXT NOT NULL,
            `author` VARCHAR(100) DEFAULT NULL,
            `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            `status` TINYINT(1) NOT NULL DEFAULT 1,
            PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";

        try {
            $pdo->beginTransaction();
            $pdo->exec($sql_comments);
            $pdo->exec($sql_articles);
            $pdo->commit();
            return true;
        } catch (PDOException $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            echo "Erreur création tables : " . $e->getMessage();
            return false;
        }
    }
}

?>