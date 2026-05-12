<?php
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/UserController.php';
session_start();

use lbuchs\WebAuthn\WebAuthn;

header('Content-Type: application/json');

function base64url_decode(string $data): string {
    return base64_decode(strtr($data, '-_', '+/') . str_repeat('=', 3 - (3 + strlen($data)) % 4));
}

$webauthn = new WebAuthn('SmartPlate', 'localhost', ['android-key', 'android-safetynet', 'apple', 'fido-u2f', 'none', 'packed', 'tpm']);

$action = $_GET['action'] ?? '';
$userC = new UserController();

try {
    if ($action === 'registerChallenge') {
        if (!isset($_SESSION['user_email'])) {
            throw new Exception("Vous devez être connecté pour configurer Face ID.");
        }
        
        $user = $userC->getUserByEmail($_SESSION['user_email']);
        if (!$user) {
            throw new Exception("Utilisateur non trouvé.");
        }

        // user id, name, display name
        $userId = (string) $user['id'];
        $userName = $user['email'];
        $userDisplayName = $user['prenom'] . ' ' . $user['nom'];

        // Force local device authenticator (platform) + required user verification.
        // This avoids cross-device Google passkey flow when possible.
        $createArgs = $webauthn->getCreateArgs(\trim($userId), $userName, $userDisplayName, 60 * 4, true, 'required', false);
        $_SESSION['webauthn_challenge'] = $webauthn->getChallenge();

        echo json_encode(['success' => true, 'args' => $createArgs]);
        exit;
    }

   if ($action === 'registerVerify') {
    if (!isset($_SESSION['user_email'])) {
        throw new Exception("Non autorisé");
    }
    
    $user = $userC->getUserByEmail($_SESSION['user_email']);
    
    $input = json_decode(file_get_contents('php://input'), true);
    if (!$input) {
        throw new Exception("Données invalides");
    }
    
    // Log pour déboguer
    error_log("Données reçues: " . print_r($input, true));
    
    $clientDataJSON = base64url_decode($input['clientDataJSON']);
    $attestationObject = base64url_decode($input['attestationObject']);
    $challenge = $_SESSION['webauthn_challenge'] ?? '';
    
    try {
        // Enforce user verification check during attestation validation.
        $data = $webauthn->processCreate($clientDataJSON, $attestationObject, $challenge, true, true, false);
        
        $credentialId = base64_encode($data->credentialId);
        $publicKey = $data->credentialPublicKey;
        // Some authenticators may not return userHandle during registration.
        // Store a stable fallback based on internal user id.
        $userHandleRaw = $data->userHandle ?? (string) $user['id'];
        $userHandle = base64_encode((string) $userHandleRaw);
        
        $result = $userC->updateWebAuthnRegistration($user['id'], $credentialId, $publicKey, $userHandle);
        
        if ($result) {
            echo json_encode(['success' => true, 'msg' => 'Face ID configuré avec succès !']);
        } else {
            throw new Exception("Erreur lors de la mise à jour de la base de données");
        }
    } catch (Exception $e) {
        error_log("Erreur processCreate: " . $e->getMessage());
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
    exit;
}

    if ($action === 'loginChallenge') {
        // We use discoverable credentials, so empty array of credential IDs
        // Allow only internal authenticator for login challenge.
        $getArgs = $webauthn->getGetArgs([], 60 * 4, false, false, false, false, true, 'required');
        $_SESSION['webauthn_challenge'] = $webauthn->getChallenge();

        echo json_encode(['success' => true, 'args' => $getArgs]);
        exit;
    }

    if ($action === 'loginVerify') {
        $input = json_decode(file_get_contents('php://input'), true);
        if (!$input) {
            throw new Exception("Données invalides");
        }

        $clientDataJSON = base64url_decode($input['clientDataJSON']);
        $authenticatorData = base64url_decode($input['authenticatorData']);
        $signature = base64url_decode($input['signature']);
        $userHandleBytes = isset($input['userHandle']) && $input['userHandle'] ? base64url_decode($input['userHandle']) : null;
        $id = base64url_decode($input['id']); // credential id
        $challenge = $_SESSION['webauthn_challenge'] ?? '';

        // Find user by userHandle when available, else fallback to credential id.
        if ($userHandleBytes) {
            $userHandle = base64_encode($userHandleBytes);
            $user = $userC->getWebAuthnUserByHandle($userHandle);
        } else {
            $user = $userC->getWebAuthnUserByCredentialId(base64_encode($id));
        }

        if (!$user || !$user['webauthn_enabled']) {
            throw new Exception("Aucun compte Face ID associé.");
        }

        // Check if the credential ID matches
        if ($user['webauthn_credential_id'] !== base64_encode($id)) {
            throw new Exception("Identifiant d'appareil non reconnu.");
        }

        $publicKey = $user['webauthn_public_key'];
        $signCount = $user['webauthn_sign_count'];

        // Validate
        $webauthn->processGet($clientDataJSON, $authenticatorData, $signature, $publicKey, $challenge, null, true);
        
        // Log the user in
        $_SESSION['user_email'] = $user['email'];
        
        $userC = new UserController();
        $userC->logConnection(
            $user['id'], 
            $_SERVER['REMOTE_ADDR'] ?? '', 
            $_SERVER['HTTP_USER_AGENT'] ?? ''
        );
        $_SESSION['user_id'] = (int) $user['id'];
        $userC->updateLastActivity($user['email']);
        
        if ($user['email'] === 'ilyesgaied32@gmail.com') {
            $_SESSION['is_admin'] = true;
            $redirect = '../backend/admin_welcome.php';
        } else {
            $_SESSION['is_admin'] = false;
            $redirect = 'dashboard.php';
        }

        echo json_encode(['success' => true, 'redirect' => $redirect]);
        exit;
    }

    throw new Exception("Action inconnue");

} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
