<?php

namespace Vundb\FirestoreBundle\Tests\Entity;

use PHPUnit\Framework\TestCase;
use Vundb\FirestoreBundle\Entity\Struct;

class StructTest extends TestCase
{
    public function testDynamicGetterAndSetter()
    {
        $struct = new TestStruct();
        $name = random_bytes(8);
        $purpose = random_bytes(8);

        $this->assertSame('', $struct->getName());
        $this->assertNull($struct->getPurpose());

        $this->assertSame($struct, $struct->setName($name));
        $this->assertSame($name, $struct->getName());

        $this->assertSame($struct, $struct->setPurpose($purpose));
        $this->assertSame($purpose, $struct->getPurpose());
        $this->assertSame($struct, $struct->setPurpose(null));
        $this->assertNull($struct->getPurpose());
    }

    /**
     * Expect and exception when getter are used for an not existing property.
     */
    public function testUndefinedGetterMethods()
    {
        $struct = new TestStruct();

        $this->expectException(\BadMethodCallException::class);
        $struct->getUnknown();
    }

    /**
     * Expect and exception when setter are used for an not existing property.
     */
    public function testUndefinedSetterMethods()
    {
        $struct = new TestStruct();

        $this->expectException(\BadMethodCallException::class);
        $struct->setUnknown();
    }

    /**
     * Ensure custom added getter and setter method are priortized first.
     */
    public function testCustomSetFunctionCall()
    {
        $struct = new TestStruct();

        $this->assertSame('custom', $struct->getCustom());
        $this->assertSame($struct, $struct->setCustom('no-custom'));
        $this->assertSame('custom', $struct->getCustom());
    }

    /**
     * Ensure public methods are still caled as usual.
     */
    public function testPublicMethodsAreCalled()
    {
        $struct = new TestStruct();

        $this->assertEquals(['ok'], $struct->doSomething());
    }

    /**
     * Still throw an exception when unknown method is called.
     */
    public function testUndefinedMethodCall()
    {
        $entity = new TestStruct();

        $this->expectException(\BadMethodCallException::class);
        $entity->undefinedMethodCall();
    }

    public function testSerialization()
    {
        $id = random_bytes(8);
        $name = random_bytes(8);
        $created = new \DateTime();
        $entity = (new SerializedStruct())
            ->setId($id)
            ->setName($name)
            ->setCreated($created);

        $this->assertArrayIsEqualToArrayOnlyConsideringListOfKeys(
            [
                'id' => $id,
                'name' => $name,
                'created' => $created
            ],
            $entity->jsonSerialize(),
            ['id', 'name', 'created']
        );
    }
}

/**
 * @method string getName()
 * @method ?string getPurpose();
 * @method self setName(string $value)
 * @method self setPurpose(?string $value)
 */
class TestStruct extends Struct
{
    protected string $name = '';
    protected ?string $purpose = null;
    protected ?string $custom = null;

    public function getCustom(): string
    {
        return 'custom';
    }

    public function setCustom(?string $value): self
    {
        return $this;
    }

    public function doSomething(): array
    {
        return ['ok'];
    }
}

/**
 * @method string getId()
 * @method string getName()
 * @method ?\DateTime getCreated()
 * @method self setId(string $id)
 * @method self setName(string $name)
 * @method self setCreated(\DateTime $created)
 */
class SerializedStruct extends Struct
{
    protected string $id = '';
    protected string $name = '';
    protected ?\DateTime $created = null;
}
