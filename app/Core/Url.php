<?php

declare(strict_types=1);

namespace App\Core;

class Url
{
    public static function to(string $path): string
    {
        $basePath = $_ENV['APP_BASE_PATH'] ?? '';
        $path = '/' . ltrim($path, '/');
        return $basePath . $path;
    }

    public static function redirect(string $path): void
    {
        // O código 303 (See Other) força o navegador a usar GET e limpa o histórico do POST
        header('Location: ' . self::to($path), true, 303);
        exit;
    }
}
