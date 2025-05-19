<?php

/**
 * Created by IntelliJ IDEA.
 * User: Benoît Guchet
 * Date: 01/03/2017
 * Time: 12:00
 */

namespace Bg\MiscBundle;

use Symfony\Component\HttpKernel\Bundle\AbstractBundle;

class BgMiscBundle extends AbstractBundle
{
    public function getPath(): string
    {
        return \dirname(__DIR__);
    }
}
