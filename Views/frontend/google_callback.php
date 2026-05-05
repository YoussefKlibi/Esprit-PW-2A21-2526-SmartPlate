<?php
session_start();
require_once __DIR__ . '/../../Controllers/UserController.php';


$localConfigPath = __DIR__ . '/google_oauth_config.php';
if (file_exists($localConfigPath)) {
    require_once $localConfigPath;
}

$client_id = getenv('GOOGLE_CLIENT_ID') ?: ($client_id ?? '');
$client_secret = getenv('GOOGLE_CLIENT_SECRET') ?: ($client_secret ?? '');
$redirect_uri = 'http://localhost/template/Views/frontend/google_callback.php';

if ($client_id === '' || $client_secret === '') {
    die("Configuration Google OAuth manquante. Définissez GOOGLE_CLIENT_ID et GOOGLE_CLIENT_SECRET.");
}

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
    // Traiter le retour
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
    // Récupérer les informations utilisateur
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
                if (isset($user['statut']) && $user['statut'] === 'banni') {
                    echo "<div style='font-family: sans-serif; text-align:center; margin-top:50px;'>";
                    echo "<h2 style='color:#dc2626;'>Accès refusé</h2>";
                    echo "<p>Votre compte a été suspendu par un administrateur.</p>";
                    echo "<br><a href='login.php' style='padding:10px 20px; border:1px solid #e5e7eb; color:#374151; text-decoration:none; border-radius:8px;'>Retour à l'accueil</a>";
                    echo "</div>";
                    exit();
                }

                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_email'] = $user['email'];
                
                // Enregistrer la connexion en historique (sans bloquer)
                $userC->logConnection(
                    $user['id'], 
                    $_SERVER['REMOTE_ADDR'] ?? '', 
                    $_SERVER['HTTP_USER_AGENT'] ?? ''
                );
                $_SESSION['user_prenom'] = $user['prenom'];
                $_SESSION['user_nom'] = $user['nom'];
                
                // Mettre à jour l'activité immédiatement
                $userC->updateLastActivity($user['email']);

                // Envoyer un email de notification (Alerte de connexion)
                $token = bin2hex(random_bytes(32));
                $expires = date("Y-m-d H:i:s", time() + 3600);
                $userC->saveResetToken($user['email'], $token, $expires);

                require_once __DIR__ . '/../PHPMailer/src/Exception.php';
                require_once __DIR__ . '/../PHPMailer/src/PHPMailer.php';
                require_once __DIR__ . '/../PHPMailer/src/SMTP.php';

                try {
                    $mail = new \PHPMailer\PHPMailer\PHPMailer(true);
                    $mail->isSMTP();
                    $mail->Host       = 'smtp.gmail.com';
                    $mail->SMTPAuth   = true;
                    $mail->Username   = 'ilyesgaied32@gmail.com';
                    $mail->Password   = 'bwqw xdyg vphr xrxn';
                    $mail->SMTPSecure = \PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_SMTPS;
                    $mail->Port       = 465;

                    $mail->setFrom('ilyesgaied32@gmail.com', 'SmartPlate Security');
                    $mail->addAddress($user['email']);
                    $mail->isHTML(true);
                    $mail->Subject = "Nouvelle connexion a votre compte SmartPlate";
                    
                    $denyLink = "http://localhost/template/Views/frontend/deny_login.php?token=" . $token;
                    
                    $mail->Body = "
                        <h2>Alerte de sécurité</h2>
                        <p>Bonjour {$user['prenom']},</p>
                        <p>Une nouvelle connexion a été détectée sur votre compte via Google.</p>
                        <p><strong>Appareil :</strong> " . ($_SERVER['HTTP_USER_AGENT'] ?? 'Inconnu') . "</p>
                        <p><strong>Adresse IP :</strong> " . ($_SERVER['REMOTE_ADDR'] ?? 'Inconnue') . "</p>
                        <br>
                        <p>Si c'est vous, vous pouvez ignorer cet email (Confirmation automatique).</p>
                        <p><strong>Si ce n'est PAS vous (Non confirmation) :</strong> Cliquez sur le lien ci-dessous pour bloquer l'accès.</p>
                        <a href='{$denyLink}' style='display:inline-block; padding:10px 15px; background-color:#dc2626; color:#ffffff; text-decoration:none; border-radius:5px;'>Ce n'était pas moi (Déconnecter)</a>
                    ";
                    $mail->send();
                } catch (\Exception $e) {
                    // Ignore email error to not block login
                }
                
                if ($user['email'] === 'ilyesgaied32@gmail.com') {
                    $_SESSION['is_admin'] = true;
                    header("Location: ../backend/admin_welcome.php");
                } else {
                    $_SESSION['is_admin'] = false;
                    header("Location: dashboard.php");
                }
                exit();
            }
        }
    } else {
        // Output the actual error from Google to help with debugging
        echo "<div style='font-family: sans-serif; text-align:center; margin-top:50px;'>";
        echo "<h2 style='color:#dc2626;'>Erreur lors de l'obtention du jeton d'accès</h2>";
        echo "<p>Google a renvoyé l'erreur suivante :</p>";
        echo "<pre style='background:#f3f4f6; padding:15px; border-radius:8px; display:inline-block; text-align:left;'>";
        echo htmlspecialchars(print_r($tokenData, true));
        echo "</pre>";
        echo "<p>Veuillez vérifier votre <b>Client Secret</b> dans le fichier google_callback.php.</p>";
        echo "</div>";
        exit();
    }
}

echo "Erreur d'authentification Google (Code manquant).";
?>
