<?php

require __DIR__ . '/../../bootstrap/app.php';

use App\Repositories\UsuarioRepository;

$falhas = 0;
function check(string $label, bool $ok): void {
    global $falhas;
    echo ($ok ? "[OK]   " : "[FALHA]") . " {$label}" . PHP_EOL;
    if (!$ok) { $falhas++; }
}

echo "=== TESTE 1: Create com filtro de segurança ===" . PHP_EOL;
$repo = new UsuarioRepository();

$dados = [
    'nome' => 'Admin Teste',
    'email' => 'admin@teste.com',
    'senha_hash' => password_hash('123456', PASSWORD_DEFAULT),
    'perfil' => 'admin',
    'id' => 999,           // MALICIOSO: tentar forçar o ID
    'cargo_falso' => 'hacker' // MALICIOSO: coluna que não existe
];

$novoId = $repo->create($dados);
check("Create retornou um ID válido", $novoId > 0);
check("O ID não foi 999 (fillable funcionou)", $novoId !== 999);

echo PHP_EOL . "=== TESTE 2: Buscar o usuário criado ===" . PHP_EOL;
$usuario = $repo->find($novoId);
check("Conseguiu buscar o usuário", $usuario !== null);
check("O nome está correto", $usuario->nome === 'Admin Teste');
check("O email está correto", $usuario->email === 'admin@teste.com');

echo PHP_EOL . "=== TESTE 3: Excluir o usuário ===" . PHP_EOL;
$deletou = $repo->delete($novoId);
check("Delete retornou true", $deletou === true);

$usuarioDeletado = $repo->find($novoId);
check("O usuário sumiu do banco", $usuarioDeletado === null);

echo PHP_EOL;
echo $falhas === 0 ? "=== TODOS OS TESTES PASSARAM ===" . PHP_EOL : "=== {$falhas} TESTE(S) FALHARAM ===" . PHP_EOL;
exit($falhas === 0 ? 0 : 1);
