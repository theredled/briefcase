<?php

/**
 * Created by PhpStorm.
 * User: Benoît Guchet
 * Date: 2019-01-04
 * Time: 00:17
 */

namespace Bg\MiscBundle\LiipImagine;
use Liip\ImagineBundle\Imagine\Cache\CacheManager;
use Liip\ImagineBundle\Imagine\Cache\SignerInterface;
use Liip\ImagineBundle\Imagine\Filter\FilterConfiguration;
use Liip\ImagineBundle\Service\FilterService;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;

class BgCacheManager extends CacheManager
{
    protected $filterRoute;
    /** @var FilterService */
    protected $filterService;

    public function __construct(
        FilterConfiguration $filterConfig,
        RouterInterface $router,
        SignerInterface $signer,
        EventDispatcherInterface $dispatcher,
        $defaultResolver = null
    ) {
        parent::__construct($filterConfig, $router, $signer, $dispatcher, $defaultResolver);
    }

    /**
     * @return FilterConfiguration
     */
    public function getFilterConfig() {
        return $this->filterConfig;
    }

    public function generateUrl($path, $filter, array $runtimeConfig = [], $resolver = null,
        $referenceType = UrlGeneratorInterface::ABSOLUTE_URL)
    {
        $resolverInst = $this->getResolver($filter, $resolver);

        if ($resolverInst instanceof SessionPermissionResolver) {
            $paramPath = ltrim($path, '/');
            $params = [
                'path' => $paramPath,
                'filter' => $filter,
                'hash' => $resolverInst->buildHash($resolverInst->getRelFilePath($paramPath, $filter)),
            ];

            if ($resolver)
                $params['resolver'] = $resolver;

            if (empty($runtimeConfig)) {
                $filterUrl = $this->router->generate('liip_imagine_filter', $params,
                    UrlGeneratorInterface::ABSOLUTE_URL);
            } else {
                $params['filters'] = $runtimeConfig;
                $params['hash'] = $this->signer->sign($path, $runtimeConfig);

                $filterUrl = $this->router->generate('liip_imagine_filter_runtime', $params,
                    UrlGeneratorInterface::ABSOLUTE_URL);
            }
        }
        else
            $filterUrl = parent::generateUrl($path, $filter, $runtimeConfig, $resolver);

        return $filterUrl;
    }

    public function setFilter(FilterService $filterService)
    {
        $this->filterService = $filterService;
    }

    /**
     * protected => public
     * @return SessionPermissionResolver
     */
    public function getResolver($filter, $resolver)
    {
        return parent::getResolver($filter, $resolver);
    }

    /**
     * @param $path
     * @param $filter
     * @param null $resolver
     * @param false $regularPath : évite les chemins file://
     * @return string
     */
    public function getFilteredPath($path, $filter, $resolver = null, $regularPath = false)
    {
        $resolverInst = $this->getResolver($filter, $resolver);
        $resolverInst->setLocalUrl(true);
        $path = $this->filterService->getUrlOfFilteredImage($path, $filter, $resolver);
        $resolverInst->setLocalUrl(false);

        if ($regularPath)
            $path = urldecode(preg_replace('#^file:\/\/#', '', $path));

        return $path;
    }
}
