<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Core\Database;
use PDO;

/**
 * Acesso à tabela sessoes_lembradas (login "lembrar de mim").
 * Guarda só o HASH do validador: se alguém vazar o banco, não consegue
 * montar um cookie válido.
 */
class SessaoLembradaRepository
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::connection();
    }

    public function criar(int $usuarioId, string $seletor, string $validadorHash, string $expiraEm): void
    {
        $stmt = $this->db->prepare(
            "INSERT INTO sessoes_lembradas (usuario_id, seletor, validador_hash, expira_em)
             VALUES (:usuario_id, :seletor, :validador_hash, :expira_em)"
        );
        $stmt->execute([
            'usuario_id'     => $usuarioId,
            'seletor'        => $seletor,
            'validador_hash' => $validadorHash,
            'expira_em'      => $expiraEm,
        ]);
    }

    public function buscarPorSeletor(string $seletor): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM sessoes_lembradas WHERE seletor = :seletor LIMIT 1");
        $stmt->execute(['seletor' => $seletor]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function deletarPorSeletor(string $seletor): void
    {
        $this->db->prepare("DELETE FROM sessoes_lembradas WHERE seletor = :seletor")
                 ->execute(['seletor' => $seletor]);
    }

    public function deletarPorUsuario(int $usuarioId): void
    {
        $this->db->prepare("DELETE FROM sessoes_lembradas WHERE usuario_id = :id")
                 ->execute(['id' => $usuarioId]);
    }

    /** Limpa o lixo: registros já vencidos (a data vem do PHP para usar o mesmo fuso do resto do sistema). */
    public function limparExpiradas(string $agora): void
    {
        $this->db->prepare("DELETE FROM sessoes_lembradas WHERE expira_em < :agora")
                 ->execute(['agora' => $agora]);
    }
}
