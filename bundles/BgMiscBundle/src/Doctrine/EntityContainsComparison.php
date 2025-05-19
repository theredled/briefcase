<?php

/**
 * Created by PhpStorm.
 * User: Benoît Guchet
 * Date: 24/10/2018
 * Time: 00:18
 */

namespace Bg\MiscBundle\Doctrine;

use Doctrine\Common\Collections\Expr\Comparison;

//-- Le champ (Collection/array) contient-il la valeur ?

class EntityContainsComparison extends Comparison
{
    public function __construct($field, $value)
    {
        parent::__construct($field, null, $value);
    }
}
