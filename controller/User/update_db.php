<?php
require_once __DIR__ . '/config.php';

try {
    $db = Config::getConnexion();
    
    // Add WebAuthn columns
    $db->exec("ALTER TABLE users ADD COLUMN webauthn_credential_id TEXT NULL;");
    $db->exec("ALTER TABLE users ADD COLUMN webauthn_public_key TEXT NULL;");
    $db->exec("ALTER TABLE users ADD COLUMN webauthn_user_handle VARCHAR(255) NULL;");
    $db->exec("ALTER TABLE users ADD COLUMN webauthn_sign_count INT DEFAULT 0;");
    $db->exec("ALTER TABLE users ADD COLUMN webauthn_enabled TINYINT DEFAULT 0;");
    
    echo "Base de données mise à jour avec succès.\n";
} catch (PDOException $e) {
    if (strpos($e->getMessage(), 'Duplicate column name') !== false) {
        echo "Les colonnes existent déjà.\n";
    } else {
        echo "Erreur PDO : " . $e->getMessage() . "\n";
    }
} catch (Exception $e) {
    echo "Erreur : " . $e->getMessage() . "\n";
}
