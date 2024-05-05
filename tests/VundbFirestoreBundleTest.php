<?php

namespace Vundb\FirestoreBundle\Tests;

use PHPUnit\Framework\TestCase;
use Vundb\FirestoreBundle\VundbFirestoreBundle;

class VundbFirestoreBundleTest extends TestCase
{
    public function testGetPath()
    {
        $bundle = new VundbFirestoreBundle();

        $this->assertSame(
            dirname(__DIR__),
            $bundle->getPath()
        );
    }
}
