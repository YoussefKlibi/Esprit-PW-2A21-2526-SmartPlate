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
            return false;
        }

        $sql_comments = "CREATE TABLE IF NOT EXISTS `comments` (
            `id` INT NOT NULL AUTO_INCREMENT,
            `article_id` INT NOT NULL,
            `username` VARCHAR(100) NOT NULL,
            `comment` TEXT NOT NULL,
            `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            `status` TINYINT(1) NOT NULL DEFAULT 0,
            `agree_count` INT DEFAULT 0,
            `disagree_count` INT DEFAULT 0,
            `nuanced_count` INT DEFAULT 0,
            `emoji` VARCHAR(255) DEFAULT NULL,
            `parent_id` INT DEFAULT NULL,
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
            `rating_sum` INT DEFAULT 0,
            `rating_count` INT DEFAULT 0,
            PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";

        try {
            $pdo->beginTransaction();
            $pdo->exec($sql_comments);
            $pdo->exec($sql_articles);
            
            // Check and add parent_id if table exists but column is missing
            $this->checkAndAddColumn($pdo, 'comments', 'parent_id', 'INT DEFAULT NULL');
            
            // Clean up existing orphaned replies
            $pdo->exec("DELETE FROM comments WHERE parent_id IS NOT NULL AND parent_id NOT IN (SELECT id FROM (SELECT id FROM comments) as tmp)");
            
            $pdo->commit();
            return true;
        } catch (PDOException $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            return false;
        }
    }

    private function checkAndAddColumn($pdo, $table, $column, $definition)
    {
        try {
            $query = $pdo->query("SHOW COLUMNS FROM `$table` LIKE '$column'");
            if ($query->rowCount() == 0) {
                $pdo->exec("ALTER TABLE `$table` ADD COLUMN `$column` $definition");
            }
        } catch (PDOException $e) {
            // Table might not exist yet, handled by CREATE TABLE IF NOT EXISTS
        }
    }
}
?>
