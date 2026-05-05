<?php
class Article
{
    protected $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function create(string $name, string $type, string $image_url, string $content, string $author = 'Admin', int $status = 1): int
    {
        $data = [
            'name' => $name,
            'type' => $type,
            'image_url' => $image_url,
            'content' => $content,
            'author' => $author,
            'status' => $status,
        ];
        $validated = $this->validateArticleData($data, false);

        $stmt = $this->pdo->prepare('INSERT INTO articles (name, type, image_url, content, author, created_at, status) VALUES (?, ?, ?, ?, ?, NOW(), ?)');
        $stmt->execute([$validated['name'], $validated['type'], $validated['image_url'], $validated['content'], $validated['author'], $validated['status']]);
        return (int)$this->pdo->lastInsertId();
    }

    public function get(int $id): ?array
    {
        $stmt = $this->pdo->prepare('SELECT id, name, type, image_url, content, author, created_at, status, rating_sum, rating_count FROM articles WHERE id = ?');
        $stmt->execute([$id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row === false ? null : $row;
    }

    public function list(bool $publishedOnly = true): array
    {
        if ($publishedOnly) {
            $stmt = $this->pdo->query('SELECT id, name, type, image_url, content, author, created_at, rating_sum, rating_count FROM articles WHERE status = 1 ORDER BY created_at DESC');
        } else {
            $stmt = $this->pdo->query('SELECT id, name, type, image_url, content, author, created_at, status, rating_sum, rating_count FROM articles ORDER BY created_at DESC');
        }
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function search(string $keyword, bool $publishedOnly = true): array
    {
        $keyword = '%' . $keyword . '%';
        if ($publishedOnly) {
            $stmt = $this->pdo->prepare('SELECT id, name, type, image_url, content, author, created_at, rating_sum, rating_count FROM articles WHERE name LIKE ? AND status = 1 ORDER BY created_at DESC');
        } else {
            $stmt = $this->pdo->prepare('SELECT id, name, type, image_url, content, author, created_at, status, rating_sum, rating_count FROM articles WHERE name LIKE ? ORDER BY created_at DESC');
        }
        $stmt->execute([$keyword]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function update(int $id, array $data): bool
    {
        $validated = $this->validateArticleData($data, true);

        $fields = [];
        $params = [];
        if (array_key_exists('name', $validated)) { $fields[] = 'name = ?'; $params[] = $validated['name']; }
        if (array_key_exists('type', $validated)) { $fields[] = 'type = ?'; $params[] = $validated['type']; }
        if (array_key_exists('image_url', $validated)) { $fields[] = 'image_url = ?'; $params[] = $validated['image_url']; }
        if (array_key_exists('content', $validated)) { $fields[] = 'content = ?'; $params[] = $validated['content']; }
        if (array_key_exists('author', $validated)) { $fields[] = 'author = ?'; $params[] = $validated['author']; }
        if (array_key_exists('status', $validated)) { $fields[] = 'status = ?'; $params[] = (int)$validated['status']; }

        if (empty($fields)) {
            return false;
        }

        $params[] = $id;
        $sql = 'UPDATE articles SET ' . implode(', ', $fields) . ' WHERE id = ?';
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute($params);
    }

    public function delete(int $id): bool
    {
        $this->pdo->beginTransaction();
        try {
            // Supprimer tous les commentaires (y compris réponses) liés à l’article
            $stmtComments = $this->pdo->prepare('DELETE FROM comments WHERE article_id = ?');
            $stmtComments->execute([$id]);

            $stmt = $this->pdo->prepare('DELETE FROM articles WHERE id = ?');
            $stmt->execute([$id]);
            $ok = $stmt->rowCount() > 0;
            $this->pdo->commit();
            return $ok;
        } catch (Throwable $e) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            return false;
        }
    }

    public function addRating(int $id, int $stars): array
    {
        $stmt = $this->pdo->prepare('UPDATE articles SET rating_sum = rating_sum + ?, rating_count = rating_count + 1 WHERE id = ?');
        $stmt->execute([$stars, $id]);

        $stmt2 = $this->pdo->prepare('SELECT rating_sum, rating_count FROM articles WHERE id = ?');
        $stmt2->execute([$id]);
        return $stmt2->fetch(PDO::FETCH_ASSOC) ?: ['rating_sum' => 0, 'rating_count' => 0];
    }

    private function validateArticleData(array $data, bool $isUpdate = false): array
    {
        $validated = [];

        if (!$isUpdate || array_key_exists('name', $data)) {
            $name = trim($data['name'] ?? '');
            if ($name === '') {
                throw new InvalidArgumentException('Name is required.');
            }
            if (mb_strlen($name) > 255) {
                throw new InvalidArgumentException('Name is too long (max 255 characters).');
            }
            $validated['name'] = $name;
        }

        if (!$isUpdate || array_key_exists('type', $data)) {
            $type = trim($data['type'] ?? '');
            if ($type === '') {
                throw new InvalidArgumentException('Type is required.');
            }
            if (mb_strlen($type) > 100) {
                throw new InvalidArgumentException('Type is too long (max 100 characters).');
            }
            $validated['type'] = $type;
        }

        if (array_key_exists('image_url', $data)) {
            $image_url = trim($data['image_url'] ?? '');
            $validated['image_url'] = $image_url;
        } elseif (!$isUpdate) {
            $validated['image_url'] = '';
        }

        if (!$isUpdate || array_key_exists('content', $data)) {
            $content = trim($data['content'] ?? '');
            if ($content === '') {
                throw new InvalidArgumentException('Content is required.');
            }
            $validated['content'] = $content;
        }

        if (array_key_exists('author', $data)) {
            $author = trim($data['author'] ?? 'Admin');
            if ($author === '') {
                $author = 'Admin';
            }
            if (mb_strlen($author) > 100) {
                throw new InvalidArgumentException('Author is too long (max 100 characters).');
            }
            $validated['author'] = $author;
        } elseif (!$isUpdate) {
            $validated['author'] = $data['author'] ?? 'Admin';
        }

        if (array_key_exists('status', $data)) {
            $status = (int)$data['status'];
            if (!in_array($status, [0, 1], true)) {
                throw new InvalidArgumentException('Status must be 0 or 1.');
            }
            $validated['status'] = $status;
        } elseif (!$isUpdate) {
            $validated['status'] = (int)($data['status'] ?? 1);
        }

        return $validated;
    }
}
