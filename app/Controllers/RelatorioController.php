<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Request;
use App\Core\View;
use App\Core\Auth;
use App\Core\Url;
use App\Repositories\RelatorioRepository;
use App\Repositories\UsuarioRepository;
use Shuchkin\SimpleXLSXGen;

class RelatorioController
{
    public function index(Request $request): string
    {
        if (!in_array(Auth::user()->perfil, ['tecnico', 'admin'])) {
            Url::redirect('/chamados'); exit;
        }

        $filtros = [
            'data_inicio' => $request->query('data_inicio', ''),
            'data_fim' => $request->query('data_fim', ''),
            'tipo' => $request->query('tipo', ''),
            'tecnico_id' => (int) $request->query('tecnico_id', 0)
        ];

        $repo = new RelatorioRepository();
        $relatorios = $repo->buscarComFiltros(
            $filtros['data_inicio'], 
            $filtros['data_fim'], 
            $filtros['tipo'], 
            $filtros['tecnico_id']
        );

        $userRepo = new UsuarioRepository();
        $tecnicos = array_filter($userRepo->all(), fn($u) => in_array($u->perfil, ['tecnico', 'admin']));

        $listas = require dirname(__DIR__, 2) . '/config/listas.php';

        return View::render('relatorios.index', [
            'relatorios' => $relatorios,
            'filtros' => $filtros,
            'tecnicos' => $tecnicos,
            'tipos' => $listas['tipos_chamado']
        ], 'layouts.main');
    }

    public function exportar(Request $request): void
    {
        if (!in_array(Auth::user()->perfil, ['tecnico', 'admin'])) {
            Url::redirect('/chamados'); exit;
        }

        $dataInicio = $request->query('data_inicio', '');
        $dataFim = $request->query('data_fim', '');
        $tipo = $request->query('tipo', '');
        $tecnicoId = (int) $request->query('tecnico_id', 0);

        $repo = new RelatorioRepository();
        $relatorios = $repo->buscarComFiltros($dataInicio, $dataFim, $tipo, $tecnicoId);

        $dados = [];
        
        // Linha 1: Título (Mesclado e estilizado)
        $dados[] = ['<style bgcolor="#1D4A8F" color="#FFFFFF" font-size="16" font-style="bold" border="1">SISTEMA DE CHAMADOS - CGTI/SEAD</style>', '', '', '', '', '', '', '', '', '', '', '', ''];
        
        // Linha 2: Subtítulo
        $dados[] = ['<style color="#555555" font-style="italic" border="1">Relatório gerado em: ' . date('d/m/Y \à\s H:i') . '</style>', '', '', '', '', '', '', '', '', '', '', '', ''];
        
        // Linha 3: Em branco
        $dados[] = ['', '', '', '', '', '', '', '', '', '', '', '', ''];

        // Linha 4: Cabeçalho (Fundo escuro, letra branca, negrito)
        $estiloHeader = '<style bgcolor="#0F172A" color="#FFFFFF" font-style="bold" border="1">';
        $dados[] = [
            $estiloHeader . 'Nº Chamado</style>',
            $estiloHeader . 'Data/Hora Abertura</style>',
            $estiloHeader . 'Solicitante</style>',
            $estiloHeader . 'Departamento</style>',
            $estiloHeader . 'Categoria</style>',
            $estiloHeader . 'Prioridade</style>',
            $estiloHeader . 'Descrição do Problema</style>',
            $estiloHeader . 'Prazo SLA (Limite)</style>',
            $estiloHeader . 'Data Fechamento</style>',
            $estiloHeader . 'Tempo Total</style>',
            $estiloHeader . 'SLA Cumprido</style>',
            $estiloHeader . 'Equipamento</style>',
            $estiloHeader . 'Solução Aplicada</style>'
        ];

        // Dados
        foreach ($relatorios as $r) {
            $dataAbertura = strtotime($r['chamado_criado_em']);
            $prazoSegundos = $r['prazo_horas'] * 3600;
            $dataLimite = date('d/m/Y H:i', $dataAbertura + $prazoSegundos);

            $tempoMin = (int) $r['tempo_min'];
            $horas = floor($tempoMin / 60);
            $minutos = $tempoMin % 60;
            $tempoFormatado = ($horas > 0 ? "{$horas}h " : "") . "{$minutos}min";
            if ($horas === 0 && $minutos === 0) $tempoFormatado = '0min';

            // Estilo condicional para o SLA
            $estiloSla = $r['sla_cumprido'] === 'Sim' 
                ? '<style color="#059669" font-style="bold" border="1">' . $r['sla_cumprido'] . '</style>' 
                : '<style color="#DC2626" font-style="bold" border="1">' . $r['sla_cumprido'] . '</style>';

            // Previne injeção de fórmula
            $neutralizar = function($texto) {
                $texto = (string) $texto;
                return ($texto !== '' && strpos("=+-@\t\r", $texto[0]) !== false) ? "'" . $texto : $texto;
            };

            $estiloNormal = '<style border="1" vertical-align="top" wrap-text="1">';
            
            $dados[] = [
                '<style border="1">#' . $r['chamado_id'] . '</style>',
                '<style border="1">' . date('d/m/Y H:i', $dataAbertura) . '</style>',
                $estiloNormal . htmlspecialchars($neutralizar($r['solicitante_nome'])) . '</style>',
                $estiloNormal . htmlspecialchars($neutralizar($r['localizacao_nome'] ?? 'Não informado')) . '</style>',
                $estiloNormal . htmlspecialchars($neutralizar($r['tipo'])) . '</style>',
                '<style border="1">' . ucfirst($neutralizar($r['chamado_prioridade'])) . '</style>',
                $estiloNormal . htmlspecialchars($neutralizar($r['chamado_descricao'])) . '</style>',
                '<style border="1">' . $dataLimite . '</style>',
                '<style border="1">' . date('d/m/Y H:i', strtotime($r['chamado_fechado_em'] ?? $r['criado_em'])) . '</style>',
                '<style border="1">' . $tempoFormatado . '</style>',
                $estiloSla,
                $estiloNormal . htmlspecialchars($neutralizar($r['equipamento'] ?? 'Nenhum')) . '</style>',
                $estiloNormal . htmlspecialchars($neutralizar(str_replace(["\r", "\n"], ' ', $r['solucao']))) . '</style>'
            ];
        }

        $xlsx = SimpleXLSXGen::fromArray($dados);
        
        // Mescla as duas primeiras linhas do título (A1 até M1 e A2 até M2)
        $xlsx->mergeCells('A1:M1');
        $xlsx->mergeCells('A2:M2');

        // Define a largura das colunas
        $xlsx->setColWidth(1, 12); // Nº Chamado
        $xlsx->setColWidth(2, 18); // Data Abertura
        $xlsx->setColWidth(3, 20); // Solicitante
        $xlsx->setColWidth(4, 20); // Departamento
        $xlsx->setColWidth(5, 15); // Categoria
        $xlsx->setColWidth(6, 12); // Prioridade
        $xlsx->setColWidth(7, 40); // Descrição
        $xlsx->setColWidth(8, 18); // Prazo SLA
        $xlsx->setColWidth(9, 18); // Data Fechamento
        $xlsx->setColWidth(10, 12); // Tempo
        $xlsx->setColWidth(11, 14); // SLA
        $xlsx->setColWidth(12, 15); // Equipamento
        $xlsx->setColWidth(13, 40); // Solução

        $xlsx->downloadAs('relatorio_chamados_' . date('Y-m-d') . '.xlsx');
        exit;
    }
}
