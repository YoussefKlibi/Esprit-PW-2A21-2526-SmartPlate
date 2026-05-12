<?php
/**
 * Forum API Router — point d'entrée pour les requêtes AJAX du module Forum.
 * Suit le même principe que les autres contrôleurs du projet integration/.
 */
require_once __DIR__ . '/../../config.php';

header('Content-Type: application/json; charset=utf-8');

$controller = $_GET['controller'] ?? '';

switch ($controller) {
    case 'article':
        require_once __DIR__ . '/ArticleController.php';
        (new ArticleController())->handleRequest();
        break;

    case 'comment':
        require_once __DIR__ . '/CommentController.php';
        (new CommentController())->handleRequest();
        break;

    default:
        http_response_code(400);
        echo json_encode(['error' => 'Unknown controller']);
}