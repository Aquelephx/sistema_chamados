<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

class Anexo extends Model
{
    public ?int $id = null;
    public int $chamado_id = 0;
    public int $usuario_id = 0;
    public string $nome_original = '';
    public string $mime = '';
    public int $tamanho = 0;
    public string $conteudo = '';
    public ?string $criado_em = null;

    // Virtuais
    public ?string $usuario_nome = null;
}
