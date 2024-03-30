<?php

namespace Vundb\FirestoreBundle;

use Symfony\Component\HttpKernel\Bundle\AbstractBundle;

class VundbFirestoreBundle extends AbstractBundle
{
    public function getPath(): string
    {
        return dirname(__DIR__);
    }
}
