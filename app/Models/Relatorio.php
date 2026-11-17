<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

class Relatorio extends Model
{
    public ?int $id = null;
    public int $chamado_id = 0;
    public int $usuario_id = 0;
    public ?int $localizacao_id = null;
    public string $tipo = '';
    public ?string $equipamento = null;
    public int $tempo_min = 0;
    public string $solucao = ''; // Nota interna
    public ?string $solucao_publica = null; // Mensagem para o solicitante
    public ?string $criado_em = null;
    public ?string $atualizado_em = null;

    // Propriedade virtual (vem do JOIN)
    public ?string $tecnico = null;
}