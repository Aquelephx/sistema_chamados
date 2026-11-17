<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Request;
use App\Core\View;
use App\Core\Auth;
use App\Core\Url;
use App\Core\Csrf;
use App\Core\Session;
use App\Core\Database;
use App\Services\ChamadoService;
use App\Services\AnexoService;
use App\Repositories\ChamadoRepository;
use App\Repositories\ComentarioRepository;
use App\Repositories\HistoricoRepository;
use App\Repositories\UsuarioRepository;
use App\Repositories\RelatorioRepository;
use App\Repositories\LocalizacaoRepository;
use App\Repositories\AnexoRepository;
use App\Repositories\EquipamentoRepository;
use App\Repositories\SecretariaRepository;

class ChamadoController
{
    public function index(Request $request): string
    {
        if (Auth::user()->perfil === 'painel') { Url::redirect('/painel'); exit; }

        $repo = new ChamadoRepository();
        $usuario = Auth::user();
        
        $filtros = [
            'busca' => $request->query('busca', ''),
            'status' => $request->query('status', ''),
            'prioridade' => $request->query('prioridade', ''),
            'apenas_meus' => $request->query('apenas_meus', '')
        ];

        $pagina = max(1, (int) $request->query('page', 1));
        $porPagina = 10;
        
        $total = $repo->countSearch($filtros, $usuario->id, $usuario->perfil);
        $totalPaginas = max(1, (int) ceil($total / $porPagina));
        if ($pagina > $totalPaginas) $pagina = $totalPaginas;

        $chamados = $repo->search($filtros, $usuario->id, $usuario->perfil, $pagina, $porPagina);

        $resumo = null;
        if (in_array($usuario->perfil, ['tecnico', 'admin'])) {
            $resumo = $repo->getResumoParaTecnico($usuario->id, $usuario->perfil);
        }

        return View::render('chamados.index', [
            'usuario' => $usuario, 
            'chamados' => $chamados, 
            'filtros' => $filtros,
            'pagina' => $pagina, 
            'totalPaginas' => $totalPaginas, 
            'total' => $total, 
            'resumo' => $resumo
        ], 'layouts.main');
    }

    public function show(Request $request, string $id): string
{
    $chamadoId = (int) $id;
    $repo = new ChamadoRepository();
    $chamado = $repo->find($chamadoId);

    if ($chamado === null) { 
    http_response_code(404); 
    return \App\Core\View::render('errors.404', [], 'layouts.main'); 
}

    $usuario = Auth::user();
    if ($chamado->solicitante_id !== $usuario->id && $usuario->perfil === 'solicitante') {
        http_response_code(403); return "Acesso negado.";
    }

    $comentarioRepo = new ComentarioRepository();
    $comentarios = $comentarioRepo->findByChamadoId($chamadoId);

    $historicoRepo = new HistoricoRepository();
    $historicos = $historicoRepo->findByChamadoId($chamadoId);

    $anexoRepo = new AnexoRepository();
    $anexos = $anexoRepo->findByChamadoId($chamadoId);

    $relatorioRepo = new RelatorioRepository();
    $relatorio = $relatorioRepo->findByChamadoId($chamadoId);

    $userRepo = new UsuarioRepository();
    $tecnicos = array_filter($userRepo->all(), fn($u) => in_array($u->perfil, ['tecnico', 'admin']));

    // --- CÓDIGO ADICIONADO PARA O MODAL DO SOLICITANTE ---
    $solicitante = $userRepo->find($chamado->solicitante_id);
    $secRepo = new SecretariaRepository();
    $locRepo = new LocalizacaoRepository();
    
    $solicitanteSec = $solicitante && $solicitante->secretaria_id ? $secRepo->find($solicitante->secretaria_id) : null;
    $solicitanteLoc = $solicitante && $solicitante->localizacao_id ? $locRepo->find($solicitante->localizacao_id) : null;
    // ------------------------------------------------------

    $voltarParam = $request->query('voltar', 'chamados');
    $origem = in_array($voltarParam, ['relatorios', 'chamados']) ? $voltarParam : 'chamados';

    return View::render('chamados.show', [
        'usuario' => $usuario, 'chamado' => $chamado, 'comentarios' => $comentarios,
        'historicos' => $historicos, 'anexos' => $anexos, 'relatorio' => $relatorio,
        'tecnicos' => $tecnicos, 'origem' => $origem,
        'solicitante' => $solicitante,           // <-- ADICIONADO
        'solicitanteSec' => $solicitanteSec,     // <-- ADICIONADO
        'solicitanteLoc' => $solicitanteLoc      // <-- ADICIONADO
    ], 'layouts.main');
}

