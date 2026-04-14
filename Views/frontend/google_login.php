<?php
session_start();

// ⚠️ IMPORTANT : REMPLACEZ CETTE VALEUR PAR LA VOTRE DEPUIS GOOGLE CLOUD CONSOLE 
$client_id = 'REMPLACEZ_PAR_VOTRE_VRAI_CLIENT_ID';

$redirect_uri = 'http://' . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']) . '/google_callback.php';

$auth_url = 'https://accounts.google.com/o/oauth2/v2/auth?' . http_build_query([
    'client_id'     => $client_id,
    'redirect_uri'  => $redirect_uri,
    'response_type' => 'code',
    'scope'         => 'email profile',
    'access_type'   => 'online'
]);

// Redirige vers la vraie interface d'authentification Google
header('Location: ' . filter_var($auth_url, FILTER_SANITIZE_URL));
exit();
?>
