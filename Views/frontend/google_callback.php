<?php
session_start();
require_once __DIR__ . '/../../Controllers/UserController.php';

// ⚠️ IMPORTANT : REMPLACEZ CES DEUX VALEURS DEPUIS GOOGLE CLOUD CONSOLE 
$client_id = 'REMPLACEZ_PAR_VOTRE_VRAI_CLIENT_ID';
$client_secret = 'REMPLACEZ_PAR_VOTRE_VRAI_CLIENT_SECRET';

$redirect_uri = 'http://' . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']) . '/google_callback.php';

if (isset($_GET['code'])) {
    $code = $_GET['code'];

    // 1. Echange du code d'autorisation contre un Token d'Accès (Access Token)
    $token_url = 'https://oauth2.googleapis.com/token';
    $post_data = [
        'code'          => $code,
        'client_id'     => $client_id,
        'client_secret' => $client_secret,
        'redirect_uri'  => $redirect_uri,
        'grant_type'    => 'authorization_code'
    ];

    $options = [
        'http' => [
            'method'  => 'POST',
            'header'  => 'Content-type: application/x-www-form-urlencoded',
            'content' => http_build_query($post_data)
        ]
    ];
    $context  = stream_context_create($options);
    
    // Si cela crash ici avec HTTP failed 400, c'est que les clés (ID ou Secret) sont fausses/manquantes
    $response = @file_get_contents($token_url, false, $context);

    if ($response === FALSE) {
        die("Erreur de connexion (400 Bad Request). Avez-vous correctement configuré votre 'Client ID' et 'Client Secret' professionnel dans le code ?");
    }

    $token_data = json_decode($response, true);
    
    if (isset($token_data['access_token'])) {
        $access_token = $token_data['access_token'];

        // 2. Fetch du Profil de Réseaux Google Sécurisé en utilisant le Token d'Accès
        $profil_url = 'https://www.googleapis.com/oauth2/v2/userinfo?access_token=' . $access_token;
        $profil_response = file_get_contents($profil_url);
        $profil_data = json_decode($profil_response, true);

        if (isset($profil_data['email'])) {
            $email = $profil_data['email'];
            $prenom = $profil_data['given_name'] ?? 'Utilisateur';
            $nom = $profil_data['family_name'] ?? 'Google';

            $userC = new UserController();
            $existingUser = $userC->getUserByEmail($email);

            if (!$existingUser) {
                // Créer le compte à la volée avec mot de passe aveugle hashé
                $randomPassword = password_hash(bin2hex(random_bytes(8)), PASSWORD_DEFAULT);
                $user = new User($prenom, $nom, $email, $randomPassword);
                $userC->addUser($user);
            }

            // Ouverture de la session et redirection MVC Native
            $_SESSION['user_email'] = $email;
            header("Location: profile.php");
            exit();
        }
    }
} else {
    echo "L'authentification Google a été refusée ou ignorée. <a href='login.php'>Retourner</a>";
}
?>
