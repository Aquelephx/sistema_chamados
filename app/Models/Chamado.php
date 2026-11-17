<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

class Chamado extends Model
{
    public ?int $id = null;
    public string $titulo = '';
    public string $descricao = '';
    public string $prioridade = 'media';
    public ?string $categoria = null;
    public string $status = 'aberto';
    public int $solicitante_id = 0;
    public ?int $responsavel_id = null;
    public ?int $localizacao_id = null;
    public ?int $secretaria_id = null; 
    public ?string $criado_em = null;
    public ?string $atualizado_em = null;
    public ?string $assumido_em = null;
    public ?string $fechado_em = null;
    public ?int $avaliacao = null;
    public ?string $comentario_avaliacao = null; 
    public ?string $sla_pausado_em = null;
    public int $tempo_pausado_minutos = 0;
    public ?string $motivo_pausa = null;

    // Propriedades virtuais (JOIN)
    public ?string $solicitante_nome = null;
    public ?string $responsavel_nome = null; // Mantido para compatibilidade
    public ?string $localizacao_nome = null;
    public ?string $secretaria_nome = null; 
    public ?int $prazo_horas = null;

    // Propriedades para múltiplos responsáveis
    public ?string $responsaveis_ids = null;
    public ?string $responsaveis_nomes = null;

    public function getStatusLabel(): string
    {
        $labels = ['aberto' => 'Aberto', 'em_andamento' => 'Em Andamento', 'aguardando' => 'Aguardando', 'resolvido' => 'Resolvido', 'fechado' => 'Fechado'];
        return $labels[$this->status] ?? ucfirst(str_replace('_', ' ', $this->status));
    }

    public function getSlaInfo(): array
    {
        if ($this->prazo_horas === null || $this->criado_em === null) return ['cor' => 'cinza', 'texto' => 'Sem SLA', 'vencido' => false];
        if ($this->status === 'aguardando') return ['cor' => 'cinza', 'texto' => '⏸ SLA Pausado', 'vencido' => false];

        $inicio = strtotime($this->criado_em);
        $prazoSegundos = $this->prazo_horas * 3600;
        $pausaSegundos = $this->tempo_pausado_minutos * 60;
        $vencimento = $inicio + $prazoSegundos + $pausaSegundos;

        if (in_array($this->status, ['resolvido', 'fechado'])) {
            $fimStr = $this->fechado_em ?? $this->atualizado_em;
            if ($fimStr !== null) {
                $fim = strtotime($fimStr);
                if ($fim <= $vencimento) return ['cor' => 'verde', 'texto' => 'Concluído no prazo', 'vencido' => false];
                $atraso = round(($fim - $vencimento) / 3600, 1);
                return ['cor' => 'vermelho', 'texto' => "Concluído com atraso ({$atraso}h)", 'vencido' => true];
            }
        }

        $agora = time();
        $diferenca = $vencimento - $agora;
        if ($diferenca < 0) {
            $horas = floor(abs($diferenca) / 3600);
            $minutos = floor((abs($diferenca) % 3600) / 60);
            return ['cor' => 'vermelho', 'texto' => "Vencido há {$horas}h {$minutos}m", 'vencido' => true];
        }
        $horas = floor($diferenca / 3600);
        $minutos = floor(($diferenca % 3600) / 60);
        $cor = ($horas < 2) ? 'amarelo' : 'verde';
        return ['cor' => $cor, 'texto' => "Vence em {$horas}h {$minutos}m", 'vencido' => false];
    }
}
