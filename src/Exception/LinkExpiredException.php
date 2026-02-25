<?php

/**
 * Created by PhpStorm.
 * User: Benoît Guchet
 * Date: 25/02/2026
 * Time: 15:32
 */

namespace App\Exception;

use Symfony\Component\HttpKernel\Attribute\WithHttpStatus;

#[WithHttpStatus(410)]
class LinkExpiredException extends \RuntimeException
{

}
