<?php

declare(strict_types=1);

namespace App\Core;

use App\Models\Usuario;
use App\Repositories\UsuarioRepository;

class Auth
{
    private static ?Usuario $usuarioLogado = null;
    private static bool $buscou = false;

    /**
     * Retorna o usuário logado (ou null).
     * Usa cache em memória para não buscar no banco 2x na mesma requisição.
     */
    public static function user(): ?Usuario
    {
        if (self::$buscou) {
            return self::$usuarioLogado;
        }
        self::$buscou = true;

        $id = Session::usuarioId();
        if ($id === null) {
            return null;
        }

        $repo = new UsuarioRepository();
        $usuario = $repo->find($id);

        // Se o usuário foi desativado no meio da sessão, derruba o login.
        if ($usuario === null || $usuario->ativo !== 1) {
            Session::logout();
            return null;
        }

        self::$usuarioLogado = $usuario;
        return $usuario;
    }

    public static function check(): bool
    {
        return self::user() !== null;
    }

    public static function id(): ?int
    {
        $u = self::user();
        return $u?->id;
    }

    public static function isAdmin(): bool
    {
        $u = self::user();
        return $u?->perfil === 'admin';
    }

    public static function isTecnico(): bool
    {
        $u = self::user();
        return $u?->perfil === 'tecnico';
    }
}
