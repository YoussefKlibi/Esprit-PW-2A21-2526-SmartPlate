<?php
require_once 'c:\xampp\htdocs\template\Controllers\UserController.php';
try {
    $db = config::getConnexion();
    $db->exec("ALTER TABLE users ADD COLUMN statut VARCHAR(20) DEFAULT 'actif'");
    echo 'Column statut added successfully.';
} catch (PDOException $e) {
    if (strpos($e->getMessage(), 'Duplicate column') !== false) {
        echo 'Column already exists.';
    } else {
        echo 'Error: ' . $e->getMessage();
    }
}
