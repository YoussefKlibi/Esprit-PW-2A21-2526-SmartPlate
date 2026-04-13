<?php
header('Content-Type: application/json; charset=utf-8');
require __DIR__ . '/db.php';
require __DIR__ . '/Comment.php';

$db = new Database();
$pdo = $db->getConnection();
if (!$pdo) { http_response_code(500); echo json_encode(['error' => 'Database connection failed']); exit; }

$ctrl = new Comment($pdo);
$action = $_REQUEST['action'] ?? ($_SERVER['REQUEST_METHOD'] === 'POST' ? 'create' : 'list');

try {
    switch ($action) {
        case 'list':
            $article_id = isset($_GET['article_id']) ? (int)$_GET['article_id'] : null;
            $all = isset($_GET['all']) && $_GET['all'] == 1;
            $rows = $ctrl->list($article_id, !$all);
            echo json_encode($rows);
            break;

        case 'get':
            $id = isset($_REQUEST['id']) ? (int)$_REQUEST['id'] : 0;
            if ($id <= 0) { http_response_code(400); echo json_encode(['error' => 'Missing id']); break; }
            echo json_encode($ctrl->get($id));
            break;

        case 'create':
            $article_id = isset($_POST['article_id']) ? (int)$_POST['article_id'] : 0;
            $username = $_POST['username'] ?? '';
            $comment = $_POST['comment'] ?? '';
            $status = isset($_POST['status']) ? (int)$_POST['status'] : 0;

            if ($article_id <= 0 || empty($username) || empty($comment)) {
                http_response_code(400);
                echo json_encode(['error' => 'Missing required fields']);
                break;
            }

            $id = $ctrl->create($article_id, $username, $comment, $status);
            echo json_encode(['success' => true, 'id' => $id]);
            break;

        case 'update':
            $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
            if ($id <= 0) { http_response_code(400); echo json_encode(['error' => 'Missing id']); break; }
            $ok = $ctrl->update($id, $_POST);
            echo json_encode(['success' => (bool)$ok]);
            break;

        case 'delete':
            $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
            if ($id <= 0) { http_response_code(400); echo json_encode(['error' => 'Missing id']); break; }
            $ok = $ctrl->delete($id);
            echo json_encode(['success' => (bool)$ok]);
            break;

        default:
            http_response_code(400);
            echo json_encode(['error' => 'Unknown action']);
            break;
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}

?>
