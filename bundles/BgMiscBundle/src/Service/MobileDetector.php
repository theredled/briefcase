<?php

/**
 * Created by PhpStorm.
 * User: Benoît Guchet
 * Date: 01/12/2020
 * Time: 16:42
 */

namespace Bg\MiscBundle\Service;

use Symfony\Component\HttpFoundation\RequestStack;

final class MobileDetector
{
    public function __construct(private RequestStack $requestStack)
    {
    }

    public function isMobile()
    {
        $request = $this->requestStack->getCurrentRequest();
        $md = new \Mobile_Detect($request->headers->all(), $request->headers->get('User-Agent'));

        return $md->isMobile() && !$md->isTablet();
    }
}
