<?php

/**
 * Created by PhpStorm.
 * User: Benoît Guchet
 * Date: 21/11/2018
 * Time: 01:01
 */

namespace Bg\MiscBundle\old;

use Assetic\Asset\AssetInterface;
use Symfony\Bundle\AsseticBundle\Twig\AsseticTokenParser;
use Twig_Node;

class CustomAsseticTokenParser extends AsseticTokenParser
{
    protected function createBodyNode(AssetInterface $asset, Twig_Node $body, array $inputs, array $filters, $name, array $attributes = array(), $lineno = 0, $tag = null)
    {
        return new CustomAsseticNode($asset, $body, $inputs, $filters, $name, $attributes, $lineno, $tag);
    }
}

