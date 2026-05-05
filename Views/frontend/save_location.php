<?php
session_start();
header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION['user_email'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Non authentifie.']);
    exit();
}

require_once __DIR__ . '/../../Controllers/UserController.php';

$input = json_decode(file_get_contents('php://input'), true);
if (!is_array($input)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Corps de requete invalide.']);
    exit();
}

$latitude = isset($input['latitude']) ? (float) $input['latitude'] : null;
$longitude = isset($input['longitude']) ? (float) $input['longitude'] : null;

if ($latitude === null || $longitude === null) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Latitude/longitude manquantes.']);
    exit();
}

$userC = new UserController();
$ok = $userC->updateUserLocation($_SESSION['user_email'], $latitude, $longitude);

if (!$ok) {
    http_response_code(422);
    echo json_encode(['success' => false, 'error' => 'Impossible de sauvegarder la localisation.']);
    exit();
}

date_default_timezone_set('Africa/Tunis');
echo json_encode([
    'success' => true,
    'latitude' => round($latitude, 6),
    'longitude' => round($longitude, 6),
    'updated_at' => date('Y-m-d H:i:s')
]);
