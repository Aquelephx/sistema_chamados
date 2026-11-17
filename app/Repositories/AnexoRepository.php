<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\Anexo;

class AnexoRepository extends AbstractRepository
{
    protected string $table = 'anexos';
    protected string $entityClass = Anexo::class;

    protected array $fillable = [
        'chamado_id', 'usuario_id', 'nome_original', 'mime', 'tamanho', 'conteudo'
    ];

    public function findByChamadoId(int $chamadoId): array
    {
        // Não buscamos a coluna "conteudo" na listagem para não pesar o banco (só buscamos na hora de exibir a foto)
        $sql = "SELECT a.id, a.chamado_id, a.usuario_id, a.nome_original, a.mime, a.tamanho, a.criado_em, u.nome AS usuario_nome
                FROM {$this->table} a
                JOIN usuarios u ON a.usuario_id = u.id
                WHERE a.chamado_id = :id
                ORDER BY a.criado_em ASC";
                
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $chamadoId]);
        
        $rows = $stmt->fetchAll();
        $anexos = [];

        foreach ($rows as $row) {
            $anexos[] = (new Anexo())->fromArray($row);
        }

        return $anexos;
    }

    public function contarPorChamado(int $chamadoId): int
    {
        $sql = "SELECT COUNT(*) FROM {$this->table} WHERE chamado_id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $chamadoId]);
        return (int) $stmt->fetchColumn();
    }
}
