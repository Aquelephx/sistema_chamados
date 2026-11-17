<?php

require __DIR__ . '/../../bootstrap/app.php';

use App\Core\Request;
use App\Core\Router;

$falhas = 0;
function check(string $label, bool $ok): void {
    global $falhas;
    echo ($ok ? "[OK]   " : "[FALHA]") . " {$label}" . PHP_EOL;
    if (!$ok) { $falhas++; }
}

// 1. Criamos um "Controller Falso" só para o teste
class FakeController {
    public function index(Request $req) {
        return "Lista de chamados";
    }
    public function show(Request $req, string $id) {
        return "Visualizando chamado ID: " . $id;
    }
}

// 2. Criamos o roteador e registramos as rotas
$router = new Router();
$router->get('/chamados', [FakeController::class, 'index']);
$router->get('/chamados/{id}', [FakeController::class, 'show']);

echo "=== TESTE 1: Rota simples sem parâmetro ===" . PHP_EOL;
// Simulamos a requisição injetando os valores no construtor
$req1 = new Request('/chamados', 'GET', []);
$resultado1 = $router->dispatch($req1);
check("Chamou o método index", $resultado1 === 'Lista de chamados');

echo PHP_EOL . "=== TESTE 2: Rota com parâmetro ===" . PHP_EOL;
$req2 = new Request('/chamados/42', 'GET', []);
$resultado2 = $router->dispatch($req2);
check("Passou o ID 42 para o controller", $resultado2 === 'Visualizando chamado ID: 42');

echo PHP_EOL . "=== TESTE 3: Rota não encontrada (404) ===" . PHP_EOL;
$req3 = new Request('/rota-que-nao-existe', 'GET', []);
$resultado3 = $router->dispatch($req3);
check("Retornou mensagem de erro", str_contains($resultado3, '404'));

echo PHP_EOL;
echo $falhas === 0 ? "=== TODOS OS TESTES PASSARAM ===" . PHP_EOL : "=== {$falhas} TESTE(S) FALHARAM ===" . PHP_EOL;
exit($falhas === 0 ? 0 : 1);
