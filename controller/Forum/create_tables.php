<?php
/**
 * Crée les tables articles et comments dans la base smartplate.
 * À exécuter une seule fois depuis le navigateur.
 */
require_once __DIR__ . '/../../config.php';

$pdo = Config::getConnexion();

$pdo->exec("CREATE TABLE IF NOT EXISTS `articles` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(255) NOT NULL,
    `type` VARCHAR(100) NOT NULL,
    `image_url` VARCHAR(500) DEFAULT '',
    `content` TEXT NOT NULL,
    `author` VARCHAR(100) DEFAULT 'Admin',
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `status` TINYINT(1) DEFAULT 1,
    `rating_sum` INT DEFAULT 0,
    `rating_count` INT DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

$pdo->exec("CREATE TABLE IF NOT EXISTS `comments` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `article_id` INT NOT NULL,
    `parent_id` INT DEFAULT NULL,
    `username` VARCHAR(100) NOT NULL,
    `comment` TEXT NOT NULL,
    `emoji` VARCHAR(10) DEFAULT NULL,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `status` TINYINT(1) DEFAULT 1,
    `agree_count` INT DEFAULT 0,
    `disagree_count` INT DEFAULT 0,
    `nuanced_count` INT DEFAULT 0,
    `toxic_flag` TINYINT(1) DEFAULT 0,
    `toxic_delete_at` DATETIME DEFAULT NULL,
    `badge` VARCHAR(100) DEFAULT NULL,
    `badge_assigned_at` DATETIME DEFAULT NULL,
    `report_count` INT DEFAULT 0,
    `stance` VARCHAR(20) DEFAULT NULL,
    `reclass_pour` INT DEFAULT 0,
    `reclass_contre` INT DEFAULT 0,
    `reclass_neutre` INT DEFAULT 0,
    INDEX `idx_article_id` (`article_id`),
    INDEX `idx_parent_id` (`parent_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

echo '<h2 style=\"font-family:sans-serif;color:green;\">✅ Tables articles et comments créées avec succès dans smartplate !</h2>';
echo '<p style=\"font-family:sans-serif;\">Vous pouvez maintenant utiliser le module Forum.</p>';
echo '<p style=\"font-family:sans-serif;\"><a href=\"../../view/Forum/frontoffice/forum.php\">→ Ouvrir le Forum</a> | <a href=\"../../view/Forum/backoffice/admin_forum.php\">→ Back-office Forum</a></p>';