    public function avaliar(Request $request, string $id): void
    {
        $chamadoId = (int) $id;
        $usuario = Auth::user();

        if (!Csrf::validate($request->input('_token'))) { 
            Url::redirect('/chamados/' . $chamadoId); exit; 
        }

        $repo = new ChamadoRepository();
        $chamado = $repo->find($chamadoId);

        // Apenas o próprio solicitante pode avaliar
        if ($chamado->solicitante_id !== $usuario->id) { 
            Url::redirect('/chamados/' . $chamadoId); exit; 
        }

        // Só pode avaliar se estiver fechado/resolvido e se ainda não avaliou
        if (!in_array($chamado->status, ['resolvido', 'fechado']) || $chamado->avaliacao !== null) {
            Url::redirect('/chamados/' . $chamadoId); exit;
        }

        $nota = (int) $request->input('avaliacao', 0);
        $comentario = trim($request->input('comentario_avaliacao', ''));

        if ($nota < 1 || $nota > 5) {
            Session::flash('erro', 'Nota inválida.');
            Url::redirect('/chamados/' . $chamadoId); exit;
        }

        $repo->update($chamadoId, [
            'avaliacao' => $nota,
            'comentario_avaliacao' => $comentario ?: null
        ]);

        $historicoRepo = new HistoricoRepository();
        $historicoRepo->create([
            'chamado_id' => $chamadoId, 
            'usuario_id' => $usuario->id, 
            'texto' => "O solicitante avaliou o atendimento com {$nota} estrela(s)."
        ]);

        Session::flash('sucesso', 'Obrigado pela sua avaliação!');
        Url::redirect('/chamados/' . $chamadoId); exit;
    }

    public function comentar(Request $request, string $id): void
    {
        $chamadoId = (int) $id;
        if (!Csrf::validate($request->input('_token'))) { Url::redirect('/chamados/' . $chamadoId); exit; }

        $repo = new ChamadoRepository();
        $chamado = $repo->find($chamadoId);
        $usuario = Auth::user();

        if (!$chamado || ($chamado->solicitante_id !== $usuario->id && $usuario->perfil === 'solicitante')) {
            http_response_code(403); exit("Acesso negado.");
        }

        if ($chamado->status === 'fechado') {
            Session::flash('erro', 'Chamado fechado: reabra para comentar.');
            Url::redirect('/chamados/' . $chamadoId); exit;
        }

        $texto = trim($request->input('comentario', ''));
        if (!empty($texto)) {
            $comentarioRepo = new ComentarioRepository();
            $comentarioRepo->create(['chamado_id' => $chamadoId, 'usuario_id' => $usuario->id, 'texto' => $texto]);
        }
        $voltar = in_array($request->query('voltar'), ['relatorios']) ? '?voltar=' . $request->query('voltar') : '';
        Url::redirect('/chamados/' . $chamadoId . $voltar); exit;
    }

