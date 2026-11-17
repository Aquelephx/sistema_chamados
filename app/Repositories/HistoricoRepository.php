<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\Historico;

class HistoricoRepository extends AbstractRepository
{
    protected string $table = 'historico';
    protected string $entityClass = Historico::class;

    protected array $fillable = [
        'chamado_id',
        'usuario_id',
        'texto'
    ];

    public function findByChamadoId(int $chamadoId): array
    {
        $sql = "SELECT h.*, u.nome AS usuario_nome
                FROM {$this->table} h
                LEFT JOIN usuarios u ON h.usuario_id = u.id
                WHERE h.chamado_id = :id
                ORDER BY h.criado_em ASC";
                
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $chamadoId]);
        
        $rows = $stmt->fetchAll();
        $historicos = [];

        foreach ($rows as $row) {
            $historicos[] = (new Historico())->fromArray($row);
        }

        return $historicos;
    }
}
