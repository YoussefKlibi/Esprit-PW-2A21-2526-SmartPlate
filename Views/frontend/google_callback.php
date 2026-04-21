<?php
session_start();
require_once __DIR__ . '/../../Controllers/UserController.php';

$client_id = '1010671260376-4d5hi2j046vns8l6t3kboujqaopcmrs3.apps.googleusercontent.com';
$client_secret = 'VOTRE_CLIENT_SECRET_GOOGLE';
$redirect_uri = 'http://localhost/template/Views/frontend/google_callback.php';

if (isset($_GET['code'])) {
    $code = $_GET['code'];
    
    $token_url = 'https://oauth2.googleapis.com/token';
    $postData = [
        'code' => $code,
        'client_id' => $client_id,
        'client_secret' => $client_secret,
        'redirect_uri' => $redirect_uri,
        'grant_type' => 'authorization_code'
    ];
    
    $ch = curl_init($token_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postData));
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/x-www-form-urlencoded']);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // Fix for XAMPP local SSL issue
    
    $response = curl_exec($ch);
    if ($response === false) {
        die('cURL Error (Token): ' . curl_error($ch));
    }
    curl_close($ch);
    
    $tokenData = json_decode($response, true);
    
    if (isset($tokenData['access_token'])) {
        $userinfo_url = 'https://www.googleapis.com/oauth2/v2/userinfo';
        $ch = curl_init($userinfo_url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Authorization: Bearer ' . $tokenData['access_token']]);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // Fix for XAMPP local SSL issue
        
        $userinfo = curl_exec($ch);
        if ($userinfo === false) {
            die('cURL Error (UserInfo): ' . curl_error($ch));
        }
        curl_close($ch);
        
        $userData = json_decode($userinfo, true);
        
        if (isset($userData['email'])) {
            $userC = new UserController();
            
            $googleUserData = [
                'email' => $userData['email'],
                'google_id' => $userData['id'],
                'given_name' => $userData['given_name'] ?? '',
                'family_name' => $userData['family_name'] ?? ''
            ];
            
            $existingUser = $userC->getUserByEmail($userData['email']);
            $action = $_SESSION['google_auth_action'] ?? 'login';

            if ($action === 'login' && !$existingUser) {
                echo "<div style='font-family: sans-serif; text-align:center; margin-top:50px;'>";
                echo "<h2 style='color:#dc2626;'>Accès refusé</h2>";
                echo "<p>Votre compte Google n'est pas enregistré dans notre base de données.</p>";
                echo "<p>Veuillez d'abord créer un compte.</p>";
                echo "<br><a href='register.php' style='padding:10px 20px; background:#4f46e5; color:white; text-decoration:none; border-radius:8px;'>S'inscrire</a>";
                echo "&nbsp;&nbsp;<a href='login.php' style='padding:10px 20px; border:1px solid #e5e7eb; color:#374151; text-decoration:none; border-radius:8px;'>Retour</a>";
                echo "</div>";
                exit();
            }
            
            $user = $userC->createOrGetGoogleUser($googleUserData);
            
            if ($user) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_email'] = $user['email'];
                $_SESSION['user_prenom'] = $user['prenom'];
                $_SESSION['user_nom'] = $user['nom'];

                
                header("Location: dashboard.php");
                exit();
            }
        }
    }
}

echo "Erreur d'authentification Google.";
?>