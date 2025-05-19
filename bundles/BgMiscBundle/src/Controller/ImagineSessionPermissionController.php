<?php

/**
 * Created by PhpStorm.
 * User: Benoît Guchet
 * Date: 2019-01-04
 * Time: 01:10
 */

namespace Bg\MiscBundle\Controller;
use Bg\MiscBundle\LiipImagine\BgCacheManager;
use Bg\MiscBundle\LiipImagine\SessionPermissionResolver;
use Liip\ImagineBundle\Controller\ImagineController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class ImagineSessionPermissionController extends ImagineController
{
    /** @var BgCacheManager */
    protected $cacheManager;

    public function setCacheManager($cacheManager) {
        $this->cacheManager = $cacheManager;
    }

    public function filterAction(Request $request, $path, $filter)
    {
        $resolver = $this->cacheManager->getResolver($filter, $request->get('resolver'));

        if ($resolver instanceof SessionPermissionResolver) {
            $file = $resolver->getRelFilePath($path, $filter);

            if (!$resolver->isFileOk($file, $request->get('hash'), $request->getSession()))
                throw new AccessDeniedException();
        }

        return parent::filterAction($request, $path, $filter);
    }
}
