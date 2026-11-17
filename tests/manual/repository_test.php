<?php

require __DIR__ . '/../../bootstrap/app.php';

use App\Repositories\UsuarioRepository;
use App\Models\Usuario;

$falhas = 0;
function check(string $label, bool $ok): void {
    global $falhas;
    echo ($ok ? "[OK]   " : "[FALHA]") . " {$label}" . PHP_EOL;
    if (!$ok) { $falhas++; }
}

echo "=== TESTE: Repositório no banco real ===" . PHP_EOL;

$repo = new UsuarioRepository();

// 1. Testa o all() (banco está vazio, deve retornar array vazio)
$todos = $repo->all();
check("all() retorna um array", is_array($todos));
check("all() está vazio (banco zerado)", count($todos) === 0);

// 2. Testa o find() com ID que não existe
$naoExiste = $repo->find(999);
check("find() de ID inexistente retorna null", $naoExiste === null);

echo PHP_EOL;
echo $falhas === 0 ? "=== TODOS OS TESTES PASSARAM ===" . PHP_EOL : "=== {$falhas} TESTE(S) FALHARAM ===" . PHP_EOL;
exit($falhas === 0 ? 0 : 1);
