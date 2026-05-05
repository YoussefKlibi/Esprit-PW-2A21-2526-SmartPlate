<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/../Models/User.php';

class UserController {
    private $db;
    
    public function __construct() {
        $this->db = Config::getConnexion();
    }
    
    // Vérifier si l'email existe déjà
    public function emailExists(string $email): bool {
        $stmt = $this->db->prepare("SELECT id FROM users WHERE email = :email");
        $stmt->execute(['email' => $email]);
        return $stmt->fetch() !== false;
    }
    
    // Ajouter un utilisateur
    public function addUser(User $user): bool {
        try {
            // Vérifier si l'email existe déjà
            if ($this->emailExists($user->getEmail())) {
                throw new Exception("Cet email est déjà utilisé");
            }
            
            $sql = "INSERT INTO users (prenom, nom, email, mot_de_passe, google_id, created_at) 
                    VALUES (:prenom, :nom, :email, :mot_de_passe, :google_id, NOW())";
            
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([
                'prenom' => $user->getPrenom(),
                'nom' => $user->getNom(),
                'email' => $user->getEmail(),
                'mot_de_passe' => $user->getMotDePasse(),
                'google_id' => $user->getGoogleId()
            ]);
        } catch (Exception $e) {
            error_log("Erreur addUser: " . $e->getMessage());
            throw $e;
        }
    }
    
    // Authentifier un utilisateur
    public function authenticate(string $email, string $password): ?array {
        try {
            $sql = "SELECT * FROM users WHERE email = :email";
            $stmt = $this->db->prepare($sql);
            $stmt->execute(['email' => $email]);
            $user = $stmt->fetch();
            
            if ($user && password_verify($password, $user['mot_de_passe'])) {
                return $user;
            }
            return null;
        } catch (Exception $e) {
            error_log("Erreur authenticate: " . $e->getMessage());
            return null;
        }
    }
    
    // Récupérer un utilisateur par email
    public function getUserByEmail(string $email): ?array {
        try {
            $stmt = $this->db->prepare("SELECT * FROM users WHERE email = :email");
            $stmt->execute(['email' => $email]);
            $user = $stmt->fetch();
            return $user ?: null;
        } catch (Exception $e) {
            error_log("Erreur getUserByEmail: " . $e->getMessage());
            return null;
        }
    }
    
    // Récupérer un utilisateur par Google ID
    public function getUserByGoogleId(string $googleId): ?array {
        try {
            $stmt = $this->db->prepare("SELECT * FROM users WHERE google_id = :google_id");
            $stmt->execute(['google_id' => $googleId]);
            $user = $stmt->fetch();
            return $user ?: null;
        } catch (Exception $e) {
            error_log("Erreur getUserByGoogleId: " . $e->getMessage());
            return null;
        }
    }
    
    // Récupérer un utilisateur par ID
    public function getUserById(int $id): ?array {
        try {
            $stmt = $this->db->prepare("SELECT * FROM users WHERE id = :id");
            $stmt->execute(['id' => $id]);
            $user = $stmt->fetch();
            return $user ?: null;
        } catch (Exception $e) {
            error_log("Erreur getUserById: " . $e->getMessage());
            return null;
        }
    }
    
    // Créer ou récupérer un utilisateur Google
    public function createOrGetGoogleUser(array $googleData): ?array {
        try {
            // Vérifier si l'utilisateur existe déjà
            $user = $this->getUserByEmail($googleData['email']);
            
            if ($user) {
                // Mettre à jour google_id si nécessaire
                if (empty($user['google_id'])) {
                    $stmt = $this->db->prepare("UPDATE users SET google_id = :google_id WHERE id = :id");
                    $stmt->execute([
                        'google_id' => $googleData['google_id'],
                        'id' => $user['id']
                    ]);
                }
                return $user;
            }
            
            // Créer un nouvel utilisateur
            $randomPassword = password_hash(bin2hex(random_bytes(16)), PASSWORD_DEFAULT);
            $sql = "INSERT INTO users (prenom, nom, email, mot_de_passe, google_id, created_at) 
                    VALUES (:prenom, :nom, :email, :password, :google_id, NOW())";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                'prenom' => $googleData['given_name'] ?? 'Utilisateur',
                'nom' => $googleData['family_name'] ?? 'Google',
                'email' => $googleData['email'],
                'password' => $randomPassword,
                'google_id' => $googleData['google_id']
            ]);
            
