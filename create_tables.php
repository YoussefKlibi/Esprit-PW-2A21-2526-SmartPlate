<?php
// create_tables.php — run this once (via browser) to create required tables
require 'db.php';

$db = new Database();
if ($db->createTables()) {
    echo "Table 'comments' created or already exists.";
} else {
    echo "Erreur lors de la création des tables (voir messages).";
}

?>
