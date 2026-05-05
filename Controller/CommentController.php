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
            $this->model->purgeExpiredToxic();

            switch ($action) {
                case 'list':   $this->listComments(); break;
                case 'stats':  $this->statsComments(); break;
                case 'get':    $this->getComment(); break;
                case 'create': $this->createComment(); break;
                case 'update': $this->updateComment(); break;
                case 'delete': $this->deleteComment(); break;
                case 'vote':   $this->voteComment(); break;
                case 'report': $this->reportComment(); break;
                case 'reclassify': $this->reclassifyComment(); break;
                case 'badge':  $this->assignBadge(); break;
                case 'check_new': $this->checkNewComments(); break;
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
        $publicMask = isset($_GET['public_mask']) && $_GET['public_mask'] === '1';
        $rows = $this->model->list($article_id, !$all, $publicMask);
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
        $emoji      = $_POST['emoji'] ?? null;
        $status     = isset($_POST['status']) ? (int)$_POST['status'] : 0;
        $parent_id  = isset($_POST['parent_id']) && (int)$_POST['parent_id'] > 0 ? (int)$_POST['parent_id'] : null;

        if ($article_id <= 0 || empty($username) || empty(trim($comment))) {
            http_response_code(400);
            echo json_encode(['error' => 'Missing required fields']);
            return;
        }

        if (!preg_match('/[a-zA-Z\x{00C0}-\x{017F}]/u', $comment)) {
            http_response_code(400);
            echo json_encode(['error' => 'Le commentaire doit contenir au moins une lettre.']);
            return;
        }

        $result = $this->model->create($article_id, $username, $comment, $status, $emoji, $parent_id);
        $id = $result['id'];
        $toxic = $result['toxic'];
        $this->trayNotifyNewComment($id, $username, $comment, $article_id);
        echo json_encode(['success' => true, 'id' => $id, 'toxic' => $toxic]);
    }

    /**
     * Nouveaux commentaires après last_id (polling cloche admin).
     */
    private function checkNewComments(): void
    {
        $lastId = isset($_GET['last_id']) ? (int)$_GET['last_id'] : 0;
        $newComments = $this->model->getNewerThan($lastId);
        echo json_encode(['new_comments' => $newComments, 'count' => count($newComments)]);
    }

    private function trayNotifyNewComment(int $id, string $username, string $comment, int $articleId): void
    {
        if (PHP_OS_FAMILY !== 'Windows') {
            return;
        }
        $script = realpath(__DIR__ . '/../scripts/tray-notify.ps1');
        if ($script === false || !is_readable($script)) {
            return;
        }
        $snippet = mb_substr(preg_replace('/\s+/', ' ', $comment), 0, 120);
        $title = 'Smart Plate — nouveau commentaire';
        $body = $username . ' · article #' . $articleId . "\n" . $snippet;
        $cmd = 'powershell.exe -NoProfile -ExecutionPolicy Bypass -File '
            . escapeshellarg($script)
            . ' -Title ' . escapeshellarg($title)
            . ' -Body ' . escapeshellarg($body);
        if (function_exists('popen')) {
            @pclose(@popen('cmd /c start /B ' . $cmd, 'r'));
        }
    }

    private function voteComment()
    {
        $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
        $type = $_POST['type'] ?? '';
        $oldType = $_POST['oldType'] ?? null;

        if ($id <= 0 || !in_array($type, ['agree', 'disagree', 'nuanced'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Invalid vote data']);
            return;
        }

        $ok = $this->model->switchVote($id, $type, $oldType);
        echo json_encode(['success' => (bool)$ok]);
    }

    private function updateComment()
    {
        $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
        if ($id <= 0) {
            http_response_code(400);
            echo json_encode(['error' => 'Missing id']);
            return;
        }

        if (isset($_POST['comment'])) {
            $comment = $_POST['comment'];
            if (empty(trim($comment))) {
                http_response_code(400);
                echo json_encode(['error' => 'Comment cannot be empty']);
                return;
            }
            if (!preg_match('/[a-zA-Z\x{00C0}-\x{017F}]/u', $comment)) {
                http_response_code(400);
                echo json_encode(['error' => 'Le commentaire doit contenir au moins une lettre.']);
                return;
            }
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

    private function reportComment()
    {
        $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
        if ($id <= 0) {
            http_response_code(400);
            echo json_encode(['error' => 'Missing id']);
            return;
        }
        $ok = $this->model->report($id);
        echo json_encode(['success' => (bool)$ok]);
    }

    private function reclassifyComment()
    {
        $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
        $stance = $_POST['stance'] ?? '';
        if ($id <= 0 || empty($stance)) {
            http_response_code(400);
            echo json_encode(['error' => 'Missing id or stance']);
            return;
        }
        $ok = $this->model->voteReclassification($id, $stance);
        echo json_encode(['success' => (bool)$ok]);
    }

    private function assignBadge()
    {
        $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
        $badge = isset($_POST['badge']) ? $_POST['badge'] : '';
        if ($id <= 0) {
            http_response_code(400);
            echo json_encode(['error' => 'Missing id']);
            return;
        }
        $ok = $this->model->assignBadge($id, $badge);
        echo json_encode(['success' => (bool)$ok]);
    }
}
