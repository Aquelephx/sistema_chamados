<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Request;
use App\Core\View;
use App\Core\Auth;
use App\Core\Url;

class PainelController
{
    public function index(Request $request): string
    {
        if (!Auth::check()) {
            Url::redirect('/login');
            exit;
        }

        return View::render('painel.index', [
            'usuario' => Auth::user()
        ]);
    }
}
