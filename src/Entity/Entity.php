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
     * @return array
     */
    public function toArray(): array
    {
        $array = [];
        $reflectionClass = new \ReflectionClass($this);

        foreach ($reflectionClass->getProperties() as $property) {
            $propertyName = $property->getName();
            $array[$propertyName] = $this->$propertyName;
        }

        return $array;
    }
}
