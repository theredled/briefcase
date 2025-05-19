<?php

/**
 * Created by PhpStorm.
 * User: Benoît Guchet
 * Date: 2018-12-28
 * Time: 23:51
 */

namespace Bg\MiscBundle\LiipImagine;

use Liip\ImagineBundle\Binary\BinaryInterface;
use Liip\ImagineBundle\Imagine\Cache\Resolver\ResolverInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;

class SessionPermissionResolver implements ResolverInterface
{
    protected $key;
    protected $localUrl = false;

    public function __construct(
        protected Filesystem $filesystem,
        protected RequestContext $requestContext,
        protected SessionInterface $session,
        protected CsrfTokenManagerInterface $csrfTokenManager,
        protected string $salt,
        protected string $cacheDir,
        protected string $scriptPrefix,
        protected string $env
    ) {
    }

    /*public function setAsync(bool $val) {
        $this->async = $val;
    }*/

    public function setLocalUrl(bool $val) {
        $this->localUrl = $val;
    }

    public function resolve($path, $filter)
    {
        $relPath = $this->getRelFilePath($path, $filter);
        $url = $this->buildUrl($relPath);
        return $url;
    }

    public function isStored($path, $filter)
    {
        return is_file($this->getAbsFilePath($path, $filter));
    }

    public function store(BinaryInterface $binary, $path, $filter)
    {
        $this->filesystem->dumpFile(
            $this->getAbsFilePath($path, $filter),
            $binary->getContent()
        );
    }

    public function remove(array $paths, array $filters)
    {
        if (empty($paths) && empty($filters))
            return;

        if (empty($paths)) {
            $filtersCacheDir = [];
            foreach ($filters as $filter)
                $filtersCacheDir[] = $this->cacheDir.'/'.$filter;

            $this->filesystem->remove($filtersCacheDir);
            return;
        }

        foreach ($paths as $path) {
            foreach ($filters as $filter)
                $this->filesystem->remove($this->getAbsFilePath($path, $filter));
        }
    }

    public function getToken()
    {
        return $this->csrfTokenManager->getToken('media');
    }

    protected function getAbsFilePath($path, $filter)
    {
        return $this->cacheDir.'/'.$this->getRelFilePath($path, $filter);
    }

    public function getRelFilePath($path, $filter)
    {
        // crude way of sanitizing URL scheme ("protocol") part
        $path = str_replace('://', '---', $path);
        return $filter.'/'.ltrim($path, '/');
    }

    protected function buildUrl($path)
    {
        if (!$this->localUrl) {
            $port = '';
            if ('https' === $this->requestContext->getScheme() && 443 !== $this->requestContext->getHttpsPort()) {
                $port = ":{$this->requestContext->getHttpsPort()}";
            }

            if ('http' === $this->requestContext->getScheme() && 80 !== $this->requestContext->getHttpPort()) {
                $port = ":{$this->requestContext->getHttpPort()}";
            }

            return sprintf('%s://%s%s/%s?file=%s&hash=%s&env=%s',
                $this->requestContext->getScheme(),
                $this->requestContext->getHost(),
                $port,
                $this->scriptPrefix,
                urlencode($path),
                $this->buildHash($path),
                $this->env
            );
        }
        else
            return sprintf('file://%s',
                str_replace(' ', '%20', realpath($this->cacheDir.'/'.$path))
            );
    }

    /**
     * @param $path string : Final path, with filter in it.
     * @return string
     */
    public function buildHash($path)
    {
        $this->session->set('mediaKey', $this->getKey());
        return self::staticBuildHash($path, $this->getKey());
    }

    static public function staticBuildHash($path, $key)
    {
        return md5($path.$key);
    }

    public function getKey()
    {
        if (!$this->key)
            $this->key = md5($this->salt);
        return $this->key;
    }

    public function isFileOk($file, $hash, $session)
    {
        return self::staticIsFileOk($file, $hash, $session);
    }

    static public function staticIsFileOk($file, $hash, $session)
    {
        if (is_object($session) and $session instanceof SessionInterface)
            $session = $session->all();

        return self::staticBuildHash($file, $session['mediaKey']) == $hash;
    }
}
