<?php

namespace Vundb\FirestoreBundle\Entity;

use DateTime;

/**
 * @template TEntity
 */
abstract class Entity
{
    private string $id = '';

    /**
     * @return string
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * @param string $id
     * @return TEntity
     */
    public function setId(string $id): self
    {
        $this->id = $id;
        return $this;
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id
        ];
    }
}
