<?php
require __DIR__ . '/../../bootstrap/app.php';
use App\Core\Model;

class UsuarioDummy extends Model {
    public ?int $id = null;
    public string $nome = '';
}

$falhas = 0;
function check(string $label, bool $ok): void {
    global $falhas;
    echo ($ok ? "[OK]   " : "[FALHA]") . " {$label}" . PHP_EOL;
    if (!$ok) { $falhas++; }
}

echo "=== TESTE 1: Hidratacao correta (fromArray) ===" . PHP_EOL;
$user = (new UsuarioDummy())->fromArray(['id' => 1, 'nome' => 'Admin']);
check("ID foi preenchido", $user->id === 1);
check("Nome foi preenchido", $user->nome === 'Admin');

echo PHP_EOL . "=== TESTE 2: Conversao para array (toArray) ===" . PHP_EOL;
$arrayVolta = $user->toArray();
check("Array tem a chave id", array_key_exists('id', $arrayVolta));
check("Array tem a chave nome", array_key_exists('nome', $arrayVolta));
check("Valores batem", $arrayVolta['id'] === 1 && $arrayVolta['nome'] === 'Admin');

echo PHP_EOL . "=== TESTE 3: Chave desconhecida deve dar erro ===" . PHP_EOL;
try {
    (new UsuarioDummy())->fromArray(['id' => 2, 'nome' => 'Teste', 'senha' => '123']);
    check("Deveria ter lancado erro", false);
} catch (\InvalidArgumentException $e) {
    check("Excecao capturada", true);
    check("Mensagem cita a coluna errada", str_contains($e->getMessage(), 'senha'));
}

echo PHP_EOL;
echo $falhas === 0 ? "=== TODOS OS TESTES PASSARAM ===" . PHP_EOL : "=== {$falhas} TESTE(S) FALHARAM ===" . PHP_EOL;
exit($falhas === 0 ? 0 : 1);
