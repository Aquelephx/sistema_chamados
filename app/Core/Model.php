<?php

declare(strict_types=1);

namespace App\Core;

abstract class Model
{
    /**
     * Preenche a entidade a partir de um array (geralmente vindo do banco).
     * 
     * @param array $data
     * @return static
     * @throws \InvalidArgumentException Se o array tiver colunas desconhecidas.
     */
    public function fromArray(array $data): static
    {
        foreach ($data as $key => $value) {
            if (!property_exists($this, $key)) {
                throw new \InvalidArgumentException(
                    "Coluna desconhecida na entidade " . static::class . ": {$key}"
                );
            }

            $this->$key = $value;
        }

        return $this;
    }

    /**
     * Transforma a entidade em um array associativo.
     * 
     * @return array
     */
    public function toArray(): array
    {
        return get_object_vars($this);
    }
}