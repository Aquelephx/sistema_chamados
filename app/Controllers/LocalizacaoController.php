<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Request;
use App\Core\View;
use App\Core\Auth;
use App\Core\Url;
use App\Core\Csrf;
use App\Core\Session;
use App\Repositories\LocalizacaoRepository;
use App\Repositories\SecretariaRepository;

class LocalizacaoController
{
    public function index(Request $request): string
    {
        if (!Auth::isAdmin()) { Url::redirect('/chamados'); exit; }

        $repo = new LocalizacaoRepository();
        $localizacoes = $repo->getAllWithSecretarias(); // <-- Usando o método com JOIN

        return View::render('localizacoes.index', [
            'localizacoes' => $localizacoes
        ], 'layouts.main');
    }

    public function create(Request $request): string
    {
        if (!Auth::isAdmin()) { Url::redirect('/chamados'); exit; }

        $secRepo = new SecretariaRepository();
        $secretarias = $secRepo->findAtivas();

        return View::render('localizacoes.novo', ['secretarias' => $secretarias], 'layouts.main');
    }

    public function store(Request $request): string
    {
        if (!Auth::isAdmin() || !Csrf::validate($request->input('_token'))) {
            Url::redirect('/locais'); exit;
        }

        $nome = trim($request->input('nome', ''));
        $secretariaId = $request->input('secretaria_id') ? (int)$request->input('secretaria_id') : null;

        if (empty($nome) || empty($secretariaId)) {
            $secRepo = new SecretariaRepository();
            return View::render('localizacoes.novo', [
                'erro' => 'O nome e a secretaria são obrigatórios.',
                'secretarias' => $secRepo->findAtivas()
            ], 'layouts.main');
        }

        $repo = new LocalizacaoRepository();
        
        if ($repo->findByNome($nome, $secretariaId) !== null) {
            $secRepo = new SecretariaRepository();
            return View::render('localizacoes.novo', [
                'erro' => 'Este local já está cadastrado nesta secretaria.',
                'secretarias' => $secRepo->findAtivas()
            ], 'layouts.main');
        }

        $repo->create(['nome' => $nome, 'secretaria_id' => $secretariaId, 'ativo' => 1]);
        Session::flash('sucesso', 'Local cadastrado com sucesso.');
        Url::redirect('/locais'); exit;
    }

    public function alterarStatus(Request $request, string $id): void
    {
        if (!Auth::isAdmin() || !Csrf::validate($request->input('_token'))) {
            Url::redirect('/locais'); exit;
        }

        $localId = (int) $id;
        $repo = new LocalizacaoRepository();
        $local = $repo->find($localId);

        if ($local) {
            $novoStatus = $local->ativo === 1 ? 0 : 1;
            $repo->update($localId, ['ativo' => $novoStatus]);
            Session::flash('sucesso', $novoStatus === 1 ? 'Local ativado.' : 'Local desativado.');
        }

        Url::redirect('/locais'); exit;
    }

    public function destroy(Request $request, string $id): void
    {
        if (!Auth::isAdmin() || !Csrf::validate($request->input('_token'))) {
            Url::redirect('/locais'); exit;
        }

        $localId = (int) $id;
        $repo = new LocalizacaoRepository();
        
        try {
            $repo->delete($localId);
            Session::flash('sucesso', 'Local excluído com sucesso.');
        } catch (\PDOException $e) {
            Session::flash('erro', 'Não é possível excluir este local pois ele está vinculado a chamados existentes. Tente apenas desativá-lo.');
        }

        Url::redirect('/locais'); exit;
    }
}