    public function atualizar(Request $request, string $id): void
    {
        $chamadoId = (int) $id;
        $usuario = Auth::user();

        if (!in_array($usuario->perfil, ['tecnico', 'admin']) || !Csrf::validate($request->input('_token'))) {
            Url::redirect('/chamados/' . $chamadoId); exit;
        }

        $repo = new ChamadoRepository();
        $chamado = $repo->find($chamadoId);
        $historicoRepo = new HistoricoRepository();

        $novoStatus = $request->input('status');
        if ($novoStatus === 'fechado') { $novoStatus = $chamado->status; }
        if (!in_array($novoStatus, ['aberto', 'em_andamento', 'aguardando', 'resolvido'])) { $novoStatus = $chamado->status; }
        
        $novaPrioridade = $request->input('prioridade');
        if (!in_array($novaPrioridade, ['baixa', 'media', 'alta', 'urgente'])) { $novaPrioridade = $chamado->prioridade; }
        
        $novaCategoria = $request->input('categoria', $chamado->categoria);
        $novoResponsavelIds = $request->input('responsavel_ids', []);
        $motivoPausa = trim($request->input('motivo_pausa', ''));

        if ($novoStatus === 'aguardando' && $chamado->status !== 'aguardando' && empty($motivoPausa)) {
            Session::flash('erro', 'Informe o motivo pelo qual o chamado está aguardando.');
            Url::redirect('/chamados/' . $chamadoId); exit;
        }

        $textosHistorico = [];
        $dadosUpdate = [];
        $agora = date('Y-m-d H:i:s');

        if ($novoStatus === 'aguardando' && $chamado->status !== 'aguardando') {
            $dadosUpdate['sla_pausado_em'] = $agora;
            $dadosUpdate['motivo_pausa'] = mb_substr($motivoPausa, 0, 250);
            $textosHistorico[] = "Status: {$chamado->status} → Aguardando. Motivo: {$motivoPausa}";
        } elseif ($novoStatus !== 'aguardando' && $chamado->status === 'aguardando') {
            if ($chamado->sla_pausado_em) {
                $minutosPausados = (int) round((strtotime($agora) - strtotime($chamado->sla_pausado_em)) / 60);
                $dadosUpdate['tempo_pausado_minutos'] = $chamado->tempo_pausado_minutos + $minutosPausados;
                $dadosUpdate['sla_pausado_em'] = null;
            }
            $textosHistorico[] = "Status: Aguardando → {$novoStatus} (SLA Retomado)";
        }

        if ($novoStatus && $novoStatus !== $chamado->status && $novoStatus !== 'aguardando' && $chamado->status !== 'aguardando') {
            $textosHistorico[] = "Status: {$chamado->status} → {$novoStatus}";
        }
        $dadosUpdate['status'] = $novoStatus;

        if ($novaPrioridade && $novaPrioridade !== $chamado->prioridade) {
            $textosHistorico[] = "Prioridade: {$chamado->prioridade} → {$novaPrioridade}";
            $dadosUpdate['prioridade'] = $novaPrioridade;
        }
        
        if ($novaCategoria && $novaCategoria !== $chamado->categoria) {
            $textosHistorico[] = "Categoria: " . ($chamado->categoria ?: "Nenhuma") . " → {$novaCategoria}";
            $dadosUpdate['categoria'] = $novaCategoria;
        }

        if (!empty($novoResponsavelIds)) {
            $idsAntigos = $chamado->responsaveis_ids ? array_map('intval', explode(',', $chamado->responsaveis_ids)) : [];
            $idsNovos = array_map('intval', $novoResponsavelIds);
            
            $a = $idsAntigos; $b = $idsNovos; sort($a); sort($b);
            if ($a !== $b) {
                $dadosUpdate['responsavel_id'] = $idsNovos[0];
                if ($chamado->responsavel_id === null) {
                    $dadosUpdate['assumido_em'] = $agora;
                }
                
                $pdo = Database::connection();
                $pdo->prepare("DELETE FROM chamado_responsaveis WHERE chamado_id = ?")->execute([$chamadoId]);
                $stmtInsert = $pdo->prepare("INSERT INTO chamado_responsaveis (chamado_id, usuario_id) VALUES (?, ?)");
                foreach ($idsNovos as $uid) {
                    $stmtInsert->execute([$chamadoId, $uid]);
                }
                
                $textosHistorico[] = "Responsáveis atualizados.";
            }
        } elseif ($chamado->responsavel_id !== null) {
            $dadosUpdate['responsavel_id'] = null;
            $dadosUpdate['assumido_em'] = null;
            Database::connection()->prepare("DELETE FROM chamado_responsaveis WHERE chamado_id = ?")->execute([$chamadoId]);
            $textosHistorico[] = "Responsáveis removidos.";
        }

        if (!empty($dadosUpdate)) { $repo->update($chamadoId, $dadosUpdate); }

        foreach ($textosHistorico as $texto) {
            $historicoRepo->create(['chamado_id' => $chamadoId, 'usuario_id' => $usuario->id, 'texto' => $texto]);
        }

        $voltar = in_array($request->query('voltar'), ['relatorios']) ? '?voltar=' . $request->query('voltar') : '';
        Url::redirect('/chamados/' . $chamadoId . $voltar); exit;
    }

