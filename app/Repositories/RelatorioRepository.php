<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\Relatorio;

class RelatorioRepository extends AbstractRepository
{
    protected string $table = 'relatorios';
    protected string $entityClass = Relatorio::class;

    protected array $fillable = [
        'chamado_id', 'usuario_id', 'localizacao_id', 'tipo', 
        'equipamento', 'tempo_min', 'solucao'
    ];

    public function findByChamadoId(int $chamadoId): ?Relatorio
    {
        $sql = "SELECT r.*, u.nome AS tecnico
                FROM {$this->table} r
                JOIN usuarios u ON r.usuario_id = u.id
                WHERE r.chamado_id = :chamado_id LIMIT 1";
                
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['chamado_id' => $chamadoId]);
        $row = $stmt->fetch();

        if (!$row) return null;
        return (new Relatorio())->fromArray($row);
    }

    public function buscarComFiltros(string $dataInicio = '', string $dataFim = '', string $tipo = '', int $tecnicoId = 0): array
    {
        $sql = "SELECT r.*, 
                       c.titulo AS chamado_titulo, 
                       c.descricao AS chamado_descricao,
                       c.status AS chamado_status,
                       c.prioridade AS chamado_prioridade,
                       c.criado_em AS chamado_criado_em, 
                       c.fechado_em AS chamado_fechado_em,
                       loc.nome AS localizacao_nome,
                       u.nome AS tecnico_nome, 
                       sol.nome AS solicitante_nome,
                       s.prazo_horas,
                       CASE 
                           WHEN COALESCE(c.fechado_em, c.atualizado_em) <= DATE_ADD(c.criado_em, INTERVAL s.prazo_horas HOUR) THEN 'Sim'
                           ELSE 'Não'
                       END AS sla_cumprido
                FROM relatorios r
                JOIN chamados c ON r.chamado_id = c.id
                JOIN usuarios u ON r.usuario_id = u.id
                JOIN usuarios sol ON c.solicitante_id = sol.id
                LEFT JOIN localizacoes loc ON c.localizacao_id = loc.id
                JOIN sla_prazos s ON c.prioridade = s.prioridade
                WHERE 1=1";
        $params = [];

        if ($dataInicio) {
            $sql .= " AND DATE(r.criado_em) >= :data_inicio";
            $params['data_inicio'] = $dataInicio;
        }
        if ($dataFim) {
            $sql .= " AND DATE(r.criado_em) <= :data_fim";
            $params['data_fim'] = $dataFim;
        }
        if ($tipo) {
            $sql .= " AND r.tipo = :tipo";
            $params['tipo'] = $tipo;
        }
        if ($tecnicoId > 0) {
            $sql .= " AND r.usuario_id = :tecnico_id";
            $params['tecnico_id'] = $tecnicoId;
        }

        $sql .= " ORDER BY r.criado_em DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
}
