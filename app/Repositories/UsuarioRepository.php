<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\Usuario;

class UsuarioRepository extends AbstractRepository
{
    protected string $table = 'usuarios';
    protected string $entityClass = Usuario::class;
    
    protected array $fillable = [
        'nome',
        'email',
        'secretaria_id',
        'localizacao_id',
        'senha_hash',
        'perfil',
        'trocar_senha',
        'ativo',
        'pediu_senha_em',
        'foto',
        'bio'
    ];

    public function findByEmail(string $email): ?Usuario
    {
        $sql = "SELECT * FROM {$this->table} WHERE email = :email LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['email' => $email]);
        
        $row = $stmt->fetch();
        if (!$row) return null;
        return (new Usuario())->fromArray($row);
    }

    public function countAll(): int
    {
        return (int) $this->db->query("SELECT COUNT(*) FROM {$this->table}")->fetchColumn();
    }

    public function getAllPaginated(int $limit, int $offset): array
    {
        $sql = "SELECT * FROM {$this->table} ORDER BY ativo DESC, nome ASC LIMIT {$limit} OFFSET {$offset}";
        $stmt = $this->db->query($sql);
        $rows = $stmt->fetchAll();

        $usuarios = [];
        foreach ($rows as $row) {
            $usuarios[] = (new Usuario())->fromArray($row);
        }
        return $usuarios;
    }
}
