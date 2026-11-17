<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

class Secretaria extends Model
{
    public ?int $id = null;
    public string $nome = '';
    public int $ativo = 1;
}
