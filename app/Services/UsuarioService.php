<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Result;
use App\Core\Database;
use App\Models\Usuario;
use App\Repositories\UsuarioRepository;

class UsuarioService
{
    private UsuarioRepository $repo;

    public function __construct()
    {
        $this->repo = new UsuarioRepository();
    }

    public function alterarSenha(Usuario $usuario, string $senhaAtual, string $novaSenha, string $confirmacao): Result
    {
        if (!password_verify($senhaAtual, $usuario->senha_hash)) {
            return Result::error('A senha atual está incorreta.');
        }

        if (strlen($novaSenha) < 6) {
            return Result::error('A nova senha deve ter pelo menos 6 caracteres.');
        }

        if ($novaSenha !== $confirmacao) {
            return Result::error('A nova senha e a confirmação não coincidem.');
        }

        $novoHash = password_hash($novaSenha, PASSWORD_DEFAULT);
        
        $this->repo->update($usuario->id, [
            'senha_hash' => $novoHash,
            'trocar_senha' => 0
        ]);

        // Senha nova = acessos antigos perdem a validade (outros dispositivos "lembrados").
        (new \App\Repositories\SessaoLembradaRepository())->deletarPorUsuario($usuario->id);

        return Result::ok('Senha alterada com sucesso.');
    }

    public function gerarSenhaTemporaria(int $usuarioId): Result
    {
        $usuario = $this->repo->find($usuarioId);
        if (!$usuario) {
            return Result::error('Usuário não encontrado.');
        }

        // Senha de 10 caracteres (igual o Claude fez)
        $alfabeto = 'abcdefghjkmnpqrstuvwxyzABCDEFGHJKMNPQRSTUVWXYZ23456789';
        $novaSenha = '';
        
        for ($i = 0; $i < 10; $i++) {
            $novaSenha .= $alfabeto[random_int(0, strlen($alfabeto) - 1)];
        }

        $hash = password_hash($novaSenha, PASSWORD_DEFAULT);

        // A MÁGICA QUE O CLAUDE FEZ: Transação com limpeza
        Database::transaction(function (\PDO $pdo) use ($usuarioId, $usuario, $hash) {
            // 1. Atualiza a senha e obriga a troca
            $pdo->prepare("UPDATE usuarios SET senha_hash = ?, trocar_senha = 1, pediu_senha_em = NULL WHERE id = ?")
                ->execute([$hash, $usuarioId]);

            // 2. Apaga sessões lembradas (desconecta de todos os dispositivos)
            $pdo->prepare("DELETE FROM sessoes_lembradas WHERE usuario_id = ?")
                ->execute([$usuarioId]);

            // 3. Apaga tentativas de login (desbloqueia a conta)
            $pdo->prepare("DELETE FROM tentativas_login WHERE email = ?")
                ->execute([$usuario->email]);
        });

        return Result::ok($novaSenha);
    }

    public function alterarStatus(int $usuarioId, bool $ativar): Result
    {
        $usuario = $this->repo->find($usuarioId);
        if (!$usuario) {
            return Result::error('Usuário não encontrado.');
        }

        if ($ativar) {
            $this->repo->update($usuarioId, ['ativo' => 1]);
            return Result::ok('Conta reativada.');
        } else {
            // Desativar e limpar sessões
            Database::transaction(function (\PDO $pdo) use ($usuarioId) {
                $pdo->prepare("UPDATE usuarios SET ativo = 0, pediu_senha_em = NULL WHERE id = ?")
                    ->execute([$usuarioId]);

                $pdo->prepare("DELETE FROM sessoes_lembradas WHERE usuario_id = ?")
                    ->execute([$usuarioId]);
            });
            return Result::ok('Conta desativada. A pessoa perde o acesso na hora.');
        }
    }
}
