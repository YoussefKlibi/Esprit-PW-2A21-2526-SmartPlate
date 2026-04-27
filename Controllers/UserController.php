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
            $stmt = $this->db->prepare("UPDATE users SET mot_de_passe = :password, reset_token = NULL, token_expires = NULL WHERE id = :id");
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
                $sql = "SELECT id, prenom, nom, email, created_at, statut, last_activity
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
                $stmt = $this->db->query("SELECT id, prenom, nom, email, created_at, statut, last_activity FROM users ORDER BY created_at DESC");
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
}
?>