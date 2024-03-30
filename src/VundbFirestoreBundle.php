<?php

namespace Vundb;

use Symfony\Component\HttpKernel\Bundle\AbstractBundle;

class VundbFirestoreBundle extends AbstractBundle
{
    public function getPath(): string
    {
        return dirname(__DIR__);
    }
}
