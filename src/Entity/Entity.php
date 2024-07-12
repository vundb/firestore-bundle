<?php

namespace Vundb\FirestoreBundle\Entity;

/**
 * @template T
 * @method string getId()
 * @method T setId(string $value)
 */
abstract class Entity extends Struct
{
    protected string $id = '';

    /**
     * @deprecated Use the Struct::jsonSerialize() method instead.
     * @return array
     */
    public function toArray(): array
    {
        return $this->jsonSerialize();
    }
}
