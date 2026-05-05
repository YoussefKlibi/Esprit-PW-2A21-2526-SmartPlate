<?php
/**
 * SmartPlate — Point d'entrée unique (Routeur MVC)
 * 
 * URLs:
 *   index.php                              → Front-office (vue par défaut)
 *   index.php?page=back                    → Back-office
 *   index.php?controller=article&action=X  → API Article (JSON)
 *   index.php?controller=comment&action=X  → API Comment (JSON)
 *   action=check_new — nouveaux commentaires (notifications back-office)
 */

// Load database configuration
require_once __DIR__ . '/config/database.php';

// Determine what to do
$page       = $_GET['page'] ?? null;
$controller = $_GET['controller'] ?? null;

// ─── API Requests (JSON) ──────────────────────────────────────
if ($controller !== null) {
    // Connect to database
    $db = new Database();
    $pdo = $db->getConnection();
    if (!$pdo) {
        http_response_code(500);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['error' => 'Database connection failed']);
        exit;
    }
    $db->createTables();

    switch ($controller) {
        case 'article':
            require_once __DIR__ . '/Controller/ArticleController.php';
            $ctrl = new ArticleController($pdo);
            $ctrl->handleRequest();
            break;

        case 'comment':
            require_once __DIR__ . '/Controller/CommentController.php';
            $ctrl = new CommentController($pdo);
            $ctrl->handleRequest();
            break;

        default:
            http_response_code(404);
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['error' => 'Controller not found']);
            break;
    }
    exit;
}

// ─── Page Requests (HTML Views) ───────────────────────────────
switch ($page) {
    case 'back':
        require __DIR__ . '/View/back-office.php';
        break;

    default:
        // Front-office par défaut
        require __DIR__ . '/View/front-office.php';
        break;
}
