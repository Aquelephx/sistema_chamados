<?php

declare(strict_types=1);

require __DIR__ . '/../../bootstrap/app.php';

use App\Core\Database;

$pdo = Database::connection();
$pdo->exec("DROP TEMPORARY TABLE IF EXISTS _test_tx");
$pdo->exec("CREATE TEMPORARY TABLE _test_tx (id INT PRIMARY KEY, nome VARCHAR(50))");

$falhas = 0;

function check(string $label, bool $ok): void
{
    global $falhas;
    echo ($ok ? "[OK]   " : "[FALHA]") . " {$label}\n";
    if (!$ok) {
        $falhas++;
    }
}

echo "=== TESTE 1: commit ===\n";
$retorno = Database::transaction(function (\PDO $pdo) {
    $pdo->exec("INSERT INTO _test_tx (id, nome) VALUES (1, 'commit-ok')");
    return 'valor-retornado';
});
$count = (int) $pdo->query("SELECT COUNT(*) FROM _test_tx")->fetchColumn();
check("retorno do callback preservado", $retorno === 'valor-retornado');
check("linha persistida apos commit", $count === 1);

echo "\n=== TESTE 2: rollback com Exception ===\n";
try {
    Database::transaction(function (\PDO $pdo) {
        $pdo->exec("INSERT INTO _test_tx (id, nome) VALUES (2, 'nao-persistir')");
        throw new \RuntimeException('erro proposital');
    });
    check("excecao deveria ter propagado", false);
} catch (\RuntimeException $e) {
    check("RuntimeException propagada", $e->getMessage() === 'erro proposital');
}
$count = (int) $pdo->query("SELECT COUNT(*) FROM _test_tx")->fetchColumn();
check("linha nao persistida apos rollback", $count === 1);

echo "\n=== TESTE 3: aninhamento bloqueado ===\n";
try {
    Database::transaction(function (\PDO $pdo) {
        Database::transaction(function (\PDO $pdo2) {});
    });
    check("LogicException deveria ter sido lancada", false);
} catch (\LogicException $e) {
    check("LogicException capturada", true);
    check(
        "mensagem correta",
        stripos($e->getMessage(), 'aninhamento') !== false
    );
}
check("nenhuma transacao ficou aberta apos o erro", $pdo->inTransaction() === false);

echo "\n=== TESTE 4: Error (nao Exception) tambem faz rollback ===\n";
try {
    Database::transaction(function (\PDO $pdo) {
        $pdo->exec("INSERT INTO _test_tx (id, nome) VALUES (3, 'nao-persistir')");
        throw new \TypeError('erro de tipo proposital');
    });
    check("TypeError deveria ter propagado", false);
} catch (\TypeError $e) {
    check("TypeError propagado", $e->getMessage() === 'erro de tipo proposital');
}
$count = (int) $pdo->query("SELECT COUNT(*) FROM _test_tx")->fetchColumn();
check("linha nao persistida apos TypeError", $count === 1);

echo "\n=== TESTE 5: retorno null funciona ===\n";
$retorno = Database::transaction(fn(\PDO $pdo) => null);
check("callback que retorna null nao quebra", $retorno === null);

echo "\n";
echo $falhas === 0
    ? "=== TODOS OS TESTES PASSARAM ===\n"
    : "=== {$falhas} TESTE(S) FALHARAM ===\n";

exit($falhas === 0 ? 0 : 1);
