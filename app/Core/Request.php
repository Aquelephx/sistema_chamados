<?php

declare(strict_types=1);

namespace App\Core;

class Request
{
    public function __construct(
        private string $uri,
        private string $method,
        private array $body,
        private array $query,
        private array $files
    ) {}

    public static function capture(): self
    {
        $uri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
        $uri = preg_replace('#^/public#', '', $uri);
        $uri = preg_replace('#^/index\.php#', '', $uri);
        $uri = rtrim($uri, '/') ?: '/';
        
        return new self($uri, $_SERVER['REQUEST_METHOD'] ?? 'GET', $_POST ?? [], $_GET ?? [], $_FILES ?? []);
    }

    public function uri(): string { return $this->uri; }
    public function method(): string { return $this->method; }
    public function input(string $key, mixed $default = null): mixed { return $this->body[$key] ?? $default; }
    public function query(string $key, mixed $default = null): mixed { return $this->query[$key] ?? $default; }
    public function all(): array { return $this->body; }
    
    /**
     * Retorna uma LISTA de arquivos (para uploads múltiplos com "fotos[]").
     */
    public function files(string $key): array
    {
        if (!isset($this->files[$key])) {
            return [];
        }

        $fileData = $this->files[$key];

        // Se for upload único (name é string), transforma em array de 1 elemento
        if (!is_array($fileData['name'])) {
            return [$fileData];
        }

        // Se for upload múltiplo, reorganiza o array do PHP
        $normalized = [];
        $count = count($fileData['name']);
        for ($i = 0; $i < $count; $i++) {
            if ($fileData['error'][$i] === UPLOAD_ERR_NO_FILE) {
                continue;
            }
            $normalized[] = [
                'name'     => $fileData['name'][$i],
                'type'     => $fileData['type'][$i],
                'tmp_name' => $fileData['tmp_name'][$i],
                'error'    => $fileData['error'][$i],
                'size'     => $fileData['size'][$i]
            ];
        }

        return $normalized;
    }

    /**
     * Retorna um ÚNICO arquivo (para uploads simples, sem "[]").
     */
    public function file(string $key): ?array
    {
        $files = $this->files($key);
        return $files[0] ?? null;
    }
}