    public function formFechar(Request $request, string $id): string
    {
        $chamadoId = (int) $id;
        $repo = new ChamadoRepository();
        $chamado = $repo->find($chamadoId);

        if (!$chamado || !in_array(Auth::user()->perfil, ['tecnico', 'admin'])) {
            Url::redirect('/chamados/' . $chamadoId); exit;
        }

        $tempoCalculado = 0;
        if ($chamado->assumido_em) {
            $inicio = strtotime($chamado->assumido_em);
            $tempoCalculado = max(0, (int) round((time() - $inicio) / 60));
        }

        $equipamentoRepo = new EquipamentoRepository();
        $equipamentos = $equipamentoRepo->findAtivos();

        $listas = require dirname(__DIR__, 2) . '/config/listas.php';
        $tipos = $listas['tipos_chamado'];

        $mapaEquipamentos = [
            'Hardware' => ['Computador', 'Notebook', 'Impressora', 'Monitor', 'Roteador/Switch', 'Telefone', 'Outro'],
            'Software' => ['Computador', 'Notebook'],
            'Rede' => ['Computador', 'Notebook', 'Roteador/Switch', 'Telefone'],
            'Impressora' => ['Impressora', 'Computador', 'Notebook'],
            'Acesso' => ['Computador', 'Notebook', 'Outro'],
            'Outros' => ['Computador', 'Notebook', 'Impressora', 'Monitor', 'Roteador/Switch', 'Telefone', 'Outro']
        ];

        $voltar = in_array($request->query('voltar'), ['relatorios']) ? $request->query('voltar') : '';

        return View::render('chamados.fechar', [
            'usuario' => Auth::user(), 'chamado' => $chamado, 
            'tempoCalculado' => $tempoCalculado, 'equipamentos' => $equipamentos,
            'voltar' => $voltar, 'tipos' => $tipos, 'mapaEquipamentos' => $mapaEquipamentos
        ], 'layouts.main');
    }

    public function fechar(Request $request, string $id): void
    {
        $chamadoId = (int) $id;
        $usuario = Auth::user();

        if (!in_array($usuario->perfil, ['tecnico', 'admin']) || !Csrf::validate($request->input('_token'))) {
            Url::redirect('/chamados/' . $chamadoId); exit;
        }

        $repo = new ChamadoRepository();
        $chamado = $repo->find($chamadoId);
        
        $tipo = $request->input('tipo', '');
        $tipoOutro = trim($request->input('tipo_outro', ''));
        if ($tipo === 'Outros' && !empty($tipoOutro)) { $tipo = 'Outros: ' . $tipoOutro; }

        $equipamento = $request->input('equipamento', '');
        $tempo_min = (int) $request->input('tempo_min', 0);
        $solucao = $request->input('solucao', '');
        $solucao_publica = $request->input('solucao_publica', '');

        $dadosPausa = [];
        if ($chamado->sla_pausado_em) {
            $minutosPausados = (int) round((time() - strtotime($chamado->sla_pausado_em)) / 60);
            $dadosPausa['tempo_pausado_minutos'] = $chamado->tempo_pausado_minutos + $minutosPausados;
            $dadosPausa['sla_pausado_em'] = null;
        }

        Database::transaction(function (\PDO $pdo) use ($chamadoId, $usuario, $tipo, $equipamento, $tempo_min, $solucao, $solucao_publica, $dadosPausa) {
            $relatorioRepo = new RelatorioRepository();
            $relatorioExistente = $relatorioRepo->findByChamadoId($chamadoId);

            if ($relatorioExistente) {
                $relatorioRepo->update($relatorioExistente->id, [
                    'usuario_id' => $usuario->id, 'tipo' => $tipo, 
                    'equipamento' => $equipamento, 'tempo_min' => $tempo_min, 
                    'solucao' => $solucao, 'solucao_publica' => $solucao_publica
                ]);
            } else {
                $relatorioRepo->create([
                    'chamado_id' => $chamadoId, 'usuario_id' => $usuario->id,
                    'tipo' => $tipo, 'equipamento' => $equipamento, 'tempo_min' => $tempo_min, 
                    'solucao' => $solucao, 'solucao_publica' => $solucao_publica
                ]);
            }

            $chamadoRepo = new ChamadoRepository();
            $dadosUpdate = array_merge($dadosPausa, ['status' => 'fechado', 'fechado_em' => date('Y-m-d H:i:s'), 'categoria' => $tipo]);
            $chamadoRepo->update($chamadoId, $dadosUpdate);

            $historicoRepo = new HistoricoRepository();
            $historicoRepo->create([
                'chamado_id' => $chamadoId, 'usuario_id' => $usuario->id,
                'texto' => "Chamado encerrado com relatório de {$tempo_min} minutos."
            ]);
        });

        $files = $request->files('fotos_encerramento');
        if (!empty($files)) {
            $anexoService = new AnexoService();
            $anexoService->processarUploads($files, $chamadoId, $usuario->id);
        }

        $voltar = in_array($request->query('voltar'), ['relatorios']) ? '?voltar=' . $request->query('voltar') : '';
        Url::redirect('/chamados/' . $chamadoId . $voltar); exit;
    }

