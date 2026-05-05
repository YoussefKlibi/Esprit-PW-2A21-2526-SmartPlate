<?php
require_once __DIR__ . '/CommentModeration.php';

class Comment
{
    protected $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    /**
     * @return array{id: int, toxic: bool}
     */
    public function create(int $article_id, string $username, string $comment, int $status = 0, ?string $emoji = null, ?int $parent_id = null): array
    {
        $toxic = CommentModeration::isToxic($comment);
        $toxicFlag = $toxic ? 1 : 0;
        if ($toxic) {
            $status = 1;
        }

        // Date de suppression = même horloge que MySQL (évite décalage fuseau / PHP vs NOW() en purge)
        $stmt = $this->pdo->prepare('INSERT INTO comments (article_id, username, comment, created_at, status, emoji, parent_id, toxic_flag, toxic_delete_at) VALUES (?, ?, ?, NOW(), ?, ?, ?, ?, IF(? = 1, DATE_ADD(NOW(), INTERVAL 1 MINUTE), NULL))');
        $stmt->execute([$article_id, $username, $comment, $status, $emoji, $parent_id, $toxicFlag, $toxicFlag]);
        $id = (int)$this->pdo->lastInsertId();

        return ['id' => $id, 'toxic' => $toxic];
    }

    public function get(int $id): ?array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM comments WHERE id = ?');
        $stmt->execute([$id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row === false ? null : $row;
    }

    public function list(?int $article_id = null, bool $publishedOnly = true, bool $publicMaskToxic = false): array
    {
        if ($article_id !== null) {
            if ($publishedOnly) {
                $stmt = $this->pdo->prepare('SELECT *, IF(badge_assigned_at >= NOW() - INTERVAL 48 HOUR, badge, NULL) AS badge FROM comments WHERE article_id = ? AND status = 1 ORDER BY created_at DESC');
            } else {
                $stmt = $this->pdo->prepare('SELECT *, IF(badge_assigned_at >= NOW() - INTERVAL 48 HOUR, badge, NULL) AS badge FROM comments WHERE article_id = ? ORDER BY created_at DESC');
            }
            $stmt->execute([$article_id]);
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
            return $this->applyPublicToxicMask($rows, $publicMaskToxic);
        }

        // Jointure sur articles : ne pas lister les commentaires orphelins (article supprimé)
        if ($publishedOnly) {
            $stmt = $this->pdo->query('SELECT c.*, IF(c.badge_assigned_at >= NOW() - INTERVAL 48 HOUR, c.badge, NULL) AS badge FROM comments c INNER JOIN articles a ON a.id = c.article_id WHERE c.status = 1 ORDER BY c.created_at DESC');
        } else {
            $stmt = $this->pdo->query('SELECT c.*, IF(c.badge_assigned_at >= NOW() - INTERVAL 48 HOUR, c.badge, NULL) AS badge FROM comments c INNER JOIN articles a ON a.id = c.article_id ORDER BY c.created_at DESC');
        }
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $this->applyPublicToxicMask($rows, $publicMaskToxic);
    }

    /**
     * Supprime les commentaires toxiques dont le délai de grâce (1 min) est écoulé.
     * Supprime également les réponses enfants pour éviter les orphelins.
     */
    public function purgeExpiredToxic(): void
    {
        try {
            // Récupérer les IDs des commentaires toxiques expirés
            $stmt = $this->pdo->query('SELECT id FROM comments WHERE toxic_delete_at IS NOT NULL AND toxic_delete_at <= NOW()');
            $expiredIds = $stmt->fetchAll(PDO::FETCH_COLUMN, 0);

            if (!empty($expiredIds)) {
                $placeholders = implode(',', array_fill(0, count($expiredIds), '?'));

                // Supprimer d'abord les réponses enfants
                $stmtChildren = $this->pdo->prepare("DELETE FROM comments WHERE parent_id IN ($placeholders)");
                $stmtChildren->execute($expiredIds);

                // Puis supprimer les commentaires toxiques eux-mêmes
                $stmtParents = $this->pdo->prepare("DELETE FROM comments WHERE id IN ($placeholders)");
                $stmtParents->execute($expiredIds);
            }
        } catch (PDOException $e) {
            // Colonnes absentes tant que createTables n'a pas tourné
        }
    }

    /**
     * Front : texte remplacé par des étoiles pour tout commentaire marqué toxique.
     * Pendant le délai de grâce (1 min), le texte est déjà masqué.
     * Après expiration, toxic_delete_at est mis à NULL et le masquage reste permanent.
     *
     * @param array<int, array<string, mixed>> $rows
     * @return array<int, array<string, mixed>>
     */
    private function applyPublicToxicMask(array $rows, bool $publicMaskToxic): array
    {
        if (!$publicMaskToxic || $rows === []) {
            return $rows;
        }
        foreach ($rows as &$row) {
            if (empty($row['toxic_flag'])) {
                continue;
            }
            // Masquer tout commentaire toxique (en attente ou déjà expiré)
            $row['comment'] = '★★★★★';
            $row['toxic_masked'] = true;
        }
        unset($row);

        return $rows;
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
        $statusHandled = false;

        if (isset($data['username'])) { $fields[] = 'username = ?'; $params[] = $data['username']; }
        if (isset($data['comment'])) {
            $fields[] = 'comment = ?';
            $params[] = $data['comment'];
            $toxic = CommentModeration::isToxic($data['comment']);
            if ($toxic) {
                $fields[] = 'toxic_flag = ?';
                $params[] = 1;
                $fields[] = 'toxic_delete_at = DATE_ADD(NOW(), INTERVAL 1 MINUTE)';
                $fields[] = 'status = ?';
                $params[] = 1;
                $statusHandled = true;
            } else {
                $fields[] = 'toxic_flag = ?';
                $params[] = 0;
                $fields[] = 'toxic_delete_at = ?';
                $params[] = null;
            }
        }
        if (isset($data['status']) && !$statusHandled) {
            $fields[] = 'status = ?';
            $params[] = (int)$data['status'];
        }

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

    /**
     * Commentaires créés après un ID (notifications back-office / tray).
     */
    public function getNewerThan(int $lastId): array
    {
        $stmt = $this->pdo->prepare('SELECT c.*, a.name AS article_name FROM comments c LEFT JOIN articles a ON c.article_id = a.id WHERE c.id > ? ORDER BY c.id ASC');
        $stmt->execute([$lastId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
