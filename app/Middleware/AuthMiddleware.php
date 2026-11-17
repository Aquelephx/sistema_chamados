<?php

declare(strict_types=1);

namespace App\Middleware;

use App\Core\Auth;
use App\Core\Url;

class AuthMiddleware
{
    /**
     * Executa a verificação. Se falhar, redireciona e para a execução.
     */
    public function handle(): void
    {
        if (!Auth::check()) {
            Url::redirect('/login');
        }
    }
}
