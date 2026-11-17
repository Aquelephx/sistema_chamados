<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\Secretaria;

class SecretariaRepository extends AbstractRepository
{
    protected string $table = 'secretarias';
    protected string $entityClass = Secretaria::class;

    protected array $fillable = ['nome', 'ativo'];

    public function findAtivas(): array
    {
        $sql = "SELECT * FROM {$this->table} WHERE ativo = 1 ORDER BY nome ASC";
        $stmt = $this->db->query($sql);
        $rows = $stmt->fetchAll();

        $secretarias = [];
        foreach ($rows as $row) {
            $secretarias[] = (new Secretaria())->fromArray($row);
        }
        return $secretarias;
    }

    public function findByNome(string $nome): ?Secretaria
    {
        $sql = "SELECT * FROM {$this->table} WHERE nome = :nome LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['nome' => $nome]);
        $row = $stmt->fetch();
        return $row ? (new Secretaria())->fromArray($row) : null;
    }
}
