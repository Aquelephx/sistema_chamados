<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Request;
use App\Core\View;
use App\Core\Auth;
use App\Core\Url;
use App\Core\Csrf;
use App\Core\Session;
use App\Repositories\SecretariaRepository;

class SecretariaController
{
    public function index(Request $request): string
    {
        if (!Auth::isAdmin()) { Url::redirect('/chamados'); exit; }

        $repo = new SecretariaRepository();
        $secretarias = $repo->all();

        return View::render('secretarias.index', [
            'secretarias' => $secretarias
        ], 'layouts.main');
    }

    public function create(Request $request): string
    {
        if (!Auth::isAdmin()) { Url::redirect('/chamados'); exit; }
        return View::render('secretarias.novo', [], 'layouts.main');
    }

    public function store(Request $request): string
    {
        if (!Auth::isAdmin() || !Csrf::validate($request->input('_token'))) {
            Url::redirect('/secretarias'); exit;
        }

        $nome = trim($request->input('nome', ''));

        if (empty($nome)) {
            return View::render('secretarias.novo', ['erro' => 'O nome da secretaria é obrigatório.'], 'layouts.main');
        }

        $repo = new SecretariaRepository();
        
        if ($repo->findByNome($nome) !== null) {
            return View::render('secretarias.novo', ['erro' => 'Esta secretaria já está cadastrada.'], 'layouts.main');
        }

        $repo->create(['nome' => $nome, 'ativo' => 1]);
        Session::flash('sucesso', 'Secretaria cadastrada com sucesso.');
        Url::redirect('/secretarias'); exit;
    }

    public function alterarStatus(Request $request, string $id): void
    {
        if (!Auth::isAdmin() || !Csrf::validate($request->input('_token'))) {
            Url::redirect('/secretarias'); exit;
        }

        $id = (int) $id;
        $repo = new SecretariaRepository();
        $secretaria = $repo->find($id);

        if ($secretaria) {
            $novoStatus = $secretaria->ativo === 1 ? 0 : 1;
            $repo->update($id, ['ativo' => $novoStatus]);
            $msg = $novoStatus === 1 ? 'Secretaria ativada.' : 'Secretaria desativada.';
            Session::flash('sucesso', $msg);
        }

        Url::redirect('/secretarias'); exit;
    }

    public function destroy(Request $request, string $id): void
    {
        if (!Auth::isAdmin() || !Csrf::validate($request->input('_token'))) {
            Url::redirect('/secretarias'); exit;
        }

        $id = (int) $id;
        $repo = new SecretariaRepository();
        
        try {
            $repo->delete($id);
            Session::flash('sucesso', 'Secretaria excluída com sucesso.');
        } catch (\PDOException $e) {
            Session::flash('erro', 'Não é possível excluir esta secretaria pois existem locais ou usuários vinculados a ela. Tente apenas desativá-la.');
        }

        Url::redirect('/secretarias'); exit;
    }
}
