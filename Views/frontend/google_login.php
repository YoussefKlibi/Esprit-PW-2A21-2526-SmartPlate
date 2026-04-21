<?php
session_start();

$client_id = 'VOTRE_CLIENT_ID_GOOGLE.apps.googleusercontent.com';

$_SESSION['google_auth_action'] = $_GET['action'] ?? 'login';

$redirect_uri = 'http://localhost/template/Views/frontend/google_callback.php';

$auth_url = 'https://accounts.google.com/o/oauth2/v2/auth?' . http_build_query([
    'client_id' => $client_id,
    'redirect_uri' => $redirect_uri,
    'response_type' => 'code',
    'scope' => 'email profile',
    'access_type' => 'online',
    'prompt' => 'select_account'
]);

header('Location: ' . filter_var($auth_url, FILTER_SANITIZE_URL));
exit();
?>