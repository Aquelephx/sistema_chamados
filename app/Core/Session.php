<?php

declare(strict_types=1);

namespace App\Core;

class Session
{
    public static function iniciar(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            if (!headers_sent()) {
                session_set_cookie_params([
                    'lifetime' => 0,
                    'path' => '/',
                    'domain' => '',
                    'secure' => false, // Mude para true quando tiver HTTPS
                    'httponly' => true,
                    'samesite' => 'Lax'
                ]);
            }
            session_start();
        }
    }

    public static function login(int $usuarioId): void
    {
        self::iniciar();
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_regenerate_id(true);
        }
        $_SESSION['usuario_id'] = $usuarioId;
        $_SESSION['ultima_atividade'] = time(); // Registra a hora do login
    }

    public static function usuarioId(): ?int
    {
        self::iniciar();
        return isset($_SESSION['usuario_id']) ? (int) $_SESSION['usuario_id'] : null;
    }

    public static function logout(): void
    {
        self::iniciar();
        $_SESSION = [];

        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000, $params["path"], $params["domain"], $params["secure"], $params["httponly"]);
        }
        session_destroy();
    }

    // TRAVA DE SEGURANÇA: Inatividade
    public static function verificarInatividade(int $timeoutSegundos = 1800): void // 1800s = 30 minutos
    {
        self::iniciar();
        if (isset($_SESSION['usuario_id'])) {
            if (isset($_SESSION['ultima_atividade']) && (time() - $_SESSION['ultima_atividade'] > $timeoutSegundos)) {
                self::logout();
                self::flash('erro', 'Sua sessão expirou por inatividade. Por favor, faça login novamente.');
                Url::redirect('/login');
                exit;
            }
            $_SESSION['ultima_atividade'] = time(); // Atualiza o relógio a cada clique
        }
    }

    public static function flash(string $tipo, string $mensagem): void
    {
        self::iniciar();
        $_SESSION['flash'][$tipo] = $mensagem;
    }

    public static function getFlash(string $tipo): ?string
    {
        self::iniciar();
        if (isset($_SESSION['flash'][$tipo])) {
            $msg = $_SESSION['flash'][$tipo];
            unset($_SESSION['flash'][$tipo]);
            return $msg;
        }
        return null;
    }
}