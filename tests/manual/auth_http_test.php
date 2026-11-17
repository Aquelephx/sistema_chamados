<?php

require __DIR__ . '/../../bootstrap/app.php';

use App\Core\Session;
use App\Core\Auth;
use App\Repositories\UsuarioRepository;
use App\Services\AuthService;
use App\Core\Database;

$falhas = 0;
function check(string $label, bool $ok): void {
    global $falhas;
    echo ($ok ? "[OK]   " : "[FALHA]") . " {$label}" . PHP_EOL;
    if (!$ok) { $falhas++; }
}

$pdo = Database::connection();
$pdo->exec("DELETE FROM usuarios");

$repo = new UsuarioRepository();
$authService = new AuthService($repo);

// Cria um usuário
$res = $authService->registrar('Carlos', 'carlos@teste.com', 'senha123');
$id = $res->data;

echo "=== TESTE 1: Sem sessão ===" . PHP_EOL;
check("Auth::user() é null", Auth::user() === null);
check("Auth::check() é false", Auth::check() === false);

echo PHP_EOL . "=== TESTE 2: Com sessão iniciada ===" . PHP_EOL;
Session::login($id);

// O Auth tem cache na memória, precisamos reiniciar o estado dele para o teste
// (Isso é um detalhe do teste, no mundo real cada requisição é um processo novo)
$ref = new ReflectionClass(Auth::class);
$propBuscou = $ref->getProperty('buscou');
$propBuscou->setAccessible(true);
$propBuscou->setValue(null, false);
$propUsuario = $ref->getProperty('usuarioLogado');
$propUsuario->setAccessible(true);
$propUsuario->setValue(null, null);

$user = Auth::user();
check("Auth::user() não é null", $user !== null);
check("Auth::user() é o Carlos", $user->nome === 'Carlos');
check("Auth::id() bate com o ID", Auth::id() === $id);
check("Auth::isAdmin() é false", Auth::isAdmin() === false);

echo PHP_EOL . "=== TESTE 3: Usuário desativado no banco ===" . PHP_EOL;
$repo->update($id, ['ativo' => 0]);

$propBuscou->setValue(null, false);
$propUsuario->setValue(null, null);

check("Auth::user() vira null", Auth::user() === null);
check("Sessão foi destruída", Session::usuarioId() === null);

// Limpeza
$pdo->exec("DELETE FROM usuarios");

echo PHP_EOL;
echo $falhas === 0 ? "=== TODOS OS TESTES PASSARAM ===" . PHP_EOL : "=== {$falhas} TESTE(S) FALHARAM ===" . PHP_EOL;
exit($falhas === 0 ? 0 : 1);
