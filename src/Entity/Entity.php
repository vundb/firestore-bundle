<?php

namespace Vundb\FirestoreBundle\Entity;

/**
 * @template T
 * @method string getId()
 * @method T setId(string $value)
 */
abstract class Entity
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

    public function __call($method, $arguments)
    {
        if (method_exists($this, $method)) {
            return $this->$method(...$arguments);
        } elseif (str_starts_with($method, 'get')) {
            $propertyName = lcfirst(substr($method, 3));

            if (property_exists($this, $propertyName)) {
                return $this->$propertyName;
            } else {
                throw new \BadMethodCallException('Call to undefined getter method ' . static::class . '::' . $method . '()');
            }
        } elseif (str_starts_with($method, 'set')) {
            $propertyName = lcfirst(substr($method, 3));

            if (property_exists($this, $propertyName)) {
                $this->$propertyName = $arguments[0] ?? null;
                return $this;
            } else {
                throw new \BadMethodCallException('Call to undefined setter method ' . static::class . '::' . $method . '()');
            }
        } else {
            throw new \BadMethodCallException('Call to undefined method ' . static::class . '::' . $method . '()');
        }
    }
}
