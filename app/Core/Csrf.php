<?php

declare(strict_types=1);

namespace App\Core;

class Csrf
{
    /**
     * Gera um token (se não existir) e retorna.
     */
    public static function token(): string
    {
        Session::iniciar();
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }

    /**
     * Retorna o campo input HTML oculto para colocar nos formulários.
     */
    public static function field(): string
    {
        $token = self::token();
        return '<input type="hidden" name="_token" value="' . htmlspecialchars($token) . '">';
    }

    /**
     * Verifica se o token enviado é válido.
     */
    public static function validate(?string $token): bool
    {
        Session::iniciar();
        return !empty($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token ?? '');
    }
}
