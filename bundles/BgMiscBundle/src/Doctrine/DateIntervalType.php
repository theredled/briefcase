<?php

/**
 * Created by IntelliJ IDEA.
 * User: Benoît Guchet
 * Date: 01/03/2017
 * Time: 12:00
 */

namespace Bg\MiscBundle\Doctrine;

use DateInterval;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\ConversionException;
use Doctrine\DBAL\Types\Type;
use Exception;

/**
 * Tiré de Doctrine DBAL 2.6.0-DEV
 *
 * Type that maps interval string to a PHP DateInterval Object.
 */
class DateIntervalType extends Type
{
    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'qs_dateinterval';
    }

    /**
     * {@inheritdoc}
     */
    public function getSQLDeclaration(array $fieldDeclaration, AbstractPlatform $platform)
    {
        $fieldDeclaration['length'] = 20;
        $fieldDeclaration['fixed']  = true;
        return $platform->getVarcharTypeDeclarationSQL($fieldDeclaration);
    }
    /**
     * {@inheritdoc}
     */
    public function convertToDatabaseValue($value, AbstractPlatform $platform)
    {
        if (null === $value) {
            return null;
        }
        if ($value instanceof DateInterval) {
            return 'P'
                . str_pad($value->y, 4, '0', STR_PAD_LEFT) . '-'
                . $value->format('%M-%DT%H:%I:%S');
        }
        throw ConversionException::conversionFailed($value, __CLASS__);
    }
    /**
     * {@inheritdoc}
     */
    public function convertToPHPValue($value, AbstractPlatform $platform)
    {
        if ($value === null || $value instanceof DateInterval) {
            return $value;
        }
        try {
            return new DateInterval($value);
        } catch (Exception $exception) {
            throw ConversionException::conversionFailed($value, __CLASS__);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function requiresSQLCommentHint(AbstractPlatform $platform)
    {
        return true;
    }
}