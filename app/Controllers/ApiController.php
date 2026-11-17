<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Request;
use App\Core\Auth;
use App\Repositories\DashboardRepository;
use App\Repositories\ChamadoRepository;

class ApiController
{
    public function indicadores(Request $request): string
    {
        if (!in_array(Auth::user()->perfil, ['tecnico', 'admin', 'painel'])) {
            http_response_code(403);
            return json_encode(['erro' => 'Acesso negado']);
        }

        $repo = new DashboardRepository();
        
        $dados = [
            'gerais' => $repo->getIndicadoresGerais(),
            'tabela_categorias' => $repo->getTabelaCategorias(),
            'tabela_prioridades' => $repo->getTabelaPrioridades(),
            'tecnicos' => $repo->getDesempenhoTecnicos(),
            'status_totais' => $repo->getStatusTotais(),
            'sla_prazos' => $repo->getSlaPrazos(),
            'equipe' => $repo->getEquipe(),
            'evolucao_mensal' => $repo->getEvolucaoMensal()
        ];

        header('Content-Type: application/json; charset=utf-8');
        return json_encode($dados);
    }

    public function chamadosStatus(Request $request): string
    {
        if (!in_array(Auth::user()->perfil, ['tecnico', 'admin', 'painel'])) {
            http_response_code(403);
            return json_encode(['erro' => 'Acesso negado']);
        }

        $repo = new ChamadoRepository();
        $dados = $repo->getDadosNotificacao();

        header('Content-Type: application/json; charset=utf-8');
        return json_encode($dados);
    }
}
