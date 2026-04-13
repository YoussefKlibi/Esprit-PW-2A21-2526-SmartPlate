# Smart Plate — Blog (Alimentation Durable & Intelligente)

Petit projet PHP simple pour gérer des articles et des commentaires avec une interface "front" et une interface d'administration (back).

## Objectif

- Publier et gérer des articles (titre, type, image, contenu, auteur, statut).
- Ajouter et modérer les commentaires des lecteurs.

## Principales fonctionnalités

- API simple via `article_controller.php` et `comment_controller.php` (JSON responses).
- Interface publique: [inter-front.html](inter-front.html)
- Interface d'administration: [inter-back.html](inter-back.html)
- Script d'initialisation des tables: [create_tables.php](create_tables.php)

## Prérequis

- XAMPP (Apache + MySQL) ou tout serveur PHP/MySQL équivalent.
- PHP with PDO MySQL extension enabled.

## Installation rapide

1. Placez le dossier du projet dans le répertoire web de XAMPP, par exemple:

```
C:\xampp\htdocs\smart_plate\
```

2. Vérifiez la configuration de la base de données dans [db.php](db.php). Par défaut la configuration est:

- host: `localhost`
- database: `smart_plate`
- username: `root`
- password: `` (vide)

Créez la base `smart_plate` via phpMyAdmin ou changez le nom dans `db.php` pour correspondre à votre base.

3. Créez les tables (exécutez une fois) :

- Ouvrez `http://localhost/smart_plate/create_tables.php` dans votre navigateur
- ou exécutez depuis la ligne de commande: `php create_tables.php`

4. Ouvrez l'interface publique: `http://localhost/smart_plate/inter-front.html`
   Ouvrez l'admin: `http://localhost/smart_plate/inter-back.html`

## Points d'accès / API

- Lister les articles (publiés):

```
GET article_controller.php?action=list
```

- Lister tous les articles (admin):

```
GET article_controller.php?action=list&all=1
```

- Créer un article (POST form-data):

```
POST article_controller.php?action=create
Fields: name, type, image_url, content, author, status
```

- Mettre à jour un article (POST):

```
POST article_controller.php?action=update
Fields: id, plus les champs à modifier
```

- Supprimer un article (POST): `action=delete`, `id`.

- Commentaires: `comment_controller.php` utilise des actions similaires (`list`, `create`, `update`, `delete`).

Exemples `curl` (adapt to your host/path):

```
curl -X POST -F "action=create" -F "name=Mon article" -F "content=Texte" http://localhost/smart_plate/article_controller.php
```

## Validation et règles importantes

- Server-side validation: la classe `Article` (fichier [Article.php](Article.php)) contient maintenant une méthode `validateArticleData()` qui contrôle `name`, `type`, `image_url`, `content`, `author`, et `status`. Les entrées manquantes ou invalides lèvent une `InvalidArgumentException` et renvoient une erreur JSON depuis les contrôleurs.
- HTML5 validation: les attributs `required` ont été retirés des formulaires (`inter-back.html` et `inter-front.html`) et le formulaire admin a l'attribut `novalidate` — la validation est donc principalement assurée côté serveur.

## Fichiers clés

- [db.php](db.php): connexion PDO + méthode `createTables()`.
- [create_tables.php](create_tables.php): script pour créer `articles` et `comments`.
- [Article.php](Article.php): modèle pour les articles (+ validation ajoutée).
- [Comment.php](Comment.php): modèle pour les commentaires.
- [article_controller.php](article_controller.php): endpoints JSON pour les articles.
- [comment_controller.php](comment_controller.php): endpoints JSON pour les commentaires.
- [inter-front.html](inter-front.html): interface publique.
- [inter-back.html](inter-back.html): interface d'administration.

## Prochaines améliorations possibles

- Ajouter une validation côté client (fichier JavaScript) si vous voulez une UX plus guidée.
- Authentification pour l'admin.
- Upload d'images plutôt que simple URL.

## Support / Debug

- Vérifiez les erreurs de connexion MySQL dans `db.php` si la page renvoie une erreur de connexion.
- Activez l'affichage des erreurs PHP en développement si nécessaire.

---

Si vous voulez, je peux :

- ajouter un fichier `validation.js` pour une validation côté client et intégrer les formulaires,
- ou mettre à jour `db.php` pour charger la configuration depuis un fichier `.env`.