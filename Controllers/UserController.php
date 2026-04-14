<?php
require_once __DIR__ . '/config.php';  // config dans le même dossier
require_once __DIR__ . '/../Models/User.php';

class UserController {
    
   // Ajouter un utilisateur
public function addUser($user) {
    $db = Config::getConnexion();
    try {
        // Vérifier si l'email existe déjà
        $check = $db->prepare('SELECT id FROM users WHERE email = :email');
        $check->execute(['email' => $user->getEmail()]);
        if ($check->fetch()) {
            throw new Exception("Cet email existe déjà");
        }
        
        $req = $db->prepare('INSERT INTO users (prenom, nom, email, mot_de_passe) VALUES(:prenom, :nom, :email, :mot_de_passe)');
        $req->execute([
            'prenom' => $user->getPrenom(),
            'nom' => $user->getNom(),
            'email' => $user->getEmail(),
            'mot_de_passe' => $user->getMotDePasse()
        ]);
        return true;
    } catch (Exception $e) {
        throw new Exception($e->getMessage());
    }  
}

    // Récupérer un utilisateur par Email
    public function getUserByEmail($email) {
        $db = Config::getConnexion();
        try {
            $req = $db->prepare('SELECT * FROM users WHERE email = :email LIMIT 1');
            $req->execute(['email' => $email]);
            return $req->fetch();
        } catch (Exception $e) {
            die('Error: ' . $e->getMessage());
        }
    }

    // Récupérer un utilisateur par ID
    public function getUserById($id) {
        $db = Config::getConnexion();
        try {
            $req = $db->prepare('SELECT * FROM users WHERE id = :id LIMIT 1');
            $req->execute(['id' => $id]);
            return $req->fetch();
        } catch (Exception $e) {
            die('Error: ' . $e->getMessage());
        }
    }

    // Lister tous les utilisateurs
    public function listeUsers() {
        $db = Config::getConnexion();
        try {
            $liste = $db->query('SELECT * FROM users'); 
            return $liste->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            die('Error: ' . $e->getMessage());
        }
    }

    // Supprimer un utilisateur par ID
    public function deleteUser($id) {
        $db = Config::getConnexion();
        try {
            $req = $db->prepare("DELETE FROM users WHERE id=:id");
            $req->execute(['id' => $id]);
        } catch (Exception $e) {
            die('Error: ' . $e->getMessage());
        }
    }

    // Modifier un utilisateur
    public function updateUser($id, $nom, $email) {
        $db = Config::getConnexion();
        try {
            $req = $db->prepare('UPDATE users SET nom=:nom, email=:email WHERE id=:id');
            $req->execute([
                'id' => $id,
                'nom' => $nom,
                'email' => $email
            ]);
        } catch (Exception $e) {
            die('Error: ' . $e->getMessage());
        }
    }

    // Sauvegarder le token de réinitialisation
    public function saveResetToken($email, $token, $expires) {
        $db = Config::getConnexion();
        try {
            $req = $db->prepare('UPDATE users SET reset_token = :token, token_expires = :expires WHERE email = :email');
            $req->execute([
                'token' => $token,
                'expires' => $expires,
                'email' => $email
            ]);
        } catch (Exception $e) {
            die('Error: ' . $e->getMessage());
        }
    }

    // Récupérer un utilisateur par son token
    public function getUserByToken($token) {
        $db = Config::getConnexion();
        try {
            $req = $db->prepare('SELECT * FROM users WHERE reset_token = :token AND token_expires > NOW() LIMIT 1');
            $req->execute(['token' => $token]);
            return $req->fetch();
        } catch (Exception $e) {
            die('Error: ' . $e->getMessage());
        }
    }

    // Mettre à jour le mot de passe et effacer le token
    public function updatePassword($id, $hashedPassword) {
        $db = Config::getConnexion();
        try {
            $req = $db->prepare('UPDATE users SET mot_de_passe = :mot_de_passe, reset_token = NULL, token_expires = NULL WHERE id = :id');
            $req->execute([
                'mot_de_passe' => $hashedPassword,
                'id' => $id
            ]);
        } catch (Exception $e) {
            die('Error: ' . $e->getMessage());
        }
    }
}
?>