    public function fecharPeloSolicitante(Request $request, string $id): void
    {
        $chamadoId = (int) $id;
        $usuario = Auth::user();

        if (!Csrf::validate($request->input('_token'))) { Url::redirect('/chamados/' . $chamadoId); exit; }

        $repo = new ChamadoRepository();
        $chamado = $repo->find($chamadoId);

        if ($chamado->solicitante_id !== $usuario->id) { Url::redirect('/chamados/' . $chamadoId); exit; }

        if (!in_array($chamado->status, ['aberto', 'em_andamento', 'aguardando'])) {
            Session::flash('erro', 'Este chamado não pode ser fechado no status atual.');
            Url::redirect('/chamados/' . $chamadoId); exit;
        }

        $dadosPausa = [];
        if ($chamado->sla_pausado_em) {
            $minutosPausados = (int) round((time() - strtotime($chamado->sla_pausado_em)) / 60);
            $dadosPausa['tempo_pausado_minutos'] = $chamado->tempo_pausado_minutos + $minutosPausados;
            $dadosPausa['sla_pausado_em'] = null;
        }

        Database::transaction(function (\PDO $pdo) use ($chamadoId, $usuario, $dadosPausa) {
            $chamadoRepo = new ChamadoRepository();
            $dadosUpdate = array_merge($dadosPausa, ['status' => 'fechado', 'fechado_em' => date('Y-m-d H:i:s')]);
            $chamadoRepo->update($chamadoId, $dadosUpdate);
            $historicoRepo = new HistoricoRepository();
            $historicoRepo->create(['chamado_id' => $chamadoId, 'usuario_id' => $usuario->id, 'texto' => "Chamado fechado pelo solicitante (resolvido)."]);
        });

        Session::flash('sucesso', 'Chamado fechado com sucesso.');
        $voltar = in_array($request->query('voltar'), ['relatorios']) ? '?voltar=' . $request->query('voltar') : '';
        Url::redirect('/chamados/' . $chamadoId . $voltar); exit;
    }

    public function reabrir(Request $request, string $id): void
    {
        $chamadoId = (int) $id;
        $usuario = Auth::user();

        if (!Csrf::validate($request->input('_token'))) { Url::redirect('/chamados/' . $chamadoId); exit; }

        $repo = new ChamadoRepository();
        $chamado = $repo->find($chamadoId);

        if ($chamado->solicitante_id !== $usuario->id && !in_array($usuario->perfil, ['tecnico', 'admin'])) {
            Url::redirect('/chamados/' . $chamadoId); exit;
        }

        if (!in_array($chamado->status, ['resolvido', 'fechado'])) {
            Session::flash('erro', 'Apenas chamados resolvidos ou fechados podem ser reabertos.');
            Url::redirect('/chamados/' . $chamadoId); exit;
        }

        Database::transaction(function (\PDO $pdo) use ($chamadoId, $usuario) {
            $chamadoRepo = new ChamadoRepository();
            $chamadoRepo->update($chamadoId, ['status' => 'aberto', 'fechado_em' => null, 'assumido_em' => null]);
            $historicoRepo = new HistoricoRepository();
            $historicoRepo->create(['chamado_id' => $chamadoId, 'usuario_id' => $usuario->id, 'texto' => "Chamado reaberto."]);
        });

        Session::flash('sucesso', 'Chamado reaberto com sucesso.');
        $voltar = in_array($request->query('voltar'), ['relatorios']) ? '?voltar=' . $request->query('voltar') : '';
        Url::redirect('/chamados/' . $chamadoId . $voltar); exit;
    }

    public function create(Request $request): string
    {
        $secRepo = new SecretariaRepository();
        $secretarias = $secRepo->all();
        
        $localizacaoRepo = new LocalizacaoRepository();
        $locais = $localizacaoRepo->findAtivas(); // Pega todos os locais ativos

        return View::render('chamados.novo', [
            'locais' => $locais,
            'secretarias' => $secretarias
        ], 'layouts.main');
    }

