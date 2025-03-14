<?php

namespace Vundb\FirestoreBundle\Entity;

abstract class Struct implements \JsonSerializable
{
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

    public function jsonSerialize(): mixed
    {
        $array = [];
        $reflectionClass = new \ReflectionClass($this);

        foreach ($reflectionClass->getProperties() as $property) {
            $propertyName = $property->getName();
            if ($this->$propertyName instanceof Struct) {
                $array[$propertyName] = $this->$propertyName->jsonSerialize();
            } else {
                $array[$propertyName] = $this->$propertyName;
            }
        }

        return $array;
    }
}
