<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/Reclamation.php';

class ReclamationController
{
    private PDO $conn;
    private string $table = 'reclamation';

    public function __construct()
    {
        $database = new Database();
        $this->conn = $database->connect();
    }

    public function getAll(): array
    {
        $sql = "SELECT * FROM {$this->table} ORDER BY date_creation DESC, id DESC";
        $stmt = $this->conn->query($sql);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return array_map(static function (array $row): Reclamation {
            return Reclamation::fromArray($row);
        }, $rows);
    }

    public function getByEmail(string $email): array
    {
        $sql = "SELECT * FROM {$this->table} WHERE email = :email ORDER BY date_creation DESC, id DESC";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute(['email' => $email]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return array_map(static function (array $row): Reclamation {
            return Reclamation::fromArray($row);
        }, $rows);
    }

    public function getById(int $id): ?Reclamation
    {
        $sql = "SELECT * FROM {$this->table} WHERE id = :id LIMIT 1";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$row) {
            return null;
        }

        return Reclamation::fromArray($row);
    }

    public function create(Reclamation $reclamation): int
    {
        $sql = "INSERT INTO {$this->table} (nom_client, email, sujet, message, date_creation, priorite, statut)
                VALUES (:nom_client, :email, :sujet, :message, :date_creation, :priorite, :statut)";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute([
            'nom_client' => $reclamation->getNomClient(),
            'email' => $reclamation->getEmail(),
            'sujet' => $reclamation->getSujet(),
            'message' => $reclamation->getMessage(),
            'date_creation' => $reclamation->getDateCreation() ?? date('Y-m-d'),
            'priorite' => $reclamation->getPriorite() ?? 'Faible',
            'statut' => $reclamation->getStatut() ?? 'En attente',
        ]);

        $id = (int) $this->conn->lastInsertId();
        $reclamation->setId($id);

        return $id;
    }

    public function update(Reclamation $reclamation): bool
    {
        if ($reclamation->getId() === null) {
            throw new InvalidArgumentException('Reclamation id is required for update.');
        }

        $sql = "UPDATE {$this->table}
                SET nom_client = :nom_client,
                    email = :email,
                    sujet = :sujet,
                    message = :message,
                    date_creation = :date_creation,
                    priorite = :priorite,
                    statut = :statut
                WHERE id = :id";

        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([
            'id' => $reclamation->getId(),
            'nom_client' => $reclamation->getNomClient(),
            'email' => $reclamation->getEmail(),
            'sujet' => $reclamation->getSujet(),
            'message' => $reclamation->getMessage(),
            'date_creation' => $reclamation->getDateCreation() ?? date('Y-m-d'),
            'priorite' => $reclamation->getPriorite() ?? 'Faible',
            'statut' => $reclamation->getStatut() ?? 'En attente',
        ]);
    }

    public function delete(int $id): bool
    {
        $sql = "DELETE FROM {$this->table} WHERE id = :id";
        $stmt = $this->conn->prepare($sql);

        return $stmt->execute(['id' => $id]);
    }

    public function filter(array $criteres): array
    {
        $sql = "SELECT * FROM {$this->table} WHERE 1=1";
        $params = [];

        if (!empty($criteres['priorite'])) {
            $sql .= " AND priorite = :priorite";
            $params['priorite'] = $criteres['priorite'];
        }

        if (!empty($criteres['statut'])) {
            $sql .= " AND statut = :statut";
            $params['statut'] = $criteres['statut'];
        }

        if (!empty($criteres['sujet'])) {
            $sql .= " AND sujet = :sujet";
            $params['sujet'] = $criteres['sujet'];
        }

        if (!empty($criteres['date_debut'])) {
            $sql .= " AND date_creation >= :date_debut";
            $params['date_debut'] = $criteres['date_debut'];
        }

        if (!empty($criteres['date_fin'])) {
            $sql .= " AND date_creation <= :date_fin";
            $params['date_fin'] = $criteres['date_fin'];
        }

        $sortOrder = strtoupper($criteres['sort_date'] ?? 'DESC');
        if (!in_array($sortOrder, ['ASC', 'DESC'])) {
            $sortOrder = 'DESC';
        }

        $sql .= " ORDER BY date_creation {$sortOrder}, id {$sortOrder}";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute($params);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return array_map(static function (array $row): Reclamation {
            return Reclamation::fromArray($row);
        }, $rows);
    }
}
