<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\Chamado;
use App\Core\Model;

class ChamadoRepository extends AbstractRepository
{
    protected string $table = 'chamados';
    protected string $entityClass = Chamado::class;

    protected array $fillable = [
        'titulo', 'descricao', 'prioridade', 'categoria', 'status', 
        'solicitante_id', 'responsavel_id', 'localizacao_id', 'secretaria_id', 
        'assumido_em', 'fechado_em', 'sla_pausado_em', 'tempo_pausado_minutos', 'motivo_pausa',    'avaliacao',  
    'comentario_avaliacao'
    ];

    public function find(int $id): ?Model
    {
        $sql = "SELECT c.*, 
                       sol.nome AS solicitante_nome, 
                       loc.nome AS localizacao_nome,
                       sec.nome AS secretaria_nome,
                       sla.prazo_horas,
                       GROUP_CONCAT(DISTINCT u.id) AS responsaveis_ids,
                       GROUP_CONCAT(DISTINCT u.nome SEPARATOR ', ') AS responsaveis_nomes
                FROM {$this->table} c
                JOIN usuarios sol ON c.solicitante_id = sol.id
                LEFT JOIN localizacoes loc ON c.localizacao_id = loc.id
                LEFT JOIN secretarias sec ON c.secretaria_id = sec.id
                JOIN sla_prazos sla ON c.prioridade = sla.prioridade
                LEFT JOIN chamado_responsaveis cr ON c.id = cr.chamado_id
                LEFT JOIN usuarios u ON cr.usuario_id = u.id
                WHERE c.id = :id 
                GROUP BY c.id LIMIT 1";
                
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();

        if (!$row) return null;
        return (new Chamado())->fromArray($row);
    }

