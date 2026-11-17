<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\Equipamento;

class EquipamentoRepository extends AbstractRepository
{
    protected string $table = 'equipamentos';
    protected string $entityClass = Equipamento::class;

    protected array $fillable = ['nome', 'ativo'];

    public function findAtivos(): array
    {
        $stmt = $this->db->query("SELECT * FROM {$this->table} WHERE ativo = 1 ORDER BY nome ASC");
        $equipamentos = [];
        foreach ($stmt->fetchAll() as $row) {
            $equipamentos[] = (new Equipamento())->fromArray($row);
        }
        return $equipamentos;
    }

    public function findByNome(string $nome): ?Equipamento
    {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE nome = :nome LIMIT 1");
        $stmt->execute(['nome' => $nome]);
        $row = $stmt->fetch();
        return $row ? (new Equipamento())->fromArray($row) : null;
    }
}
