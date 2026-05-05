<?php
session_start();
header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION['user_email'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Non authentifié.']);
    exit();
}

require_once __DIR__ . '/../../Controllers/UserController.php';

$input = json_decode(file_get_contents('php://input'), true);
if (!is_array($input) || empty($input['current_password']) || empty($input['new_password'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Veuillez remplir tous les champs.']);
    exit();
}

$userC = new UserController();
$userInfo = $userC->getUserByEmail($_SESSION['user_email']);

if (!$userInfo || !password_verify($input['current_password'], $userInfo['mot_de_passe'])) {
    echo json_encode(['success' => false, 'error' => 'Le mot de passe actuel est incorrect.']);
    exit();
}

if ($input['new_password'] !== $input['confirm_password']) {
    echo json_encode(['success' => false, 'error' => 'Les nouveaux mots de passe ne correspondent pas.']);
    exit();
}

if (strlen($input['new_password']) < 8 || !preg_match('/[A-Z]/', $input['new_password']) || !preg_match('/[0-9]/', $input['new_password']) || !preg_match('/[^a-zA-Z0-9]/', $input['new_password'])) {
    echo json_encode(['success' => false, 'error' => 'Le nouveau mot de passe ne respecte pas les critères de sécurité.']);
    exit();
}

$hashedPassword = password_hash($input['new_password'], PASSWORD_DEFAULT);
$success = $userC->updateUserProfile(
    $userInfo['id'], 
    $userInfo['nom'], 
    $userInfo['prenom'], 
    $userInfo['email'], 
    $hashedPassword
);

if ($success) {
    echo json_encode(['success' => true, 'message' => 'Votre mot de passe a été modifié avec succès. Les autres appareils non autorisés seront déconnectés lors de leur prochaine activité.']);
} else {
    echo json_encode(['success' => false, 'error' => 'Erreur lors de la mise à jour du mot de passe.']);
}