    public function countSearch(array $filtros, int $usuarioId, string $perfil): int
    {
        $sql = "SELECT COUNT(c.id) FROM {$this->table} c WHERE 1=1";
        $params = [];

        if ($perfil === 'solicitante') {
            $sql .= " AND c.solicitante_id = :usuario_id";
            $params['usuario_id'] = $usuarioId;
        } elseif (!empty($filtros['apenas_meus'])) {
            $sql .= " AND c.id IN (SELECT chamado_id FROM chamado_responsaveis WHERE usuario_id = :usuario_id)";
            $params['usuario_id'] = $usuarioId;
        }

        if (!empty($filtros['busca'])) {
            $sql .= " AND (c.titulo LIKE :busca_titulo OR c.descricao LIKE :busca_descricao)";
            $params['busca_titulo'] = '%' . $filtros['busca'] . '%';
            $params['busca_descricao'] = '%' . $filtros['busca'] . '%';
        }
        if (!empty($filtros['status'])) {
            $sql .= " AND c.status = :status";
            $params['status'] = $filtros['status'];
        }
        if (!empty($filtros['prioridade'])) {
            $sql .= " AND c.prioridade = :prioridade";
            $params['prioridade'] = $filtros['prioridade'];
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return (int) $stmt->fetchColumn();
    }

    public function search(array $filtros, int $usuarioId, string $perfil, int $pagina = 1, int $porPagina = 10): array
    {
        $offset = ($pagina - 1) * $porPagina;

        $sql = "SELECT c.*, 
                       sol.nome AS solicitante_nome, 
                       loc.nome AS localizacao_nome,
                       sec.nome AS secretaria_nome,
                       sla.prazo_horas,
                       GROUP_CONCAT(DISTINCT u.id) AS responsaveis_ids,
                       GROUP_CONCAT(DISTINCT u.nome SEPARATOR ', ') AS responsaveis_nomes
                FROM {$this->table} c
                JOIN usuarios sol ON c.solicitante_id = sol.id
                LEFT JOIN localizacoes loc ON c.localizacao_id = loc.id
                LEFT JOIN secretarias sec ON c.secretaria_id = sec.id
                JOIN sla_prazos sla ON c.prioridade = sla.prioridade
                LEFT JOIN chamado_responsaveis cr ON c.id = cr.chamado_id
                LEFT JOIN usuarios u ON cr.usuario_id = u.id
                WHERE 1=1";

        $params = [];

        if ($perfil === 'solicitante') {
            $sql .= " AND c.solicitante_id = :usuario_id";
            $params['usuario_id'] = $usuarioId;
        } elseif (!empty($filtros['apenas_meus'])) {
            $sql .= " AND c.id IN (SELECT chamado_id FROM chamado_responsaveis WHERE usuario_id = :usuario_id)";
            $params['usuario_id'] = $usuarioId;
        }

        if (!empty($filtros['busca'])) {
            $sql .= " AND (c.titulo LIKE :busca_titulo OR c.descricao LIKE :busca_descricao)";
            $params['busca_titulo'] = '%' . $filtros['busca'] . '%';
            $params['busca_descricao'] = '%' . $filtros['busca'] . '%';
        }
        if (!empty($filtros['status'])) {
            $sql .= " AND c.status = :status";
            $params['status'] = $filtros['status'];
        }
        if (!empty($filtros['prioridade'])) {
            $sql .= " AND c.prioridade = :prioridade";
            $params['prioridade'] = $filtros['prioridade'];
        }

        $sql .= " GROUP BY c.id ORDER BY c.criado_em DESC LIMIT {$porPagina} OFFSET {$offset}";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        
        $rows = $stmt->fetchAll();
        $chamados = [];

        foreach ($rows as $row) {
            $chamados[] = (new Chamado())->fromArray($row);
        }

        return $chamados;
    }

    public function getResumoParaTecnico(int $tecnicoId, string $perfil = 'tecnico'): array
    {
        $resumo = [];

        $resumo['aguardando'] = (int) $this->db->query("
            SELECT COUNT(*) FROM chamados WHERE status = 'aberto' AND responsavel_id IS NULL
        ")->fetchColumn();

        if ($perfil === 'admin') {
            $resumo['meus_atendimento'] = (int) $this->db->query("
                SELECT COUNT(*) FROM chamados WHERE status = 'em_andamento'
            ")->fetchColumn();
        } else {
            $stmt = $this->db->prepare("
                SELECT COUNT(*) FROM chamados c
                JOIN chamado_responsaveis cr ON c.id = cr.chamado_id
                WHERE c.status = 'em_andamento' AND cr.usuario_id = :id
            ");
            $stmt->execute(['id' => $tecnicoId]);
            $resumo['meus_atendimento'] = (int) $stmt->fetchColumn();
        }

        $resumo['vencidos'] = (int) $this->db->query("
            SELECT COUNT(*) FROM chamados c
            JOIN sla_prazos s ON c.prioridade = s.prioridade
            WHERE c.status NOT IN ('resolvido', 'fechado', 'aguardando')
            AND DATE_ADD(DATE_ADD(c.criado_em, INTERVAL s.prazo_horas HOUR), INTERVAL c.tempo_pausado_minutos MINUTE) < NOW()
        ")->fetchColumn();

        if ($perfil === 'admin') {
            $resumo['encerrados_semana'] = (int) $this->db->query("
                SELECT COUNT(*) FROM chamados 
                WHERE status IN ('resolvido', 'fechado') 
                AND fechado_em >= DATE_SUB(NOW(), INTERVAL 7 DAY)
            ")->fetchColumn();
        } else {
            $stmt = $this->db->prepare("
                SELECT COUNT(*) FROM chamados c
                JOIN chamado_responsaveis cr ON c.id = cr.chamado_id
                WHERE c.status IN ('resolvido', 'fechado') 
                AND cr.usuario_id = :id
                AND c.fechado_em >= DATE_SUB(NOW(), INTERVAL 7 DAY)
            ");
            $stmt->execute(['id' => $tecnicoId]);
            $resumo['encerrados_semana'] = (int) $stmt->fetchColumn();
        }

        return $resumo;
    }

    public function getDadosNotificacao(): array
    {
        $ultimoId = (int) $this->db->query("SELECT COALESCE(MAX(id), 0) FROM {$this->table}")->fetchColumn();
        $stmt = $this->db->query("SELECT id, status FROM {$this->table} WHERE status = 'aberto'");
        return ['ultimo_id' => $ultimoId, 'chamados' => $stmt->fetchAll()];
    }
}
