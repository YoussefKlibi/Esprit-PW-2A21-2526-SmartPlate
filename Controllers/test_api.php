<?php
// test_api.php
require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/Controllers/UserController.php';

session_start();
$_SESSION['user_email'] = 'ilyessgaied@gmail.com'; // Remplace par ton email

$apiUrl = 'http://localhost/template/WebAuthnController.php?action=registerChallenge';

$ch = curl_init($apiUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$response = curl_exec($ch);
curl_close($ch);

echo "Test API : ";
echo "<pre>";
print_r(json_decode($response, true));
echo "</pre>";