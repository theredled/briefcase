<?php

namespace Bg\MiscBundle\Form;
use DateInterval;
use DateTime;
use Symfony\Component\Form\DataTransformerInterface;

/**
 * Created by IntelliJ IDEA.
 * User: Benoît Guchet
 * Date: 20/01/2017
 * Time: 02:33
 */
class DateIntervalViewTransformer implements DataTransformerInterface
{
    /**
     * controller > view
     * @param mixed $val
     */
    public function transform($val = null) {
        return $val ? (string)(
            $val->format('%d') * 24
            + $val->format('%i') / 60
            + $val->format('%h')) : null;
    }

    /**
     * view > controller
     * @param mixed $val
     */
    public function reverseTransform($val) {
        if (is_numeric($val)) {
            //-- Minutes
            $di = new DateInterval('PT'.intval($val * 60).'M');
            //-- Conversion en H/m
            $d1 = new DateTime();
            $d2 = clone $d1;
            $di = $d1->diff($d2->add($di));
        }
        else
            $di = null;
        return $di;
    }
}
