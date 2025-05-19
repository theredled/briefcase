<?php

/**
 * Created by PhpStorm.
 * User: Benoît Guchet
 * Date: 2018-12-17
 * Time: 19:28
 */

namespace Bg\MiscBundle\Doctrine;

use Closure;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Criteria;
use Doctrine\Common\Collections\Expr\ClosureExpressionVisitor;

class BgArrayCollection extends ArrayCollection
{
    public function __construct($elements = [])
    {
        parent::__construct(is_array($elements) ? $elements : $elements->toArray());
    }

    //-- fix tri par relations
    public function matching(Criteria $criteria)
    {
        $expr     = $criteria->getWhereExpression();
        $filtered = $this->toArray();

        if ($expr) {
            $visitor  = new BgClosureExpressionVisitor();
            $filter   = $visitor->dispatch($expr);
            $filtered = array_filter($filtered, $filter);
        }

        if ($orderings = $criteria->getOrderings()) {
            $next = null;
            foreach (array_reverse($orderings) as $field => $ordering) {
                $next = self::fixedSortByField($field, $ordering == Criteria::DESC ? -1 : 1, $next);
            }

            uasort($filtered, $next);
        }

        $offset = $criteria->getFirstResult();
        $length = $criteria->getMaxResults();

        if ($offset || $length) {
            $filtered = array_slice($filtered, (int)$offset, $length);
        }

        return new static($filtered);
    }

    public static function fixedSortByField($name, $orientation = 1, Closure $next = null)
    {
        if ( ! $next) {
            $next = function($a, $b) {
                return 0;
            };
        }

        return function ($a, $b) use ($name, $next, $orientation) {
            $aValue = ClosureExpressionVisitor::getObjectFieldValue($a, $name);
            $bValue = ClosureExpressionVisitor::getObjectFieldValue($b, $name);
            if (is_object($aValue) and method_exists($aValue, 'getId'))
                $aValue = $aValue->getId();
            if (is_object($bValue) and method_exists($bValue, 'getId'))
                $bValue = $bValue->getId();

            if ($aValue === $bValue) {
                return $next($a, $b);
            }

            return (($aValue > $bValue) ? 1 : -1) * $orientation;
        };
    }

    public function containsNoStrict($element)
    {
        return in_array($element, $this->toArray(), false);
    }

}
