<?php

/**
 * Created by IntelliJ IDEA.
 * User: Benoît Guchet
 * Date: 01/03/2017
 * Time: 12:00
 */

namespace Bg\MiscBundle\Doctrine;

use DateTime;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\IntegerType;


class UnixTimestampType extends IntegerType
{
    public function requiresSQLCommentHint(AbstractPlatform $platform)
    {
        return true;
    }

    public function getSqlDeclaration(array $fieldDeclaration, AbstractPlatform $platform)
    {
        return 'INT';
    }

    public function convertToPHPValue($value, AbstractPlatform $platform)
    {
        return $value ? (new DateTime())->setTimestamp($value) : null;
    }

    public function convertToDatabaseValue($value, AbstractPlatform $platform)
    {
        if ($value instanceof DateTime)
            return $value->getTimestamp();
        else
            return $value;
    }

    public function getName()
    {
        return 'unix_timestamp';
    }
}
