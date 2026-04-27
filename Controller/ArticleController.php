<?php
require_once __DIR__ . '/../Model/Article.php';

class ArticleController
{
    private $model;

    public function __construct(PDO $pdo)
    {
        $this->model = new Article($pdo);
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
                case 'list':   $this->listArticles(); break;
                case 'search': $this->searchArticles(); break;
                case 'get':    $this->getArticle(); break;
                case 'create': $this->createArticle(); break;
                case 'update': $this->updateArticle(); break;
                case 'delete': $this->deleteArticle(); break;
                case 'rate':   $this->rateArticle(); break;
                default:
                    http_response_code(400);
                    echo json_encode(['error' => 'Unknown action']);
            }
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => $e->getMessage()]);
        }
    }

    private function listArticles()
    {
        $all = isset($_GET['all']) && $_GET['all'] == 1;
        $rows = $this->model->list(!$all);
        echo json_encode($rows);
    }

    private function searchArticles()
    {
        $keyword = isset($_GET['keyword']) ? trim($_GET['keyword']) : '';
        $all = isset($_GET['all']) && $_GET['all'] == 1;
        if ($keyword === '') {
            // If empty keyword, return all articles
            $rows = $this->model->list(!$all);
        } else {
            $rows = $this->model->search($keyword, !$all);
        }
        echo json_encode($rows);
    }

    private function getArticle()
    {
        $id = isset($_REQUEST['id']) ? (int)$_REQUEST['id'] : 0;
        if ($id <= 0) {
            http_response_code(400);
            echo json_encode(['error' => 'Missing id']);
            return;
        }
        echo json_encode($this->model->get($id));
    }

    private function createArticle()
    {
        $name    = trim($_POST['name'] ?? '');
        $type    = trim($_POST['type'] ?? '');
        $content = trim($_POST['content'] ?? '');
        $author  = trim($_POST['author'] ?? 'Admin');
        $status  = isset($_POST['status']) ? (int)$_POST['status'] : 1;

        if (empty($name) || empty($type) || empty($content)) {
            http_response_code(400);
            echo json_encode(['error' => 'Tous les champs sont obligatoires.']);
            return;
        }

        if (mb_strlen($name) > 30) {
            http_response_code(400);
            echo json_encode(['error' => 'Le nom de l\'article ne doit pas dépasser 30 caractères.']);
            return;
        }

        if (!preg_match('/^[a-zA-Z0-9\s\x{00C0}-\x{017F}]+$/u', $type)) {
            http_response_code(400);
            echo json_encode(['error' => 'Le type ne doit pas contenir de caractères spéciaux.']);
            return;
        }

        if (mb_strlen($content) < 30) {
            http_response_code(400);
            echo json_encode(['error' => 'Le contenu de l\'article ne doit pas être inférieur à 30 caractères.']);
            return;
        }

        $image_url = $this->handleImageUpload();

        $id = $this->model->create($name, $type, $image_url, $content, $author, $status);
        echo json_encode(['success' => true, 'id' => $id]);
    }

    private function updateArticle()
    {
        $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
        if ($id <= 0) {
            http_response_code(400);
            echo json_encode(['error' => 'Missing id']);
            return;
        }

        $data = $_POST;
        
        $name = trim($data['name'] ?? '');
        $type = trim($data['type'] ?? '');
        $content = trim($data['content'] ?? '');

        if (isset($data['name']) && mb_strlen($name) > 30) {
            http_response_code(400);
            echo json_encode(['error' => 'Le nom de l\'article ne doit pas dépasser 30 caractères.']);
            return;
        }
        if (isset($data['type']) && !preg_match('/^[a-zA-Z0-9\s\x{00C0}-\x{017F}]+$/u', $type)) {
            http_response_code(400);
            echo json_encode(['error' => 'Le type ne doit pas contenir de caractères spéciaux.']);
            return;
        }
        if (isset($data['content']) && mb_strlen($content) < 30) {
            http_response_code(400);
            echo json_encode(['error' => 'Le contenu de l\'article ne doit pas être inférieur à 30 caractères.']);
            return;
        }

        // Handle image upload on update
        $newImage = $this->handleImageUpload();
        if ($newImage !== '') {
            $data['image_url'] = $newImage;
        }
        // Don't pass empty image_url if no new file was uploaded
        if ($newImage === '' && !isset($data['image_url'])) {
            unset($data['image_url']);
        }

        $ok = $this->model->update($id, $data);
        echo json_encode(['success' => (bool)$ok]);
    }

    private function deleteArticle()
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

    private function rateArticle()
    {
        $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
        $stars = isset($_POST['stars']) ? (int)$_POST['stars'] : 0;

        if ($id <= 0 || $stars < 1 || $stars > 5) {
            http_response_code(400);
            echo json_encode(['error' => 'Invalid rating data']);
            return;
        }

        $result = $this->model->addRating($id, $stars);
        echo json_encode(['success' => true, 'rating_sum' => $result['rating_sum'], 'rating_count' => $result['rating_count']]);
    }

    /**
     * Handle image file upload. Returns the relative path or empty string.
     */
    private function handleImageUpload(): string
    {
        if (!isset($_FILES['image']) || $_FILES['image']['error'] === UPLOAD_ERR_NO_FILE) {
            return '';
        }

        $file = $_FILES['image'];
        if ($file['error'] !== UPLOAD_ERR_OK) {
            throw new Exception('Erreur lors de l\'upload de l\'image.');
        }

        // Validate type
        $allowed = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        if (!in_array($file['type'], $allowed)) {
            throw new Exception('Type de fichier non autorisé. Formats acceptés : JPG, PNG, GIF, WEBP.');
        }

        // Validate size (max 5MB)
        if ($file['size'] > 5 * 1024 * 1024) {
            throw new Exception('L\'image est trop volumineuse (max 5 Mo).');
        }

        // Generate unique name
        $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = uniqid('img_', true) . '.' . strtolower($ext);
        $uploadDir = __DIR__ . '/../uploads/';

        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $dest = $uploadDir . $filename;
        if (!move_uploaded_file($file['tmp_name'], $dest)) {
            throw new Exception('Erreur lors de la sauvegarde de l\'image.');
        }

        return 'uploads/' . $filename;
    }
}
?>
