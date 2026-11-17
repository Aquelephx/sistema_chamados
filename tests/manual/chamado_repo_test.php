<?php

require __DIR__ . '/../../bootstrap/app.php';

use App\Repositories\ChamadoRepository;
use App\Repositories\UsuarioRepository;

$falhas = 0;
function check(string $label, bool $ok): void {
    global $falhas;
    echo ($ok ? "[OK]   " : "[FALHA]") . " {$label}" . PHP_EOL;
    if (!$ok) { $falhas++; }
}

echo "=== Preparando banco ===" . PHP_EOL;
$usuarioRepo = new UsuarioRepository();
$chamadoRepo = new ChamadoRepository();

// Limpa chamados e garante que existe um solicitante
App\Core\Database::connection()->exec("DELETE FROM chamados");
$usuarios = $usuarioRepo->all();
if (empty($usuarios)) {
    $idUser = $usuarioRepo->create(['nome' => 'Solicitante Teste', 'email' => 'teste@teste.com', 'senha_hash' => 'x', 'perfil' => 'solicitante', 'ativo' => 1]);
} else {
    $idUser = $usuarios[0]->id;
}

echo "=== TESTE 1: Criar um chamado ===" . PHP_EOL;
$dadosChamado = [
    'titulo' => 'Computador não liga',
    'descricao' => 'Apertei o botão e nada acontece.',
    'solicitante_id' => $idUser,
    'prioridade' => 'alta'
];

$idChamado = $chamadoRepo->create($dadosChamado);
check("Chamado criado com ID", $idChamado > 0);

echo PHP_EOL . "=== TESTE 2: Buscar o chamado criado ===" . PHP_EOL;
$chamado = $chamadoRepo->find($idChamado);

check("Chamado retornou do banco", $chamado !== null);
check("Título está correto", $chamado->titulo === 'Computador não liga');
check("Status inicial é 'aberto'", $chamado->status === 'aberto');
check("Prioridade é 'alta'", $chamado->prioridade === 'alta');
check("Responsável começa vazio (NULL)", $chamado->responsavel_id === null);

echo PHP_EOL . "=== TESTE 3: Atualizar o chamado (assumir) ===" . PHP_EOL;
$chamadoRepo->update($idChamado, ['responsavel_id' => $idUser, 'status' => 'em_andamento']);

$chamadoAtualizado = $chamadoRepo->find($idChamado);
check("Status mudou para 'em_andamento'", $chamadoAtualizado->status === 'em_andamento');
check("Responsável foi preenchido", $chamadoAtualizado->responsavel_id === $idUser);

// Limpeza
App\Core\Database::connection()->exec("DELETE FROM chamados");

echo PHP_EOL;
echo $falhas === 0 ? "=== TODOS OS TESTES PASSARAM ===" . PHP_EOL : "=== {$falhas} TESTE(S) FALHARAM ===" . PHP_EOL;
exit($falhas === 0 ? 0 : 1);
