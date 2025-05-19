<?php

/**
 * Created by IntelliJ IDEA.
 * User: Benoît Guchet
 * Date: 01/03/2017
 * Time: 12:00
 */
namespace Bg\MiscBundle\Doctrine;

class UnixTimestampDateType extends UnixTimestampType
{
    public function getName()
    {
        return 'unix_timestamp_date'; // modify to match your constant name
    }
}
