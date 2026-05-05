# 📘 Guide Technique du Projet SmartPlate

Ce document explique l'architecture du projet SmartPlate et le rôle de chaque composant et fonction.

## 🏗️ Architecture MVC
Le projet suit le modèle **MVC** (Modèle-Vue-Contrôleur) pour séparer la logique de données, l'affichage et la gestion des requêtes.

- **Modèle** : Gère les interactions avec la base de données (SQL).
- **Vue** : L'interface utilisateur (HTML/CSS/JS).
- **Contrôleur** : Fait le pont entre la Vue et le Modèle.

---

## 📁 Structure des Dossiers

- `/config` : Configuration de la base de données.
- `/Controller` : Logique de traitement des actions.
- `/Model` : Classes représentant les données (Articles, Commentaires).
- `/View` : Fichiers HTML des interfaces Front et Back.
- `/css` : Feuilles de style (Thèmes Clair/Sombre).
- `/uploads` : Dossier de stockage des images d'articles.

---

## ⚙️ Composants Principaux

### 1. Base de Données (`config/database.php`)
- **`getConnection()`** : Établit la connexion PDO avec MySQL.
- **`createTables()`** : Crée automatiquement les tables `articles` et `comments` si elles n'existent pas.
- **`checkAndAddColumn()`** : Gère les mises à jour de schéma (ex: ajout de `parent_id`).

### 2. Gestion des Articles (`ArticleController.php` & `Article.php`)
**Contrôleur :**
- **`listArticles()`** : Récupère la liste des articles.
- **`createArticle()`** : Valide et crée un nouvel article (avec upload d'image).
- **`rateArticle()`** : Permet aux utilisateurs de voter pour un article (système d'étoiles).
- **`handleImageUpload()`** : Sécurise et stocke les images téléchargées.

**Modèle :**
- **`list()`** : Requête SQL pour lister les articles.
- **`addRating()`** : Calcule et met à jour la moyenne des notes.

### 3. Système de Commentaires (`CommentController.php` & `Comment.php`)
**Contrôleur :**
- **`listComments()`** : Liste les commentaires d'un article.
- **`createComment()`** : Ajoute un commentaire ou une réponse.
- **`voteComment()`** : Gère les Likes/Dislikes.
- **`reportComment()`** : Permet de signaler un commentaire inapproprié.

**Modèle :**
- **`create()`** : Insère un commentaire (gère le `parent_id` pour les réponses).
- **`delete()`** : Supprime un commentaire **et toutes ses réponses** (suppression en cascade).
- **`switchVote()`** : Met à jour les compteurs de votes en évitant les doublons (via localStorage côté client).

---

## 🎨 Interfaces (Views)

### Front-Office (`View/front-office.php`)
- **`loadArticles()`** : Charge dynamiquement les articles via AJAX.
- **`loadComments(articleId)`** : Affiche les commentaires sous forme de fils de discussion (replies indentées).
- **`window.commentIsSubmitting`** : Verrou de sécurité pour empêcher d'ajouter un commentaire deux fois.
- **`toggleDarkMode()`** : Bascule entre les thèmes Clair et Sombre avec persistance (`localStorage`).

### Back-Office (`View/back-office.php`)
- Interface d'administration pour gérer les articles et modérer les commentaires.
- Utilise des modales personnalisées (`customAlert`, `customConfirm`) pour une expérience fluide.

---

## 🛠️ Fonctions de Sécurité & Robustesse
- **Validation côté Client** : Vérification des longueurs de texte et présence de lettres avant envoi.
- **Validation côté Serveur** : `preg_match` et `htmlspecialchars` pour prévenir les injections.
- **Nettoyage Automatique** : Système qui supprime les "réponses orphelines" si le parent est supprimé.

---

## 🛡️ Modération Automatique des Commentaires

Système métier intégré dans `CommentController.php` et `Comment.php` qui détecte et traite automatiquement les commentaires inappropriés.

### Fonctionnement
1. **Détection** (`isBadComment()`) : Lors de la soumission, le contrôleur vérifie le texte du commentaire contre une liste de ~50+ mots interdits (FR + EN). Utilise des regex avec word boundaries (`\b`) pour éviter les faux positifs.
2. **Signalement automatique** : Si un mot interdit est détecté, le commentaire est créé avec `flagged=1` et `flagged_at=NOW()` via `createFlagged()`.
3. **Affichage visuel** : Les commentaires flaggés apparaissent avec :
   - Une bannière rouge ⚠️ d'alerte
   - Le texte censuré (mots remplacés par `***`)
   - Un compte à rebours en temps réel
   - Les boutons d'action (like, reply, edit) masqués
4. **Suppression automatique** (`deleteFlaggedExpired()`) : À chaque requête API, un "lazy cron" supprime les commentaires flaggés datant de plus de 2 minutes.

### Colonnes BDD
- `flagged` (TINYINT) : 0 = normal, 1 = signalé
- `flagged_at` (DATETIME) : Horodatage du signalement

---

## 🔔 Notifications Push (Admin Back-Office)

Système de notifications en temps réel pour l'administrateur, utilisant l'API Web Notification et un polling AJAX.

### Fonctionnement
1. **Permission** : Au chargement du back-office, le navigateur demande l'autorisation d'envoyer des notifications.
2. **Polling** : Toutes les 10 secondes, le système interroge `check_new` pour détecter de nouveaux commentaires.
3. **Notification native** : Si un nouveau commentaire est trouvé, une notification Windows apparaît avec :
   - Le nom de l'auteur et un extrait du commentaire
   - L'icône du site (logo SmartPlate)
   - Un message spécial si le commentaire est flaggé (modération)
4. **Badge visuel** : Un compteur 🔔 dans la topbar du back-office affiche le nombre de notifications non lues.

### API utilisée
- **`Comment::getNewerThan($lastId)`** — Récupère les commentaires avec un ID > lastId
- **`CommentController::checkNewComments()`** — Point d'entrée `action=check_new&last_id=X`

> Ce projet a été conçu pour être moderne, réactif et facile à maintenir grâce à sa structure modulaire.