    public function store(Request $request): string
    {
        if (!Csrf::validate($request->input('_token'))) {
            return View::render('chamados.novo', ['erro' => 'Sessão expirada.'], 'layouts.main');
        }

        $localizacaoId = $request->input('localizacao_id') ? (int)$request->input('localizacao_id') : null;
        
        // Pega a secretaria do formulário. Se não vier, usa a do usuário logado.
        $secretariaId = $request->input('secretaria_id') ? (int)$request->input('secretaria_id') : Auth::user()->secretaria_id;

        $service = new ChamadoService();
        $resultado = $service->abrir(
            $request->input('titulo', ''), 
            $request->input('descricao', ''), 
            Auth::id(), 
            $localizacaoId, 
            $secretariaId
        );

        if (!$resultado->success) {
            $secRepo = new SecretariaRepository();
            $localizacaoRepo = new LocalizacaoRepository();
            return View::render('chamados.novo', [
                'erro' => $resultado->error, 
                'locais' => $localizacaoRepo->findAtivas(),
                'secretarias' => $secRepo->all()
            ], 'layouts.main');
        }

        $chamadoId = $resultado->data;
        $files = $request->files('fotos');
        if (!empty($files)) {
            $anexoService = new AnexoService();
            $anexoService->processarUploads($files, $chamadoId, Auth::id());
        }

        $historicoRepo = new HistoricoRepository();
        $historicoRepo->create(['chamado_id' => $chamadoId, 'usuario_id' => Auth::id(), 'texto' => 'Chamado aberto.']);

        Url::redirect('/chamados'); exit;
    }

    public function anexar(Request $request, string $id): void
    {
        $chamadoId = (int) $id;
        $usuario = Auth::user();

        if (!Csrf::validate($request->input('_token'))) { 
            Url::redirect('/chamados/' . $chamadoId); exit; 
        }

        $repo = new ChamadoRepository();
        $chamado = $repo->find($chamadoId);
        
        if (!$chamado || ($chamado->solicitante_id !== $usuario->id && $usuario->perfil === 'solicitante')) {
            http_response_code(403); exit("Acesso negado.");
        }

        if ($chamado->status === 'fechado') {
            Session::flash('erro', 'Chamado fechado. Reabra para anexar novas fotos.');
            Url::redirect('/chamados/' . $chamadoId); exit;
        }

        $files = $request->files('fotos');
        if (!empty($files)) {
            $anexoService = new AnexoService();
            $anexoService->processarUploads($files, $chamadoId, $usuario->id);
            
            $historicoRepo = new HistoricoRepository();
            $historicoRepo->create([
                'chamado_id' => $chamadoId, 
                'usuario_id' => $usuario->id,
                'texto' => 'Nova(s) foto(s) anexada(s).'
            ]);

            Session::flash('sucesso', 'Foto(s) anexada(s) com sucesso.');
        } else {
            Session::flash('erro', 'Nenhuma foto válida foi enviada.');
        }

        $voltar = in_array($request->query('voltar'), ['relatorios']) ? '?voltar=' . $request->query('voltar') : '';
        Url::redirect('/chamados/' . $chamadoId . $voltar); exit;
    }

    public function excluirAnexo(Request $request, string $id): void
    {
        $anexoId = (int) $id;
        
        if (!Csrf::validate($request->input('_token'))) { 
            Url::redirect('/chamados'); exit; 
        }

        $anexoRepo = new AnexoRepository();
        $anexo = $anexoRepo->find($anexoId);

        if ($anexo) {
            $usuario = Auth::user();
            if ($anexo->usuario_id === $usuario->id || in_array($usuario->perfil, ['tecnico', 'admin'])) {
                $anexoService = new AnexoService();
                $anexoService->remover($anexoId);
                
                $historicoRepo = new HistoricoRepository();
                $historicoRepo->create([
                    'chamado_id' => $anexo->chamado_id, 
                    'usuario_id' => $usuario->id,
                    'texto' => 'Uma foto foi removida.'
                ]);

                Session::flash('sucesso', 'Foto removida.');
            } else {
                Session::flash('erro', 'Você não tem permissão para remover esta foto.');
            }
            $chamadoId = $anexo->chamado_id;
        } else {
            $chamadoId = 1; 
        }

        $voltar = in_array($request->query('voltar'), ['relatorios']) ? '?voltar=' . $request->query('voltar') : '';
        Url::redirect('/chamados/' . $chamadoId . $voltar); exit;
    }
}
