<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

class Usuario extends Model
{
    public ?int $id = null;
    public string $nome = '';
    public string $email = '';
    public ?int $secretaria_id = null;
    public ?int $localizacao_id = null;
    public string $senha_hash = '';
    public string $perfil = 'solicitante';
    public int $trocar_senha = 0;
    public ?string $pediu_senha_em = null;
    public int $ativo = 1;
    public ?string $criado_em = null;
    
    public ?string $foto = null;
    public ?string $bio = null;

    // Propriedades virtuais (para quando fizermos JOIN nas secretarias e locais)
    public ?string $secretaria_nome = null;
    public ?string $localizacao_nome = null;

    public function inicial(): string
    {
        $nome = trim($this->nome);
        if ($nome === '') {
            return '?';
        }
        return mb_strtoupper(mb_substr($nome, 0, 1));
    }
}
