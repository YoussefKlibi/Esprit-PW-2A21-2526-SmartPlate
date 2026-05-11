<?php
require_once __DIR__ . '/config.php';

try {
    $db = Config::getConnexion();
    
    // Add google_id
    try {
        $db->exec("ALTER TABLE users ADD COLUMN google_id VARCHAR(255) NULL");
        echo "google_id added.\n";
    } catch (Exception $e) { echo "google_id error: " . $e->getMessage() . "\n"; }

    // Add reset_token
    try {
        $db->exec("ALTER TABLE users ADD COLUMN reset_token VARCHAR(255) NULL");
        echo "reset_token added.\n";
    } catch (Exception $e) { echo "reset_token error: " . $e->getMessage() . "\n"; }

    // Add token_expires
    try {
        $db->exec("ALTER TABLE users ADD COLUMN token_expires DATETIME NULL");
        echo "token_expires added.\n";
    } catch (Exception $e) { echo "token_expires error: " . $e->getMessage() . "\n"; }
    
} catch (Exception $e) {
    echo "Connection error: " . $e->getMessage();
}
