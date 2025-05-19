<?php

/**
 * Created by PhpStorm.
 * User: Benoît Guchet
 * Date: 24/10/2018
 * Time: 01:23
 */

namespace Bg\MiscBundle\Doctrine;

use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\Expr\ClosureExpressionVisitor;
use Doctrine\Common\Collections\Expr\Comparison;

class BgClosureExpressionVisitor extends ClosureExpressionVisitor
{
    public function walkComparison(Comparison $comparison)
    {
        //-- Le champ (Collection/array) contient-il la valeur ?
        if ($comparison instanceof EntityContainsComparison) {
            $field = $comparison->getField();
            $value = $comparison->getValue()->getValue();

            return function ($object) use ($field, $value) {
                if ($value === false)
                    return false;
                if ($value instanceof Collection)
                    $value = $value->toArray();
                elseif (!is_array($value))
                    $value = [$value];

                $fieldValue = ClosureExpressionVisitor::getObjectFieldValue($object, $field);

                if (is_array($fieldValue))
                    $fieldValue = new BgArrayCollection($fieldValue);
                elseif (!$fieldValue instanceof Collection)
                    return false;

                foreach ($value as $item)
                    if (is_object($item) ? $fieldValue->contains($item) : $fieldValue->containsNoStrict($item))
                        return true;

                return false;
            };
        }
        else
            return parent::walkComparison($comparison);
    }
}
