<?php
session_start();

$client_id = '819419054239-d4ab7t2c11gpo7kjbeim2tk55df0va8c.apps.googleusercontent.com';

$_SESSION['google_auth_action'] = $_GET['action'] ?? 'login';

$redirect_uri = 'http://localhost:8080/Integration_User_Reclamation/view/User/frontend/google_callback.php';

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
//Démarrer l'authentification