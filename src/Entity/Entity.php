<?php

namespace Vundb\FirestoreBundle\Entity;

use Symfony\Component\PropertyAccess\PropertyAccess;

/**
 * @template T
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
     * @return T
     */
    public function setId(string $id): self
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        $propertyAccessor = PropertyAccess::createPropertyAccessor();
        $array = [];
        $reflectionClass = new \ReflectionClass($this);

        while ($reflectionClass) {
            $properties = $reflectionClass->getProperties(\ReflectionProperty::IS_PRIVATE | \ReflectionProperty::IS_PROTECTED | \ReflectionProperty::IS_PUBLIC);

            foreach ($properties as $property) {
                $name = $property->getName();
                $getter = 'get' . ucfirst($name);

                if (method_exists($this, $getter)) {
                    $array[$name] = $propertyAccessor->getValue($this, $name);
                }
            }

            $reflectionClass = $reflectionClass->getParentClass();
        }

        return $array;
    }
}
