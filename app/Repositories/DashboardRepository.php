<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Core\Database;

class DashboardRepository
{
    private \PDO $db;

    public function __construct()
    {
        $this->db = Database::connection();
    }

    public function getIndicadoresGerais(): array
    {
        $fila = (int) $this->db->query("SELECT COUNT(*) FROM chamados WHERE status = 'aberto'")->fetchColumn();
        $atendimento = (int) $this->db->query("SELECT COUNT(*) FROM chamados WHERE status = 'em_andamento'")->fetchColumn();
        $encerrados = (int) $this->db->query("SELECT COUNT(*) FROM chamados WHERE status IN ('resolvido', 'fechado')")->fetchColumn();
        $total = $fila + $atendimento + $encerrados;

        $vencidos = (int) $this->db->query("
            SELECT COUNT(*) FROM chamados c
            JOIN sla_prazos s ON c.prioridade = s.prioridade
            WHERE c.status NOT IN ('resolvido', 'fechado', 'aguardando') 
            AND DATE_ADD(DATE_ADD(c.criado_em, INTERVAL s.prazo_horas HOUR), INTERVAL c.tempo_pausado_minutos MINUTE) < NOW()
        ")->fetchColumn();

        $tempoMedio = (int) $this->db->query("SELECT COALESCE(AVG(tempo_min), 0) FROM relatorios")->fetchColumn();
        
        // NOVO: Média geral de satisfação (CSAT)
        $satisfacaoGeral = (float) $this->db->query("SELECT COALESCE(AVG(avaliacao), 0) FROM chamados WHERE avaliacao IS NOT NULL")->fetchColumn();

        $totalFechados = $encerrados > 0 ? $encerrados : 1;
        $fechadosNoPrazo = (int) $this->db->query("
            SELECT COUNT(*) FROM chamados c
            JOIN sla_prazos s ON c.prioridade = s.prioridade
            WHERE c.status IN ('resolvido', 'fechado') 
            AND COALESCE(c.fechado_em, c.atualizado_em) <= DATE_ADD(DATE_ADD(c.criado_em, INTERVAL s.prazo_horas HOUR), INTERVAL c.tempo_pausado_minutos MINUTE)
        ")->fetchColumn();
        
        $slaCumprido = round(($fechadosNoPrazo / $totalFechados) * 100);

        return [
            'fila' => $fila, 'atendimento' => $atendimento, 'encerrados' => $encerrados,
            'total' => $total, 'vencidos' => $vencidos, 'tempo_medio' => $tempoMedio,
            'sla_cumprido' => $slaCumprido, 'satisfacao_geral' => round($satisfacaoGeral, 1)
        ];
    }

    public function getTabelaCategorias(): array
    {
        $sql = "SELECT COALESCE(r.tipo, c.categoria, 'Sem Cat.') as categoria,
                       SUM(CASE WHEN c.status = 'aberto' THEN 1 ELSE 0 END) as fila,
                       SUM(CASE WHEN c.status = 'em_andamento' THEN 1 ELSE 0 END) as atendimento,
                       SUM(CASE WHEN c.status IN ('resolvido', 'fechado') THEN 1 ELSE 0 END) as encerrados
                FROM chamados c
                LEFT JOIN relatorios r ON c.id = r.chamado_id
                GROUP BY COALESCE(r.tipo, c.categoria, 'Sem Cat.')
                ORDER BY encerrados DESC, fila DESC";
        return $this->db->query($sql)->fetchAll();
    }

    public function getTabelaPrioridades(): array
    {
        $sql = "SELECT s.prioridade,
                       SUM(CASE WHEN c.status = 'aberto' THEN 1 ELSE 0 END) as fila,
                       SUM(CASE WHEN c.status = 'em_andamento' THEN 1 ELSE 0 END) as atendimento,
                       SUM(CASE WHEN c.status IN ('resolvido', 'fechado') THEN 1 ELSE 0 END) as encerrados
                FROM sla_prazos s
                LEFT JOIN chamados c ON s.prioridade = c.prioridade
                GROUP BY s.prioridade
                ORDER BY FIELD(s.prioridade, 'urgente', 'alta', 'media', 'baixa')";
        return $this->db->query($sql)->fetchAll();
    }

    public function getDesempenhoTecnicos(): array
    {
        // NOVO: Adicionado o cálculo de satisfação média por técnico
        $sql = "SELECT u.nome, 
                       COUNT(DISTINCT c.id) as total,
                       SUM(CASE WHEN c.status = 'aberto' THEN 1 ELSE 0 END) as abertos,
                       SUM(CASE WHEN c.status = 'em_andamento' THEN 1 ELSE 0 END) as fila,
                       COALESCE(AVG(CASE WHEN c.status IN ('resolvido', 'fechado') THEN r.tempo_min END), 0) as tempo_medio,
                       SUM(CASE WHEN c.status IN ('resolvido', 'fechado') THEN 1 ELSE 0 END) as encerrados,
                       COALESCE(AVG(CASE WHEN c.avaliacao IS NOT NULL THEN c.avaliacao END), 0) as satisfacao
                FROM usuarios u
                JOIN chamado_responsaveis cr ON u.id = cr.usuario_id
                JOIN chamados c ON cr.chamado_id = c.id
                LEFT JOIN relatorios r ON c.id = r.chamado_id
                WHERE u.perfil IN ('tecnico', 'admin') AND u.ativo = 1
                GROUP BY u.id
                ORDER BY encerrados DESC";
        return $this->db->query($sql)->fetchAll();
    }

    public function getStatusTotais(): array
    {
        $sql = "SELECT status, COUNT(*) as qtd FROM chamados GROUP BY status";
        return $this->db->query($sql)->fetchAll();
    }

    public function getSlaPrazos(): array
    {
        return $this->db->query("SELECT prioridade, prazo_horas FROM sla_prazos ORDER BY FIELD(prioridade, 'urgente', 'alta', 'media', 'baixa')")->fetchAll();
    }

    public function getEquipe(): array
    {
        $sql = "SELECT nome FROM usuarios WHERE perfil IN ('tecnico', 'admin') AND ativo = 1 ORDER BY nome";
        return $this->db->query($sql)->fetchAll(\PDO::FETCH_COLUMN);
    }
  public function getEvolucaoMensal(): array
{
    $sql = "SELECT 
                DATE_FORMAT(criado_em, '%Y-%m') as mes,
                COUNT(*) as abertos,
                0 as fechados
            FROM chamados 
            WHERE criado_em >= DATE_SUB(NOW(), INTERVAL 6 MONTH)
            GROUP BY DATE_FORMAT(criado_em, '%Y-%m')
            
            UNION ALL
            
            SELECT 
                DATE_FORMAT(fechado_em, '%Y-%m') as mes,
                0 as abertos,
                COUNT(*) as fechados
            FROM chamados 
            WHERE fechado_em >= DATE_SUB(NOW(), INTERVAL 6 MONTH) AND fechado_em IS NOT NULL
            GROUP BY DATE_FORMAT(fechado_em, '%Y-%m')";
            
    $stmt = $this->db->query($sql);
    $rows = $stmt->fetchAll();

    // Agrupa os dados por mês para somar abertos e fechados no mesmo mês
    $evolucao = [];
    foreach ($rows as $row) {
        $mes = $row['mes'];
        if (!isset($evolucao[$mes])) {
            $evolucao[$mes] = ['mes' => $mes, 'abertos' => 0, 'fechados' => 0];
        }
        $evolucao[$mes]['abertos'] += (int) $row['abertos'];
        $evolucao[$mes]['fechados'] += (int) $row['fechados'];
    }

    // Ordena por mês
    ksort($evolucao);
    
    // Formata o nome do mês para exibir no gráfico (Ex: "Out/23")
    $mesesPt = ['01'=>'Jan','02'=>'Fev','03'=>'Mar','04'=>'Abr','05'=>'Mai','06'=>'Jun','07'=>'Jul','08'=>'Ago','09'=>'Set','10'=>'Out','11'=>'Nov','12'=>'Dez'];
    $resultado = [];
    foreach ($evolucao as $item) {
        $ano = substr($item['mes'], 2, 2);
        $mes = substr($item['mes'], 5, 2);
        $resultado[] = [
            'label' => $mesesPt[$mes] . '/' . $ano,
            'abertos' => $item['abertos'],
            'fechados' => $item['fechados']
        ];
    }

    return $resultado;
}
}
