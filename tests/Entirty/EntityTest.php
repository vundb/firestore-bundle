<?php

namespace Vundb\FirestoreBundle\Tests\Entity;

use PHPUnit\Framework\TestCase;
use Vundb\FirestoreBundle\Entity\Entity;

class EntityTest extends TestCase
{
    public function testGetAndSetId()
    {
        $id = random_bytes(8);

        $entity = new TestEntity();
        $this->assertSame('', $entity->getId());

        $entity->setId($id);
        $this->assertSame($id, $entity->getId());
    }

    public function testToArray()
    {
        $id = random_bytes(8);
        $entity = (new TestEntity())
            ->setId($id);

        $this->assertSame([
            'id' => $id
        ], $entity->toArray());
    }
}

class TestEntity extends Entity
{
}
