<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Request;
use App\Core\View;
use App\Core\Auth;
use App\Core\Url;
use App\Core\Csrf;
use App\Core\Session;
use App\Repositories\EquipamentoRepository;

class EquipamentoController
{
    public function index(Request $request): string
    {
        if (!Auth::isAdmin()) { Url::redirect('/chamados'); exit; }

        $repo = new EquipamentoRepository();
        $equipamentos = $repo->all();

        return View::render('equipamentos.index', [
            'equipamentos' => $equipamentos
        ], 'layouts.main');
    }

    public function create(Request $request): string
    {
        if (!Auth::isAdmin()) { Url::redirect('/chamados'); exit; }
        return View::render('equipamentos.novo', [], 'layouts.main');
    }

    public function store(Request $request): string
    {
        if (!Auth::isAdmin() || !Csrf::validate($request->input('_token'))) {
            Url::redirect('/equipamentos'); exit;
        }

        $nome = trim($request->input('nome', ''));

        if (empty($nome)) {
            return View::render('equipamentos.novo', ['erro' => 'O nome do equipamento é obrigatório.'], 'layouts.main');
        }

        $repo = new EquipamentoRepository();
        
        if ($repo->findByNome($nome) !== null) {
            return View::render('equipamentos.novo', ['erro' => 'Este equipamento já está cadastrado.'], 'layouts.main');
        }

        $repo->create(['nome' => $nome, 'ativo' => 1]);
        Session::flash('sucesso', 'Equipamento cadastrado com sucesso.');
        Url::redirect('/equipamentos'); exit;
    }

    public function alterarStatus(Request $request, string $id): void
    {
        if (!Auth::isAdmin() || !Csrf::validate($request->input('_token'))) {
            Url::redirect('/equipamentos'); exit;
        }

        $equipamentoId = (int) $id;
        $repo = new EquipamentoRepository();
        $equipamento = $repo->find($equipamentoId);

        if ($equipamento) {
            $novoStatus = $equipamento->ativo === 1 ? 0 : 1;
            $repo->update($equipamentoId, ['ativo' => $novoStatus]);
            $msg = $novoStatus === 1 ? 'Equipamento ativado.' : 'Equipamento desativado.';
            Session::flash('sucesso', $msg);
        }

        Url::redirect('/equipamentos'); exit;
    }

    public function destroy(Request $request, string $id): void
    {
        if (!Auth::isAdmin() || !Csrf::validate($request->input('_token'))) {
            Url::redirect('/equipamentos'); exit;
        }

        $equipamentoId = (int) $id;
        $repo = new EquipamentoRepository();
        
        try {
            $repo->delete($equipamentoId);
            Session::flash('sucesso', 'Equipamento excluído com sucesso.');
        } catch (\PDOException $e) {
            Session::flash('erro', 'Não é possível excluir este equipamento pois ele está vinculado a chamados existentes. Tente apenas desativá-lo.');
        }

        Url::redirect('/equipamentos'); exit;
    }
}
