<?php
session_start();
require_once __DIR__ . '/../../Controllers/UserController.php';

$message = "";
$messageType = "";
$showCodeForm = false;
$email = "";
$smtpUsername = 'ilyesgaied32@gmail.com';
$smtpPassword = 'bwqw xdyg vphr xrxn';
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'] ?? '';
    $code = trim($_POST['code'] ?? '');

    if (!empty($code) && !empty($email)) {
        // Vérification du code
        $userC = new UserController();
        $user = $userC->getUserByValidToken($code);
        
        if ($user && $user['email'] === $email) {
            // Rediriger vers reset_password.php avec le code (token)
            header("Location: reset_password.php?token=" . urlencode($code));
            exit();
        } else {
            $messageType = "error";
            $message = "Le code fourni est incorrect ou a expiré.";
            $showCodeForm = true;
        }
    } elseif (!empty($email)) {
        $userC = new UserController();
        $user = $userC->getUserByEmail($email);
        
        if ($user) {
            // Générer un code court (6 caractères majuscules aléatoires)
            $token = strtoupper(substr(bin2hex(random_bytes(16)), 0, 6));
            // Date limite : 1 heure
            $expires = date("Y-m-d H:i:s", time() + 3600);
            
            $userC->saveResetToken($email, $token, $expires);
            
            $sujet = "Votre code de réinitialisation - SmartPlate";
            $messageMail = "Bonjour,\n\nVous avez demandé la réinitialisation de votre mot de passe.\n\n";
            $messageMail .= "Voici votre code de vérification à saisir sur le site :\n";
            $messageMail .= "CODE : " . $token . "\n\n";
            $messageMail .= "Ce code est valable pendant 1 heure.\n";

            require_once __DIR__ . '/../PHPMailer/src/Exception.php';
            require_once __DIR__ . '/../PHPMailer/src/PHPMailer.php';
            require_once __DIR__ . '/../PHPMailer/src/SMTP.php';

            $mail = new \PHPMailer\PHPMailer\PHPMailer(true);

            try {
                // Configuration du serveur SMTP
                $mail->isSMTP();
                $mail->Host       = 'smtp.gmail.com';
                $mail->SMTPAuth   = true;
                $mail->Username   = $smtpUsername;
                $mail->Password   = $smtpPassword;
                $mail->SMTPSecure = \PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_SMTPS; // Port 465
                $mail->Port       = 465;

                // Destinataires
                $mail->setFrom($smtpUsername, 'SmartPlate');
                $mail->addAddress($email);

                // Contenu
                $mail->isHTML(false);
                $mail->Subject = $sujet;
                $mail->Body    = $messageMail;

                $mail->send();

                $messageType = "success";
                $message = "Un code à 6 caractères vient de vous être envoyé par email. Veuillez le saisir ci-dessous.";
                $showCodeForm = true;
            } catch (\Exception $e) {
                $messageType = "error";
                $message = "Erreur lors de l'envoi de l'email : {$mail->ErrorInfo}";
            }
        } else {
            // Sécurité anti-divulgation
            $messageType = "error";
            $message = "Aucun compte n'est associé à cette adresse e-mail.";
        }
    } else {
        $messageType = "error";
        $message = "Veuillez entrer une adresse e-mail valide.";
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SmartPlate - Mot de passe oublié</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../css/style.css">
    <style>
        .error-text { color: red; font-size: 13px; margin-top: 5px; display: block; }
        .msg-box { padding: 15px; border-radius: 8px; margin-bottom: 20px; text-align: center; font-weight: 500; }
        .msg-error { background: #ffebee; color: red; }
        .msg-success { background: #e8f5e9; color: #2e7d32; }
        
        /* OTP Styles */
        .otp-container {
            display: flex;
            justify-content: center;
            gap: 12px;
            margin: 25px 0;
        }
        .otp-input {
            width: 50px;
            height: 60px;
            text-align: center;
            font-size: 24px;
            font-weight: 700;
            border: 2px solid #e2e8f0;
            border-radius: 12px;
            background: #f8fafc;
            transition: all 0.3s ease;
            text-transform: uppercase;
            color: #1e293b;
        }
        .otp-input:focus {
            border-color: #2563eb;
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.15);
            outline: none;
            background: #ffffff;
        }
    </style>
</head>
<body>

    <div class="login-wrapper">
        <div class="login-left">
            <a href="login.php" class="brand">
                <div class="logo-icon-login">SP</div>
                <span class="brand-name">SmartPlate</span>
            </a>

            <h1>Mot de passe oublié ? 🔒</h1>
            <p>Entrez votre adresse e-mail ci-dessous et nous vous enverrons un code d'accès sécurisé pour réinitialiser votre mot de passe.</p>

            <?php if (!empty($message)): ?>
                <div class="msg-box <?= $messageType == 'error' ? 'msg-error' : 'msg-success' ?>">
                    <?= $message ?>
                </div>
            <?php endif; ?>

            <?php if ($showCodeForm): ?>
            <form id="codeForm" action="forgot_password.php" method="POST" class="modern-form">
                <input type="hidden" name="email" value="<?= htmlspecialchars($email) ?>">
                
                <div class="form-group" style="text-align: center;">
                    <label>Code de vérification à 6 caractères</label>
                    <div class="otp-container">
                        <input type="text" class="otp-input" maxlength="1" pattern="[A-Za-z0-9]" required autocomplete="off">
                        <input type="text" class="otp-input" maxlength="1" pattern="[A-Za-z0-9]" required autocomplete="off">
                        <input type="text" class="otp-input" maxlength="1" pattern="[A-Za-z0-9]" required autocomplete="off">
                        <input type="text" class="otp-input" maxlength="1" pattern="[A-Za-z0-9]" required autocomplete="off">
                        <input type="text" class="otp-input" maxlength="1" pattern="[A-Za-z0-9]" required autocomplete="off">
                        <input type="text" class="otp-input" maxlength="1" pattern="[A-Za-z0-9]" required autocomplete="off">
                    </div>
                    <input type="hidden" id="code" name="code" value="">
                </div>

                <button type="submit" class="btn-main" style="margin-top: 20px; width: 100%;">Vérifier le code</button>
            </form>
            
            <script>
                document.addEventListener('DOMContentLoaded', () => {
                    const otpInputs = document.querySelectorAll('.otp-input');
                    const hiddenCode = document.getElementById('code');

                    // Focus first input automatically when form is shown
                    if (otpInputs.length > 0) {
                        setTimeout(() => otpInputs[0].focus(), 100);
                    }

                    otpInputs.forEach((input, index) => {
                        input.addEventListener('input', (e) => {
                            if (e.target.value.length > 1) {
                                e.target.value = e.target.value.slice(0, 1);
                            }
                            if (e.target.value !== '' && index < otpInputs.length - 1) {
                                otpInputs[index + 1].focus();
                            }
                            updateHiddenCode();
                        });

                        input.addEventListener('keydown', (e) => {
                            if (e.key === 'Backspace' && e.target.value === '' && index > 0) {
                                otpInputs[index - 1].focus();
                            }
                            // Move left/right with arrows
                            if (e.key === 'ArrowLeft' && index > 0) {
                                otpInputs[index - 1].focus();
                            }
                            if (e.key === 'ArrowRight' && index < otpInputs.length - 1) {
                                otpInputs[index + 1].focus();
                            }
                        });

                        input.addEventListener('paste', (e) => {
                            e.preventDefault();
                            const pasteData = (e.clipboardData || window.clipboardData).getData('text').slice(0, 6).toUpperCase();
                            const chars = pasteData.replace(/[^A-Z0-9]/g, '').split('');
                            
                            chars.forEach((char, i) => {
                                if (i < otpInputs.length) {
                                    otpInputs[i].value = char;
                                }
                            });
                            
                            if (chars.length > 0) {
                                const focusIndex = Math.min(chars.length, otpInputs.length - 1);
                                otpInputs[focusIndex].focus();
                                updateHiddenCode();
                            }
                        });
                    });

                    function updateHiddenCode() {
                        const code = Array.from(otpInputs).map(input => input.value).join('');
                        hiddenCode.value = code.toUpperCase();
                    }
                    
                    document.getElementById('codeForm').addEventListener('submit', (e) => {
                        updateHiddenCode();
                        if (hiddenCode.value.length !== 6) {
                            e.preventDefault();
                            alert("Veuillez entrer le code à 6 caractères complet.");
                        }
                    });
                });
            </script>
            <?php else: ?>
            <form id="forgotForm" action="forgot_password.php" method="POST" class="modern-form">
                <div class="form-group">
                    <label for="email">Adresse e-mail</label>
                    <input type="text" id="emailForgot" name="email" class="form-control" placeholder="exemple@mail.com" value="<?= htmlspecialchars($email) ?>">
                    <span id="errorEmailForgot" class="error-text"></span>
                </div>

                <button type="submit" class="btn-main" style="margin-top: 20px;">Envoyer le code</button>
            </form>
            <?php endif; ?>

            <div style="margin-top: 2rem; text-align: center; padding-top: 1rem; border-top: 1px solid #e2e8f0;">
                <p style="color: var(--text-gray); font-size: 0.95rem; margin-bottom: 0.8rem;">Vous vous souvenez de votre mot de passe ?</p>
                <a href="login.php" class="btn-secondary" style="display: block; text-decoration: none; border-radius: 14px; padding: 1rem; width: 100%; box-sizing: border-box;">Retourner à la connexion</a>
            </div>
        </div>

        <div class="login-right">
            <h2>Sécurité avant tout</h2>
            <p>Vos données sont protégées. Ne partagez jamais votre code de réinitialisation de mot de passe avec une tierce personne.</p>
        </div>
    </div>

    <!-- Client-side validation -->
    <script src="../js/validation.js"></script>
</body>
</html>
