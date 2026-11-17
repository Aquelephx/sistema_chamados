<?php

declare(strict_types=1);

ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

require dirname(__DIR__) . '/bootstrap/app.php';

use App\Core\Request;
use App\Core\Router;
use App\Core\Session;
use App\Core\Auth;
use App\Core\Url;

Session::iniciar();
Session::verificarInatividade();

// "Manter conectado": sem sessão ativa mas com cookie válido, reabre o login sozinho.
if (Session::usuarioId() === null && isset($_COOKIE[\App\Services\LembrarService::COOKIE])) {
    (new \App\Services\LembrarService())->restaurarSessao();
}

// BLOQUEIO GLOBAL: Se estiver logado e precisar trocar a senha, redireciona
$request = Request::capture();
if (Auth::check() && Auth::user()->trocar_senha === 1) {
    // Permite apenas acessar a tela de trocar senha e fazer logout
    if ($request->uri() !== '/trocar-senha' && $request->uri() !== '/logout') {
        Url::redirect('/trocar-senha');
    }
}

$router = new Router();
require dirname(__DIR__) . '/routes/web.php';

try {
    $response = $router->dispatch($request);
    
    if (is_string($response)) {
        echo $response;
    }
} catch (\Throwable $e) {
    // Loga o erro real para o desenvolvedor
    error_log($e->getMessage() . ' em ' . $e->getFile() . ':' . $e->getLine());
    
    // Mostra a tela bonita para o usuário
    http_response_code(500);
    echo \App\Core\View::render('errors.500', [], 'layouts.auth');
}