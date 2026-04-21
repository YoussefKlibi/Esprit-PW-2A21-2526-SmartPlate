<?php
class Comment
{
    protected $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function create(int $article_id, string $username, string $comment, int $status = 0): int
    {
        $stmt = $this->pdo->prepare('INSERT INTO comments (article_id, username, comment, created_at, status) VALUES (?, ?, ?, NOW(), ?)');
        $stmt->execute([$article_id, $username, $comment, $status]);
        return (int)$this->pdo->lastInsertId();
    }

    public function get(int $id): ?array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM comments WHERE id = ?');
        $stmt->execute([$id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row === false ? null : $row;
    }

    public function list(?int $article_id = null, bool $publishedOnly = true): array
    {
        if ($article_id !== null) {
            if ($publishedOnly) {
                $stmt = $this->pdo->prepare('SELECT id, article_id, username, comment, created_at FROM comments WHERE article_id = ? AND status = 1 ORDER BY created_at DESC');
            } else {
                $stmt = $this->pdo->prepare('SELECT * FROM comments WHERE article_id = ? ORDER BY created_at DESC');
            }
            $stmt->execute([$article_id]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }

        if ($publishedOnly) {
            $stmt = $this->pdo->query('SELECT id, article_id, username, comment, created_at FROM comments WHERE status = 1 ORDER BY created_at DESC');
        } else {
            $stmt = $this->pdo->query('SELECT * FROM comments ORDER BY created_at DESC');
        }
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function update(int $id, array $data): bool
    {
        $fields = [];
        $params = [];
        if (isset($data['username'])) { $fields[] = 'username = ?'; $params[] = $data['username']; }
        if (isset($data['comment'])) { $fields[] = 'comment = ?'; $params[] = $data['comment']; }
        if (isset($data['status'])) { $fields[] = 'status = ?'; $params[] = (int)$data['status']; }

        if (empty($fields)) {
            return false;
        }

        $params[] = $id;
        $sql = 'UPDATE comments SET ' . implode(', ', $fields) . ' WHERE id = ?';
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute($params);
    }

    public function delete(int $id): bool
    {
        $stmt = $this->pdo->prepare('DELETE FROM comments WHERE id = ?');
        $stmt->execute([$id]);
        return $stmt->rowCount() > 0;
    }

    /**
     * Get comment counts per article (for statistics).
     */
    public function countByArticle(): array
    {
        $sql = 'SELECT a.id, a.name, COUNT(c.id) AS comment_count 
                FROM articles a 
                LEFT JOIN comments c ON c.article_id = a.id 
                WHERE a.status = 1 
                GROUP BY a.id, a.name 
                ORDER BY comment_count DESC';
        $stmt = $this->pdo->query($sql);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
}
?>