            // Récupérer l'utilisateur créé
            return $this->getUserByEmail($googleData['email']);
        } catch (Exception $e) {
            error_log("Erreur createOrGetGoogleUser: " . $e->getMessage());
            return null;
        }
    }
    
    // Sauvegarder le token de réinitialisation
    public function saveResetToken(string $email, string $token, string $expires): bool {
        try {
            $stmt = $this->db->prepare("UPDATE users SET reset_token = :token, token_expires = :expires WHERE email = :email");
            return $stmt->execute([
                'token' => $token,
                'expires' => $expires,
                'email' => $email
            ]);
        } catch (Exception $e) {
            error_log("Erreur saveResetToken: " . $e->getMessage());
            return false;
        }
    }
    
    // Récupérer un utilisateur par token valide
    public function getUserByValidToken(string $token): ?array {
        try {
            $stmt = $this->db->prepare("SELECT * FROM users WHERE reset_token = :token AND token_expires > NOW()");
            $stmt->execute(['token' => $token]);
            $user = $stmt->fetch();
            return $user ?: null;
        } catch (Exception $e) {
            error_log("Erreur getUserByValidToken: " . $e->getMessage());
            return null;
        }
    }
    
    // Mettre à jour le mot de passe
    public function updatePassword(int $id, string $hashedPassword): bool {
        try {
            // Si le compte a été bloqué par sécurité (banni), le fait de réinitialiser le mot de passe le débloque
            $stmt = $this->db->prepare("UPDATE users SET mot_de_passe = :password, reset_token = NULL, token_expires = NULL, statut = IF(statut = 'banni', 'inactif', statut) WHERE id = :id");
            return $stmt->execute([
                'password' => $hashedPassword,
                'id' => $id
            ]);
        } catch (Exception $e) {
            error_log("Erreur updatePassword: " . $e->getMessage());
            return false;
        }
    }
    
    // Lister tous les utilisateurs (avec filtre de recherche optionnel)
    public function listeUsers(?string $search = null): array {
        try {
            // Mettre inactif ceux qui n'ont pas été actifs depuis 5 minutes OU qui n'ont jamais été actifs (sauf les bannis)
            $this->db->exec("UPDATE users SET statut = 'inactif' WHERE statut = 'actif' AND (last_activity IS NULL OR last_activity < DATE_SUB(NOW(), INTERVAL 5 MINUTE))");

            $search = trim((string) $search);

            if ($search !== '') {
                $sql = "SELECT id, prenom, nom, email, created_at, statut, last_activity, last_latitude, last_longitude, last_location_update
                        FROM users
                        WHERE CAST(id AS CHAR) LIKE :search
                           OR prenom LIKE :search
                           OR nom LIKE :search
                           OR email LIKE :search
                        ORDER BY created_at DESC";

                $stmt = $this->db->prepare($sql);
                $stmt->execute([
                    'search' => '%' . $search . '%'
                ]);
            } else {
                $stmt = $this->db->query("SELECT id, prenom, nom, email, created_at, statut, last_activity, last_latitude, last_longitude, last_location_update FROM users ORDER BY created_at DESC");
            }

            return $stmt->fetchAll();
        } catch (Exception $e) {
            error_log("Erreur listeUsers: " . $e->getMessage());
            return [];
        }
    }
    
    // Supprimer un utilisateur
    public function deleteUser(int $id): bool {
        try {
            $stmt = $this->db->prepare("DELETE FROM users WHERE id = :id");
            return $stmt->execute(['id' => $id]);
        } catch (Exception $e) {
            error_log("Erreur deleteUser: " . $e->getMessage());
            return false;
        }
    }
    
    // Mettre à jour un utilisateur
    public function updateUser(int $id, string $nom, string $email): bool {
        try {
            $stmt = $this->db->prepare("UPDATE users SET nom = :nom, email = :email WHERE id = :id");
            return $stmt->execute([
                'id' => $id,
                'nom' => $nom,
                'email' => $email
            ]);
        } catch (Exception $e) {
            error_log("Erreur updateUser: " . $e->getMessage());
            return false;
        }
    }
    
    // Mettre à jour le profil complet (nom, prénom, email, mot de passe optionnel)
    public function updateUserProfile(int $id, string $nom, string $prenom, string $email, ?string $hashedPassword = null): bool {
        try {
            if ($hashedPassword) {
                $stmt = $this->db->prepare("UPDATE users SET nom = :nom, prenom = :prenom, email = :email, mot_de_passe = :password WHERE id = :id");
                return $stmt->execute([
                    'id' => $id,
                    'nom' => $nom,
                    'prenom' => $prenom,
                    'email' => $email,
                    'password' => $hashedPassword
                ]);
            } else {
                $stmt = $this->db->prepare("UPDATE users SET nom = :nom, prenom = :prenom, email = :email WHERE id = :id");
                return $stmt->execute([
                    'id' => $id,
                    'nom' => $nom,
                    'prenom' => $prenom,
                    'email' => $email
                ]);
            }
        } catch (Exception $e) {
            error_log("Erreur updateUserProfile: " . $e->getMessage());
            return false;
        }
    }
    
    // Mettre à jour le statut d'un utilisateur (ex: bannir)
    public function updateStatus(int $id, string $statut): bool {
        try {
            // Protection métier: impossible de bannir le compte admin principal
            $user = $this->getUserById($id);
            if (!$user) {
                return false;
            }

            if (($user['email'] ?? '') === 'ilyesgaied32@gmail.com' && $statut === 'banni') {
                return false;
            }

            $stmt = $this->db->prepare("UPDATE users SET statut = :statut WHERE id = :id");
            return $stmt->execute([
                'statut' => $statut,
                'id' => $id
            ]);
        } catch (Exception $e) {
            error_log("Erreur updateStatus: " . $e->getMessage());
            return false;
        }
    }
    
    // Mettre à jour la date de dernière activité et le statut actif
    public function updateLastActivity(string $email): bool {
        try {
            // On met à jour l'activité et on force le statut à 'actif' (uniquement s'il n'est pas banni)
            $stmt = $this->db->prepare("UPDATE users SET last_activity = NOW(), statut = 'actif' WHERE email = :email AND statut != 'banni'");
            return $stmt->execute(['email' => $email]);
        } catch (Exception $e) {
            error_log("Erreur updateLastActivity: " . $e->getMessage());
            return false;
        }
    }

    // Mettre à jour la dernière localisation utilisateur
    public function updateUserLocation(string $email, float $latitude, float $longitude): bool {
        try {
            if ($latitude < -90 || $latitude > 90 || $longitude < -180 || $longitude > 180) {
                return false;
            }

            date_default_timezone_set('Africa/Tunis');
            $now = date('Y-m-d H:i:s');

            $stmt = $this->db->prepare(
                "UPDATE users
                 SET last_latitude = :latitude,
                     last_longitude = :longitude,
                     last_location_update = :now
                 WHERE email = :email"
            );

            return $stmt->execute([
                'latitude' => $latitude,
                'longitude' => $longitude,
                'now' => $now,
                'email' => $email
            ]);
        } catch (Exception $e) {
            error_log("Erreur updateUserLocation: " . $e->getMessage());
            return false;
        }
    }



    public function logConnection(int $userId, string $ip, string $deviceInfo, string $status = 'Success', ?float $clientLat = null, ?float $clientLng = null): array {
        try {
            $city = 'Inconnu';
            $country = 'Inconnu';
            $lat = $clientLat;
            $lng = $clientLng;

            if ($clientLat !== null && $clientLng !== null) {
                // Reverse geocoding précis avec Nominatim (OpenStreetMap)
                $url = "https://nominatim.openstreetmap.org/reverse?format=json&lat={$clientLat}&lon={$clientLng}&addressdetails=1";
                $ch = curl_init($url);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_USERAGENT, "SmartPlate/1.0");
                curl_setopt($ch, CURLOPT_TIMEOUT, 3);
                $resp = curl_exec($ch);
                curl_close($ch);

                if ($resp) {
                    $data = json_decode($resp, true);
                    if (isset($data['address'])) {
                        $addr = $data['address'];
                        // On prend le plus précis possible (quartier, puis village, puis ville)
                        $city = $addr['suburb'] ?? $addr['village'] ?? $addr['town'] ?? $addr['city'] ?? $addr['county'] ?? 'Inconnu';
                        $country = $addr['country'] ?? 'Inconnu';
                    }
                }
                
                // Récupérer quand même la vraie IP publique si on est en local
                if ($ip === '127.0.0.1' || $ip === '::1') {
                    $ipUrl = "http://ip-api.com/json/?fields=query";
                    $chIp = curl_init($ipUrl);
                    curl_setopt($chIp, CURLOPT_RETURNTRANSFER, true);
                    curl_setopt($chIp, CURLOPT_TIMEOUT, 2);
                    $respIp = curl_exec($chIp);
                    curl_close($chIp);
                    if ($respIp) {
                        $dataIp = json_decode($respIp, true);
                        if (isset($dataIp['query'])) {
                            $ip = $dataIp['query'];
                        }
                    }
                }
            } else {
                // Fallback sur la géolocalisation par adresse IP (moins précis)
                $apiUrl = "http://ip-api.com/json/";
                if ($ip !== '127.0.0.1' && $ip !== '::1') {
                    $apiUrl .= $ip;
                }
                $apiUrl .= "?fields=status,country,city,lat,lon,query";

                $ch = curl_init($apiUrl);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_TIMEOUT, 3);
                $response = curl_exec($ch);
                curl_close($ch);

                if ($response) {
                    $data = json_decode($response, true);
                    if (isset($data['status']) && $data['status'] === 'success') {
                        $city = $data['city'] ?? 'Inconnu';
                        $country = $data['country'] ?? 'Inconnu';
                        $lat = $data['lat'] ?? null;
                        $lng = $data['lon'] ?? null;
                        
                        if (($ip === '127.0.0.1' || $ip === '::1') && isset($data['query'])) {
                            $ip = $data['query'];
                        }
                    }
                }
            }

            date_default_timezone_set('Africa/Tunis');
            $loginTime = date('Y-m-d H:i:s');

            $stmt = $this->db->prepare(
                "INSERT INTO login_history (user_id, ip_address, city, country, latitude, longitude, login_time, device_info, status)
                 VALUES (:user_id, :ip, :city, :country, :lat, :lng, :time, :device, :status)"
            );

            $stmt->execute([
                'user_id' => $userId,
                'ip' => $ip,
                'city' => $city,
                'country' => $country,
                'lat' => $lat,
                'lng' => $lng,
                'time' => $loginTime,
                'device' => substr($deviceInfo, 0, 255),
                'status' => $status
            ]);
            
            $historyId = (int) $this->db->lastInsertId();
            
            // Mettre à jour également la table users avec la dernière position
            if ($lat !== null && $lng !== null) {
                $updateUser = $this->db->prepare("UPDATE users SET last_latitude = :lat, last_longitude = :lng, last_location_update = :time WHERE id = :user_id");
                $updateUser->execute([
                    'lat' => $lat,
                    'lng' => $lng,
                    'time' => $loginTime,
                    'user_id' => $userId
                ]);
            }
            
            // --- NOUVEAU : Création de la session sécurisée dans user_sessions ---
            $sessionToken = bin2hex(random_bytes(32));
            $stmtSession = $this->db->prepare(
                "INSERT INTO user_sessions (user_id, session_token, device_name, ip_address, user_agent, location, created_at, last_activity, is_active)
                 VALUES (:uid, :token, :device, :ip, :ua, :loc, :created_at, :last_activity, 1)"
            );
            $stmtSession->execute([
                'uid'          => $userId,
                'token'        => $sessionToken,
                'device'       => substr($deviceInfo, 0, 255),
                'ip'           => $ip,
                'ua'           => substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 1000),
                'loc'          => "$city, $country",
                'created_at'   => $loginTime,
                'last_activity'=> $loginTime
            ]);
            
            // Lier ce token à l'utilisateur actuel dans la table users
            $updateUserToken = $this->db->prepare("UPDATE users SET session_token = :token, session_device = :device, session_created = :time WHERE id = :uid");
            $updateUserToken->execute([
                'token' => $sessionToken,
                'device' => substr($deviceInfo, 0, 255),
                'time' => $loginTime,
                'uid' => $userId
            ]);

            return ['history_id' => $historyId, 'session_token' => $sessionToken];
        } catch (Exception $e) {
            error_log("Erreur logConnection: " . $e->getMessage());
            return ['history_id' => 0, 'session_token' => ''];
        }
    }

    public function isSessionValidToken(string $token): bool {
        try {
            $stmt = $this->db->prepare("SELECT id FROM user_sessions WHERE session_token = :token AND is_active = 1");
            $stmt->execute(['token' => $token]);
            return $stmt->fetch() !== false;
        } catch (PDOException $e) {
            return true; // En cas d'erreur DB, on évite les déconnexions intempestives
        }
    }

    public function isSessionValidHistory(int $historyId): bool {
        try {
            $stmt = $this->db->prepare("SELECT id FROM login_history WHERE id = :id");
            $stmt->execute(['id' => $historyId]);
            return $stmt->fetch() !== false;
        } catch (PDOException $e) {
            return true;
        }
    }

    public function updateSessionActivity(string $token): void {
        try {
            $stmt = $this->db->prepare("UPDATE user_sessions SET last_activity = NOW() WHERE session_token = :token");
            $stmt->execute(['token' => $token]);
        } catch (PDOException $e) {
            // Ignorer
        }
    }

    public function isSessionValid(int $historyId): bool {
        try {
            $stmt = $this->db->prepare("SELECT id FROM login_history WHERE id = :id");
            $stmt->execute(['id' => $historyId]);
            return $stmt->fetch() !== false;
        } catch (PDOException $e) {
            return true; // En cas d'erreur DB, on ne déconnecte pas par précaution
        }
    }

    public function getLoginHistory(int $userId, int $limit = 5): array {
        try {
            $stmt = $this->db->prepare("SELECT * FROM login_history WHERE user_id = :user_id AND status != 'Logged Out' ORDER BY login_time DESC LIMIT :limit");
            // PDO can't bind limit easily without specifying type, so we use bindValue
            $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Erreur getLoginHistory: " . $e->getMessage());
            return [];
        }
    }
    public function logoutDevice(int $userId, int $historyId): bool
    {
        try {

        // Supprimer l'historique
        $stmt = $this->db->prepare("DELETE FROM login_history WHERE id = :id AND user_id = :user_id");
        return $stmt->execute([
            'id' => $historyId,
            'user_id' => $userId
        ]);
    } catch (PDOException $e) {
        error_log("Erreur dans logoutDevice : " . $e->getMessage());
        return false;
    }
}
    // Déconnexion basée sur le Token (Sécurité ultime)
    public function deactivateSessionByToken(string $token): void {
        try {
            $this->db->prepare("UPDATE user_sessions SET is_active = 0 WHERE session_token = :token")->execute(['token' => $token]);
            $this->db->prepare("UPDATE users SET session_token = NULL WHERE session_token = :token")->execute(['token' => $token]);
        } catch (PDOException $e) {}
    }
    public function cleanupExpiredSessions(): void {
        try {
            $this->db->exec("DELETE FROM user_sessions WHERE last_activity < DATE_SUB(NOW(), INTERVAL 7 DAY)");
        } catch (PDOException $e) {
            error_log("Erreur cleanupExpiredSessions: " . $e->getMessage());
        }
    }
}
?>