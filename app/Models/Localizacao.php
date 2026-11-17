<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

class Localizacao extends Model
{
    public ?int $id = null;
    public ?int $secretaria_id = null;
    public string $nome = '';
    public int $ativo = 1;
    
    // Virtual (JOIN)
    public ?string $secretaria_nome = null;
}
