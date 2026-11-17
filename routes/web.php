<?php

use App\Controllers\AuthController;
use App\Controllers\ChamadoController;
use App\Controllers\UsuarioController;
use App\Controllers\LocalizacaoController;
use App\Controllers\EquipamentoController;
use App\Controllers\AnexoController;
use App\Controllers\ApiController;
use App\Controllers\PainelController;
use App\Controllers\RelatorioController;

/** @var App\Core\Router $router */

$router->get('/', [AuthController::class, 'showLogin']); 
$router->get('/login', [AuthController::class, 'showLogin']);
$router->post('/login', [AuthController::class, 'login']);
$router->get('/cadastro', [AuthController::class, 'showCadastro']);
$router->post('/cadastro', [AuthController::class, 'cadastrar']);
$router->get('/esqueci', [AuthController::class, 'showEsqueci']);
$router->post('/esqueci', [AuthController::class, 'esqueci']);
$router->get('/logout', [AuthController::class, 'logout']);

$router->get('/chamados', [ChamadoController::class, 'index'], ['auth']);
$router->get('/chamados/novo', [ChamadoController::class, 'create'], ['auth']);
$router->post('/chamados', [ChamadoController::class, 'store'], ['auth']);
$router->get('/chamados/{id}', [ChamadoController::class, 'show'], ['auth']);
$router->post('/chamados/{id}/atualizar', [ChamadoController::class, 'atualizar'], ['auth']);
$router->post('/chamados/{id}/comentarios', [ChamadoController::class, 'comentar'], ['auth']);
$router->post('/chamados/{id}/avaliar', [ChamadoController::class, 'avaliar']);
$router->get('/chamados/{id}/fechar', [ChamadoController::class, 'formFechar'], ['auth']);
$router->post('/chamados/{id}/fechar', [ChamadoController::class, 'fechar'], ['auth']);
$router->post('/chamados/{id}/fechar-solicitante', [ChamadoController::class, 'fecharPeloSolicitante'], ['auth']);
$router->post('/chamados/{id}/reabrir', [ChamadoController::class, 'reabrir'], ['auth']);

$router->post('/chamados/{id}/anexos', [ChamadoController::class, 'anexar'], ['auth']);
$router->get('/anexos/{id}', [AnexoController::class, 'show'], ['auth']);
$router->post('/anexos/{id}/excluir', [AnexoController::class, 'destroy'], ['auth']);

$router->get('/trocar-senha', [UsuarioController::class, 'formTrocarSenha'], ['auth']);
$router->post('/trocar-senha', [UsuarioController::class, 'trocarSenha'], ['auth']);
$router->get('/meu-perfil', [UsuarioController::class, 'formPerfil'], ['auth']);
$router->post('/meu-perfil', [UsuarioController::class, 'atualizarPerfil'], ['auth']);

$router->get('/usuarios', [UsuarioController::class, 'index'], ['auth']);
$router->post('/usuarios/{id}/atualizar', [UsuarioController::class, 'atualizar'], ['auth']);
$router->post('/usuarios/{id}/resetar-senha', [UsuarioController::class, 'resetarSenha'], ['auth']);
$router->post('/usuarios/{id}/status', [UsuarioController::class, 'alterarStatus'], ['auth']);

$router->get('/locais', [LocalizacaoController::class, 'index'], ['auth']);
$router->get('/locais/novo', [LocalizacaoController::class, 'create'], ['auth']);
$router->post('/locais', [LocalizacaoController::class, 'store'], ['auth']);
$router->post('/locais/{id}/status', [LocalizacaoController::class, 'alterarStatus'], ['auth']);
$router->post('/locais/{id}/excluir', [LocalizacaoController::class, 'destroy'], ['auth']);

$router->get('/equipamentos', [EquipamentoController::class, 'index'], ['auth']);
$router->get('/equipamentos/novo', [EquipamentoController::class, 'create'], ['auth']);
$router->post('/equipamentos', [EquipamentoController::class, 'store'], ['auth']);

$router->get('/relatorios', [RelatorioController::class, 'index'], ['auth']);
$router->get('/relatorios/exportar', [RelatorioController::class, 'exportar'], ['auth']);

$router->get('/painel', [PainelController::class, 'index'], ['auth']);
$router->get('/api/indicadores', [ApiController::class, 'indicadores'], ['auth']);
$router->get('/api/chamados/status', [ApiController::class, 'chamadosStatus'], ['auth']);
