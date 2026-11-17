<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\Comentario;

class ComentarioRepository extends AbstractRepository
{
    protected string $table = 'comentarios';
    protected string $entityClass = Comentario::class;

    protected array $fillable = [
        'chamado_id',
        'usuario_id',
        'texto'
    ];

    /**
     * Busca os comentários de um chamado, trazendo o nome de quem comentou.
     */
    public function findByChamadoId(int $chamadoId): array
    {
        $sql = "SELECT c.*, u.nome AS usuario_nome
                FROM {$this->table} c
                JOIN usuarios u ON c.usuario_id = u.id
                WHERE c.chamado_id = :id
                ORDER BY c.criado_em ASC";
                
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $chamadoId]);
        
        $rows = $stmt->fetchAll();
        $comentarios = [];

        foreach ($rows as $row) {
            $comentarios[] = (new Comentario())->fromArray($row);
        }

        return $comentarios;
    }
}
