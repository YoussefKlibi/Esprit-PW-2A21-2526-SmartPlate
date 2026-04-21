<?php
require_once __DIR__ . '/../Model/Comment.php';

class CommentController
{
    private $model;

    public function __construct(PDO $pdo)
    {
        $this->model = new Comment($pdo);
    }

    /**
     * Route the request to the appropriate method.
     */
    public function handleRequest()
    {
        header('Content-Type: application/json; charset=utf-8');
        $action = $_REQUEST['action'] ?? ($_SERVER['REQUEST_METHOD'] === 'POST' ? 'create' : 'list');

        try {
            switch ($action) {
                case 'list':   $this->listComments(); break;
                case 'stats':  $this->statsComments(); break;
                case 'get':    $this->getComment(); break;
                case 'create': $this->createComment(); break;
                case 'update': $this->updateComment(); break;
                case 'delete': $this->deleteComment(); break;
                default:
                    http_response_code(400);
                    echo json_encode(['error' => 'Unknown action']);
            }
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => $e->getMessage()]);
        }
    }

    private function listComments()
    {
        $article_id = isset($_GET['article_id']) ? (int)$_GET['article_id'] : null;
        $all = isset($_GET['all']) && $_GET['all'] == 1;
        $rows = $this->model->list($article_id, !$all);
        echo json_encode($rows);
    }

    private function statsComments()
    {
        $rows = $this->model->countByArticle();
        echo json_encode($rows);
    }

    private function getComment()
    {
        $id = isset($_REQUEST['id']) ? (int)$_REQUEST['id'] : 0;
        if ($id <= 0) {
            http_response_code(400);
            echo json_encode(['error' => 'Missing id']);
            return;
        }
        echo json_encode($this->model->get($id));
    }

    private function createComment()
    {
        $article_id = isset($_POST['article_id']) ? (int)$_POST['article_id'] : 0;
        $username   = $_POST['username'] ?? '';
        $comment    = $_POST['comment'] ?? '';
        $status     = isset($_POST['status']) ? (int)$_POST['status'] : 0;

        if ($article_id <= 0 || empty($username) || empty($comment)) {
            http_response_code(400);
            echo json_encode(['error' => 'Missing required fields']);
            return;
        }

        $id = $this->model->create($article_id, $username, $comment, $status);
        echo json_encode(['success' => true, 'id' => $id]);
    }

    private function updateComment()
    {
        $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
        if ($id <= 0) {
            http_response_code(400);
            echo json_encode(['error' => 'Missing id']);
            return;
        }
        $ok = $this->model->update($id, $_POST);
        echo json_encode(['success' => (bool)$ok]);
    }

    private function deleteComment()
    {
        $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
        if ($id <= 0) {
            http_response_code(400);
            echo json_encode(['error' => 'Missing id']);
            return;
        }
        $ok = $this->model->delete($id);
        echo json_encode(['success' => (bool)$ok]);
    }
}
?>
