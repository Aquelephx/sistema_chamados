<?php

require __DIR__ . '/../../bootstrap/app.php';

use App\Core\Session;

$falhas = 0;
function check(string $label, bool $ok): void {
    global $falhas;
    echo ($ok ? "[OK]   " : "[FALHA]") . " {$label}" . PHP_EOL;
    if (!$ok) { $falhas++; }
}

echo "=== TESTE 1: Usuário não logado ===" . PHP_EOL;
check("usuarioId() é null", Session::usuarioId() === null);

echo PHP_EOL . "=== TESTE 2: Login ===" . PHP_EOL;
Session::login(42);
check("usuarioId() retorna 42", Session::usuarioId() === 42);

echo PHP_EOL . "=== TESTE 3: Logout ===" . PHP_EOL;
Session::logout();
check("usuarioId() voltou a ser null", Session::usuarioId() === null);

echo PHP_EOL . "=== TESTE 4: Flash Message ===" . PHP_EOL;
Session::flash('erro', 'Senha inválida');
$msg = Session::getFlash('erro');
check("Recuperou a mensagem", $msg === 'Senha inválida');
check("Mensagem foi apagada após ler", Session::getFlash('erro') === null);

echo PHP_EOL;
echo $falhas === 0 ? "=== TODOS OS TESTES PASSARAM ===" . PHP_EOL : "=== {$falhas} TESTE(S) FALHARAM ===" . PHP_EOL;
exit($falhas === 0 ? 0 : 1);
