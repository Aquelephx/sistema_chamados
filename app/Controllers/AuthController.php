<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Request;
use App\Core\View;
use App\Core\Session;
use App\Core\Url;
use App\Core\Csrf;
use App\Core\Auth;
use App\Repositories\UsuarioRepository;
use App\Repositories\SecretariaRepository;
use App\Repositories\LocalizacaoRepository;
use App\Services\AuthService;
use App\Services\LembrarService;

class AuthController
{
    public function showLogin(Request $request): string
    {
        if (Auth::check()) {
            Url::redirect(Auth::user()->perfil === 'painel' ? '/painel' : '/chamados');
            exit;
        }
        return View::render('auth.login', [], 'layouts.auth');
    }

    public function login(Request $request): string
    {
        if (!Csrf::validate($request->input('_token'))) {
            return View::render('auth.login', ['erro' => 'Sessão expirada.'], 'layouts.auth');
        }

        $repo = new UsuarioRepository();
        $authService = new AuthService($repo);
        
        $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
        $resultado = $authService->login($request->input('email', ''), $request->input('senha', ''), $ip);

        if (!$resultado->success) {
            // CORREÇÃO: Devolve o e-mail para a view para não apagar o que o usuário digitou
            return View::render('auth.login', [
                'erro' => $resultado->error,
                'email' => $request->input('email', '') 
            ], 'layouts.auth');
        }

        Session::login($resultado->data->id);

        // Cookie antigo deste navegador (se houver) sai de cena; só cria outro se marcou a caixa.
        $lembrar = new LembrarService();
        $lembrar->esquecer();
        if ($request->input('lembrar') === '1') {
            $lembrar->lembrar($resultado->data->id);
        }

        Url::redirect($resultado->data->perfil === 'painel' ? '/painel' : '/chamados');
        exit;
    }

    public function showCadastro(Request $request): string
    {
        if (Auth::check()) { Url::redirect('/chamados'); exit; }

        $secRepo = new SecretariaRepository();
        $locRepo = new LocalizacaoRepository();

        return View::render('auth.cadastro', [
            'secretarias' => $secRepo->all(),
            'locais' => $locRepo->all()
        ], 'layouts.auth');
    }

    public function cadastrar(Request $request): string
{
    if (!Csrf::validate($request->input('_token'))) {
        $secRepo = new SecretariaRepository();
        $locRepo = new LocalizacaoRepository();
        return View::render('auth.cadastro', [
            'erro' => 'Sessão expirada.',
            'secretarias' => $secRepo->all(),
            'locais' => $locRepo->all()
        ], 'layouts.auth');
    }

    $repo = new UsuarioRepository();
    $authService = new AuthService($repo);
    
    $resultado = $authService->registrar(
        $request->input('nome', ''), 
        $request->input('email', ''), 
        $request->input('senha', ''), 
        $request->input('codigo_empresa', ''),
        $request->input('secretaria_id') ? (int)$request->input('secretaria_id') : null,
        $request->input('localizacao_id') ? (int)$request->input('localizacao_id') : null
    );

    if (!$resultado->success) {
        $secRepo = new SecretariaRepository();
        $locRepo = new LocalizacaoRepository();
        // CORREÇÃO: Devolve os dados digitados para a view
        return View::render('auth.cadastro', [
            'erro' => $resultado->error,
            'secretarias' => $secRepo->all(),
            'locais' => $locRepo->all(),
            'nome' => $request->input('nome', ''),
            'email' => $request->input('email', ''),
            'codigo_empresa' => $request->input('codigo_empresa', ''),
            'secretaria_id' => $request->input('secretaria_id'),
            'localizacao_id' => $request->input('localizacao_id')
        ], 'layouts.auth');
    }

    return View::render('auth.login', ['sucesso' => 'Conta criada com sucesso! Faça login.'], 'layouts.auth');
}
        

    public function showEsqueci(Request $request): string
    {
        if (Auth::check()) { Url::redirect('/chamados'); exit; }
        return View::render('auth.esqueci', [], 'layouts.auth');
    }

    public function esqueci(Request $request): string
    {
        if (!Csrf::validate($request->input('_token'))) {
            return View::render('auth.esqueci', ['erro' => 'Sessão expirada.'], 'layouts.auth');
        }

        $repo = new UsuarioRepository();
        $authService = new AuthService($repo);
        
        $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
        $resultado = $authService->pedirNovaSenha($request->input('email', ''), $ip);

        if (!$resultado->success) {
            return View::render('auth.esqueci', ['erro' => $resultado->error], 'layouts.auth');
        }

        return View::render('auth.esqueci', ['msg' => $resultado->data], 'layouts.auth');
    }

    public function logout(): void
    {
        (new LembrarService())->esquecer(); // senão o cookie faria o login voltar sozinho
        Session::logout();
        Url::redirect('/login');
    }
}
