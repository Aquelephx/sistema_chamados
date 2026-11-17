<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Result;
use App\Repositories\UsuarioRepository;
use App\Repositories\TentativaLoginRepository;

class AuthService
{
    private TentativaLoginRepository $tentativas;

    public function __construct(
        private UsuarioRepository $usuarios
    ) {
        $this->tentativas = new TentativaLoginRepository();
    }

    public function registrar(string $nome, string $email, string $senha, string $codigoEmpresa, ?int $secretariaId = null, ?int $localizacaoId = null): Result
    {
        $codigoCorreto = $_ENV['APP_EMPRESA_CODIGO'] ?? 'SEAD2026';
        if ($codigoEmpresa !== $codigoCorreto) {
            return Result::error('Código da empresa inválido.');
        }

        if ($this->usuarios->findByEmail($email) !== null) {
            return Result::error('Este e-mail já está cadastrado.');
        }

        if (strlen($senha) < 8) {
    return Result::error('A senha deve ter pelo menos 8 caracteres.');
}
if (!preg_match('/[A-Z]/', $senha) || !preg_match('/[a-z]/', $senha) || !preg_match('/[0-9]/', $senha)) {
    return Result::error('A senha deve conter pelo menos uma letra maiúscula, uma minúscula e um número.');
}

        $senhaHash = password_hash($senha, PASSWORD_DEFAULT);
        $this->usuarios->create([
            'nome' => $nome,
            'email' => $email,
            'senha_hash' => $senhaHash,
            'secretaria_id' => $secretariaId,
            'localizacao_id' => $localizacaoId,
            'perfil' => 'solicitante',
            'ativo' => 1
        ]);
      

        return Result::ok('Conta criada.');
    }

    public function login(string $email, string $senha, string $ip): Result
    {
        if ($this->tentativas->contarPorIp($email, $ip, 15) >= 5) {
            return Result::error('Muitas tentativas incorretas. Aguarde 15 minutos e tente novamente.');
        }

        $usuario = $this->usuarios->findByEmail($email);

        if ($usuario === null || $usuario->ativo !== 1 || !password_verify($senha, $usuario->senha_hash)) {
            $this->tentativas->registrar($email, $ip);
            return Result::error('E-mail ou senha incorretos.');
        }

        $this->limparTentativas($email, $ip);
        return Result::ok($usuario);
    }

    public function pedirNovaSenha(string $email, string $ip): Result
    {
        if ($this->tentativas->contarPorIp('__esqueci__', $ip, 15) >= 5) {
            return Result::error('Muitos pedidos. Aguarde 15 minutos.');
        }

        $this->tentativas->registrar('__esqueci__', $ip);

        $email = strtolower(trim($email));
        if ($email !== '') {
            $usuario = $this->usuarios->findByEmail($email);
            if ($usuario && $usuario->perfil !== 'painel' && $usuario->ativo === 1) {
                $this->usuarios->update($usuario->id, ['pediu_senha_em' => date('Y-m-d H:i:s')]);
            }
        }

        return Result::ok('Pedido registrado. Procure o administrador do sistema para receber uma senha temporária.');
    }

    private function limparTentativas(string $email, string $ip): void
    {
        $pdo = \App\Core\Database::connection();
        $pdo->prepare("DELETE FROM tentativas_login WHERE email = :email AND ip = :ip")->execute(['email' => $email, 'ip' => $ip]);
    }
}
