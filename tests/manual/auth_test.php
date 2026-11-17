<?php

require __DIR__ . '/../../bootstrap/app.php';

use App\Core\Database;
use App\Services\AuthService;
use App\Repositories\UsuarioRepository;

$falhas = 0;
function check(string $label, bool $ok): void {
    global $falhas;
    echo ($ok ? "[OK]   " : "[FALHA]") . " {$label}" . PHP_EOL;
    if (!$ok) { $falhas++; }
}

$repo = new UsuarioRepository();
$auth = new AuthService($repo);
$pdo = Database::connection();

// Limpa a tabela para o teste rodar limpo
$pdo->exec("DELETE FROM usuarios");

echo "=== TESTE 1: Cadastro com sucesso ===" . PHP_EOL;
$res = $auth->registrar('João', 'joao@teste.com', 'senha123');
check("Cadastro deu certo", $res->success === true);
check("Retornou o ID do usuário", is_int($res->data));

$usuarioBanco = $repo->find($res->data);
check("Senha foi criptografada (não é senha123)", $usuarioBanco->senha_hash !== 'senha123');
check("Perfil é solicitante", $usuarioBanco->perfil === 'solicitante');

echo PHP_EOL . "=== TESTE 2: E-mail duplicado ===" . PHP_EOL;
$resDuplicado = $auth->registrar('Outro', 'joao@teste.com', 'outrasenha');
check("Falhou ao cadastrar duplicado", $resDuplicado->success === false);
check("Mensagem de erro correta", str_contains($resDuplicado->error, 'já está cadastrado'));

echo PHP_EOL . "=== TESTE 3: Login com sucesso ===" . PHP_EOL;
$resLogin = $auth->login('joao@teste.com', 'senha123');
check("Login deu certo", $resLogin->success === true);
check("Retornou o objeto Usuario", $resLogin->data instanceof \App\Models\Usuario);
check("Nome do usuário logado está certo", $resLogin->data->nome === 'João');

echo PHP_EOL . "=== TESTE 4: Login com senha errada ===" . PHP_EOL;
$resErrado = $auth->login('joao@teste.com', 'senha_errada');
check("Login falhou", $resErrado->success === false);
check("Mensagem genérica (não vaza se o email existe)", $resErrado->error === 'E-mail ou senha incorretos.');

echo PHP_EOL . "=== TESTE 5: Usuário desativado ===" . PHP_EOL;
$repo->update($res->data, ['ativo' => 0]);
$resInativo = $auth->login('joao@teste.com', 'senha123');
check("Login de inativo falhou", $resInativo->success === false);
check("Mensagem cita desativação", str_contains($resInativo->error, 'desativada'));

// Limpeza final
$pdo->exec("DELETE FROM usuarios");

echo PHP_EOL;
echo $falhas === 0 ? "=== TODOS OS TESTES PASSARAM ===" . PHP_EOL : "=== {$falhas} TESTE(S) FALHARAM ===" . PHP_EOL;
exit($falhas === 0 ? 0 : 1);
