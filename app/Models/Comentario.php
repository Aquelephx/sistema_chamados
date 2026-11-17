<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

class Comentario extends Model
{
    public ?int $id = null;
    public int $chamado_id = 0;
    public int $usuario_id = 0;
    public string $texto = '';
    public ?string $criado_em = null;

    // Propriedade virtual (vem do JOIN para exibir o nome)
    public ?string $usuario_nome = null;
}
