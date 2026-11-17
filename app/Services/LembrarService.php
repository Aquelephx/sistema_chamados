<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Session;
use App\Repositories\SessaoLembradaRepository;
use App\Repositories\UsuarioRepository;

/**
 * "Manter conectado por 30 dias" (padrão seletor + validador).
 *
 * O cookie tem duas partes:  seletor : validador
 *  - seletor   -> identifica a linha no banco (pode ser público, é só um "endereço")
 *  - validador -> o segredo. No banco guardamos apenas o SHA-256 dele.
 *
 * Por que não guardar o id do usuário (ou a senha) no cookie? Porque quem
 * copiasse o cookie viraria aquele usuário para sempre. Assim o cookie vale só
 * por 30 dias, pode ser revogado no banco e não revela nada sobre o usuário.
 */
class LembrarService
{
    public const COOKIE = 'lembrar_me';
    public const DIAS = 30;

    private SessaoLembradaRepository $repo;

    public function __construct()
    {
        $this->repo = new SessaoLembradaRepository();
    }

    /** Cria o registro no banco e entrega o cookie ao navegador. */
    public function lembrar(int $usuarioId): void
    {
        $agora = time();
        $this->repo->limparExpiradas(date('Y-m-d H:i:s', $agora));

        $seletor   = bin2hex(random_bytes(16)); // 32 caracteres
        $validador = bin2hex(random_bytes(32)); // 64 caracteres
        $expira    = $agora + self::DIAS * 86400;

        $this->repo->criar($usuarioId, $seletor, hash('sha256', $validador), date('Y-m-d H:i:s', $expira));
        $this->enviarCookie($seletor . ':' . $validador, $expira);
    }

    /**
     * Chamado quando NÃO há sessão ativa. Se o cookie for válido, reabre a sessão.
     * Retorna true se conseguiu logar o usuário.
     */
    public function restaurarSessao(): bool
    {
        $partes = $this->lerCookie();
        if ($partes === null) {
            return false;
        }
        [$seletor, $validador] = $partes;

        $registro = $this->repo->buscarPorSeletor($seletor);
        if ($registro === null) {
            $this->apagarCookie();
            return false;
        }

        // Vencido ou validador errado (cookie adulterado): apaga e não loga.
        $vencido = strtotime((string) $registro['expira_em']) < time();
        $confere = hash_equals((string) $registro['validador_hash'], hash('sha256', $validador));
        if ($vencido || !$confere) {
            $this->repo->deletarPorSeletor($seletor);
            $this->apagarCookie();
            return false;
        }

        // Conta desativada nesse meio tempo? Não entra.
        $usuario = (new UsuarioRepository())->find((int) $registro['usuario_id']);
        if ($usuario === null || $usuario->ativo !== 1) {
            $this->repo->deletarPorSeletor($seletor);
            $this->apagarCookie();
            return false;
        }

        Session::login($usuario->id); // também regenera o ID da sessão
        return true;
    }

    /** Logout deste navegador: remove o registro do banco e o cookie. */
    public function esquecer(): void
    {
        $partes = $this->lerCookie();
        if ($partes !== null) {
            $this->repo->deletarPorSeletor($partes[0]);
        }
        $this->apagarCookie();
    }

    /** Desconecta TODOS os dispositivos do usuário (ex.: ao trocar a senha). */
    public function esquecerTodos(int $usuarioId): void
    {
        $this->repo->deletarPorUsuario($usuarioId);
    }

    // ---------------------------------------------------------------

    /** @return array{0:string,1:string}|null */
    private function lerCookie(): ?array
    {
        $valor = $_COOKIE[self::COOKIE] ?? '';
        // Valida o formato antes de tocar no banco.
        if (!is_string($valor) || !preg_match('/^([a-f0-9]{32}):([a-f0-9]{64})$/', $valor, $m)) {
            return null;
        }
        return [$m[1], $m[2]];
    }

    private function enviarCookie(string $valor, int $expira): void
    {
        if (headers_sent()) {
            return;
        }
        setcookie(self::COOKIE, $valor, [
            'expires'  => $expira,
            'path'     => '/',
            'secure'   => $this->https(),
            'httponly' => true,   // JavaScript não consegue ler (protege contra XSS)
            'samesite' => 'Lax',
        ]);
        $_COOKIE[self::COOKIE] = $valor;
    }

    private function apagarCookie(): void
    {
        if (!headers_sent()) {
            setcookie(self::COOKIE, '', [
                'expires'  => time() - 3600,
                'path'     => '/',
                'secure'   => $this->https(),
                'httponly' => true,
                'samesite' => 'Lax',
            ]);
        }
        unset($_COOKIE[self::COOKIE]);
    }

    private function https(): bool
    {
        return (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
            || (int) ($_SERVER['SERVER_PORT'] ?? 0) === 443;
    }
}
