<?php
class Comment
{
    protected $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function create(int $article_id, string $username, string $comment, int $status = 0, ?string $emoji = null, ?int $parent_id = null): int
    {
        $stmt = $this->pdo->prepare('INSERT INTO comments (article_id, username, comment, created_at, status, emoji, parent_id) VALUES (?, ?, ?, NOW(), ?, ?, ?)');
        $stmt->execute([$article_id, $username, $comment, $status, $emoji, $parent_id]);
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
                $stmt = $this->pdo->prepare('SELECT *, IF(badge_assigned_at >= NOW() - INTERVAL 48 HOUR, badge, NULL) AS badge FROM comments WHERE article_id = ? AND status = 1 ORDER BY created_at DESC');
            } else {
                $stmt = $this->pdo->prepare('SELECT *, IF(badge_assigned_at >= NOW() - INTERVAL 48 HOUR, badge, NULL) AS badge FROM comments WHERE article_id = ? ORDER BY created_at DESC');
            }
            $stmt->execute([$article_id]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }

        if ($publishedOnly) {
            $stmt = $this->pdo->query('SELECT *, IF(badge_assigned_at >= NOW() - INTERVAL 48 HOUR, badge, NULL) AS badge FROM comments WHERE status = 1 ORDER BY created_at DESC');
        } else {
            $stmt = $this->pdo->query('SELECT *, IF(badge_assigned_at >= NOW() - INTERVAL 48 HOUR, badge, NULL) AS badge FROM comments ORDER BY created_at DESC');
        }
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function switchVote(int $id, string $type, ?string $oldType = null): bool
    {
        $allowed = ['agree', 'disagree', 'nuanced'];
        if (!in_array($type, $allowed)) return false;

        $col = $type . '_count';
        if ($oldType && in_array($oldType, $allowed) && $oldType !== $type) {
            $oldCol = $oldType . '_count';
            $stmt = $this->pdo->prepare("UPDATE comments SET $col = $col + 1, $oldCol = GREATEST(0, $oldCol - 1) WHERE id = ?");
            return $stmt->execute([$id]);
        } else {
            $stmt = $this->pdo->prepare("UPDATE comments SET $col = $col + 1 WHERE id = ?");
            return $stmt->execute([$id]);
        }
    }

    public function assignBadge(int $id, string $badge): bool
    {
        // $badge can be empty string to clear the badge
        $badgeValue = empty($badge) ? null : $badge;
        $stmt = $this->pdo->prepare('UPDATE comments SET badge = ?, badge_assigned_at = NOW() WHERE id = ?');
        return $stmt->execute([$badgeValue, $id]);
    }

    public function report(int $id): bool
    {
        $stmt = $this->pdo->prepare("UPDATE comments SET report_count = report_count + 1 WHERE id = ?");
        return $stmt->execute([$id]);
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
        // First delete children to avoid orphans
        $stmt = $this->pdo->prepare('DELETE FROM comments WHERE parent_id = ?');
        $stmt->execute([$id]);

        // Then delete the comment itself
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

    public function voteReclassification(int $id, string $newStance): bool
    {
        if (!in_array($newStance, ['pour', 'contre', 'neutre'])) return false;

        $col = 'reclass_' . $newStance;
        
        // Incrémente le vote
        $stmt = $this->pdo->prepare("UPDATE comments SET $col = $col + 1 WHERE id = ?");
        $stmt->execute([$id]);

        // Vérifie si le seuil de 10 est atteint
        $stmt = $this->pdo->prepare("SELECT $col AS count FROM comments WHERE id = ?");
        $stmt->execute([$id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($row && $row['count'] >= 10) {
            // Reclassification ! On modifie la stance et on reset les compteurs de reclassification
            $stmt = $this->pdo->prepare("UPDATE comments SET stance = ?, reclass_pour = 0, reclass_contre = 0, reclass_neutre = 0 WHERE id = ?");
            return $stmt->execute([$newStance, $id]);
        }
        return true;
    }
}
?>
