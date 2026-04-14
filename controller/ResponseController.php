<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/Response.php';

class ResponseController
{
    private PDO $conn;
    private string $table = 'response';

    public function __construct()
    {
        $database = new Database();
        $this->conn = $database->connect();
    }

    public function getAll(): array
    {
        $sql = "SELECT * FROM {$this->table} ORDER BY date_reponse DESC, id DESC";
        $stmt = $this->conn->query($sql);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return array_map(static function (array $row): Response {
            return Response::fromArray($row);
        }, $rows);
    }

    public function getById(int $id): ?Response
    {
        $sql = "SELECT * FROM {$this->table} WHERE id = :id LIMIT 1";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$row) {
            return null;
        }

        return Response::fromArray($row);
    }

    public function getByReclamationId(int $idReclamation): array
    {
        $sql = "SELECT * FROM {$this->table} WHERE id_reclamation = :id_reclamation ORDER BY date_reponse DESC, id DESC";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute(['id_reclamation' => $idReclamation]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return array_map(static function (array $row): Response {
            return Response::fromArray($row);
        }, $rows);
    }

    public function getLatestByReclamationId(int $idReclamation): ?Response
    {
        $sql = "SELECT * FROM {$this->table} WHERE id_reclamation = :id_reclamation ORDER BY date_reponse DESC, id DESC LIMIT 1";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute(['id_reclamation' => $idReclamation]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$row) {
            return null;
        }

        return Response::fromArray($row);
    }

    public function create(Response $response): int
    {
        if ($response->getIdReclamation() === null) {
            throw new InvalidArgumentException('id_reclamation is required for response creation.');
        }

        $sql = "INSERT INTO {$this->table} (id_reclamation, reponse, date_reponse)
                VALUES (:id_reclamation, :reponse, :date_reponse)";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute([
            'id_reclamation' => $response->getIdReclamation(),
            'reponse' => $response->getReponse() ?? '',
            'date_reponse' => $response->getDateReponse() ?? date('Y-m-d'),
        ]);

        $id = (int) $this->conn->lastInsertId();
        $response->setId($id);

        return $id;
    }

    public function update(Response $response): bool
    {
        if ($response->getId() === null) {
            throw new InvalidArgumentException('Response id is required for update.');
        }

        if ($response->getIdReclamation() === null) {
            throw new InvalidArgumentException('id_reclamation is required for update.');
        }

        $sql = "UPDATE {$this->table}
                SET id_reclamation = :id_reclamation,
                    reponse = :reponse,
                    date_reponse = :date_reponse
                WHERE id = :id";

        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([
            'id' => $response->getId(),
            'id_reclamation' => $response->getIdReclamation(),
            'reponse' => $response->getReponse() ?? '',
            'date_reponse' => $response->getDateReponse() ?? date('Y-m-d'),
        ]);
    }

    public function delete(int $id): bool
    {
        $sql = "DELETE FROM {$this->table} WHERE id = :id";
        $stmt = $this->conn->prepare($sql);

        return $stmt->execute(['id' => $id]);
    }
}
