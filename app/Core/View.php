<?php

declare(strict_types=1);

namespace App\Core;

class View
{
    /**
     * Renderiza um arquivo de view.
     * Se um layout for passado, o conteúdo da view é injetado dentro dele.
     */
    public static function render(string $view, array $data = [], ?string $layout = null): string
    {
        $viewPath = dirname(__DIR__, 2) . '/resources/views/' . str_replace('.', '/', $view) . '.php';

        if (!file_exists($viewPath)) {
            throw new \RuntimeException("View não encontrada: {$view}");
        }

        extract($data);

        // 1. Renderiza a view específica (o "miolo")
        ob_start();
        require $viewPath;
        $content = ob_get_clean();

        // 2. Se houver um layout, renderiza ele passando o miolo
        if ($layout !== null) {
            $data['content'] = $content;
            return self::render($layout, $data);
        }

        return $content;
    }
}
