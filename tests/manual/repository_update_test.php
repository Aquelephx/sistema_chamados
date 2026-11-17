<?php

require __DIR__ . '/../../bootstrap/app.php';

use App\Repositories\UsuarioRepository;

$falhas = 0;
function check(string $label, bool $ok): void {
    global $falhas;
    echo ($ok ? "[OK]   " : "[FALHA]") . " {$label}" . PHP_EOL;
    if (!$ok) { $falhas++; }
}

$repo = new UsuarioRepository();

// Preparação: cria um usuário para testar
$id = $repo->create([
    'nome' => 'Original',
    'email' => 'original@teste.com',
    'senha_hash' => 'xyz',
    'perfil' => 'solicitante'
]);

echo "=== TESTE 1: Atualização válida ===" . PHP_EOL;
$atualizou = $repo->update($id, [
    'nome' => 'Nome Alterado',
    'email' => 'alterado@teste.com',
    'cargo_falso' => 'hacker' // O fillable deve ignorar isso
]);
check("Retornou true", $atualizou === true);

$buscado = $repo->find($id);
check("Buscou o usuário", $buscado !== null);
check("Nome foi atualizado", $buscado->nome === 'Nome Alterado');
check("Email foi atualizado", $buscado->email === 'alterado@teste.com');

echo PHP_EOL . "=== TESTE 2: ID inválido (0) ===" . PHP_EOL;
try {
    $repo->update(0, ['nome' => 'Teste']);
    check("Deveria ter lançado erro no ID 0", false);
} catch (\InvalidArgumentException $e) {
    check("Exceção capturada no ID 0", true);
}

echo PHP_EOL . "=== TESTE 3: Array vazio após fillable ===" . PHP_EOL;
try {
    $repo->update($id, ['cargo_falso' => 'hacker']);
    check("Deveria ter lançado erro com array vazio", false);
} catch (\InvalidArgumentException $e) {
    check("Exceção capturada com array vazio", true);
}

// Limpeza
$repo->delete($id);
check("Usuário deletado após o teste", true);

echo PHP_EOL;
echo $falhas === 0 ? "=== TODOS OS TESTES PASSARAM ===" . PHP_EOL : "=== {$falhas} TESTE(S) FALHARAM ===" . PHP_EOL;
exit($falhas === 0 ? 0 : 1);
