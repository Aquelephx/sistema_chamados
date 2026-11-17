<?php

declare(strict_types=1);

namespace App\Core;

class Asset
{
    /**
     * Gera a URL do arquivo com uma versão baseada na data de modificação.
     */
    public static function url(string $path): string
    {
        // Garante que começa com barra
        $path = '/' . ltrim($path, '/');
        
        // Caminho físico real do arquivo na pasta public
        $physicalPath = dirname(__DIR__, 2) . '/public' . $path;
        
        // Pega a data de modificação do arquivo. Se não existir, usa o tempo atual.
        $version = file_exists($physicalPath) ? filemtime($physicalPath) : time();
        
        return $path . '?v=' . $version;
    }
}
