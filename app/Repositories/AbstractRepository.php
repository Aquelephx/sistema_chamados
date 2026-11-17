<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Core\Database;
use App\Core\Model;
use PDO;

abstract class AbstractRepository
{
    protected PDO $db;
    
    protected string $table;
    protected string $entityClass;
    protected array $fillable = [];

    public function __construct()
    {
        $this->db = Database::connection();
    }

    public function find(int $id): ?Model
    {
        $sql = "SELECT * FROM {$this->table} WHERE id = :id LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();

        if (!$row) {
            return null;
        }

        return (new $this->entityClass())->fromArray($row);
    }

    public function all(): array
    {
        $sql = "SELECT * FROM {$this->table}";
        $stmt = $this->db->query($sql);
        $rows = $stmt->fetchAll();

        $entities = [];
        foreach ($rows as $row) {
            $entities[] = (new $this->entityClass())->fromArray($row);
        }

        return $entities;
    }

    public function create(array $data): int
    {
        $data = $this->filterFillable($data);

        if (empty($data)) {
            throw new \InvalidArgumentException("Nenhuma coluna válida foi enviada para criação.");
        }

        $columns = array_keys($data);
        $fields = implode(', ', $columns);
        $placeholders = ':' . implode(', :', $columns);

        $sql = "INSERT INTO {$this->table} ({$fields}) VALUES ({$placeholders})";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($data);

        return (int) $this->db->lastInsertId();
    }

    /**
     * Atualiza um registro existente no banco.
     */
    public function update(int $id, array $data): bool
    {
        if ($id <= 0) {
            throw new \InvalidArgumentException("ID inválido para atualização.");
        }

        $data = $this->filterFillable($data);

        if (empty($data)) {
            throw new \InvalidArgumentException("Nenhuma coluna válida foi enviada para atualização.");
        }

        $fields = [];
        foreach (array_keys($data) as $column) {
            $fields[] = "{$column} = :{$column}";
        }

        $set = implode(', ', $fields);

        $sql = "UPDATE {$this->table} SET {$set} WHERE id = :_id";

        $data['_id'] = $id;

        $stmt = $this->db->prepare($sql);
        
        return $stmt->execute($data);
    }

    public function delete(int $id): bool
    {
        if ($id <= 0) {
            throw new \InvalidArgumentException("ID inválido para exclusão.");
        }

        $sql = "DELETE FROM {$this->table} WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        
        return $stmt->execute(['id' => $id]);
    }

    protected function filterFillable(array $data): array
    {
        return array_intersect_key($data, array_flip($this->fillable));
    }
}
