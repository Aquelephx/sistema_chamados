<?php

declare(strict_types=1);

namespace App\Repositories;

class TentativaLoginRepository extends AbstractRepository
{
    protected string $table = 'tentativas_login';
    protected string $entityClass = \App\Core\Model::class;
    protected array $fillable = ['email', 'ip']; 

    public function contarPorIp(string $marcador, string $ip, int $minutos): int
    {
        $sql = "SELECT COUNT(*) FROM {$this->table} 
                WHERE email = :marcador AND ip = :ip 
                AND criado_em > DATE_SUB(NOW(), INTERVAL :minutos MINUTE)";
        $stmt = $this->db->prepare($sql);
        // PDO não aceita bindValue com INTERVAL direto, então limpamos e interpolamos (seguro, pois é int)
        $sql = str_replace(':minutos', (string)$minutos, $sql);
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['marcador' => $marcador, 'ip' => $ip]);
        return (int) $stmt->fetchColumn();
    }

    public function registrar(string $marcador, string $ip): void
    {
        $this->create([
            'email' => $marcador,
            'ip' => $ip
           
        ]);
    }
}