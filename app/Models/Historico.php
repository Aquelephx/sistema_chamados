<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

class Historico extends Model
{
    public ?int $id = null;
    public int $chamado_id = 0;
    public ?int $usuario_id = null;
    public string $texto = '';
    public ?string $criado_em = null;

    public ?string $usuario_nome = null;
}
