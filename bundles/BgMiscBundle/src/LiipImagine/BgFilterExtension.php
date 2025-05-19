<?php

/**
 * Created by PhpStorm.
 * User: Benoît Guchet
 * Date: 2019-01-07
 * Time: 16:43
 */

namespace Bg\MiscBundle\LiipImagine;

use Imagine\Image\Box;
use Imagine\Image\ImageInterface;
use Liip\ImagineBundle\Service\FilterService;
use Liip\ImagineBundle\Templating\FilterExtension;
use QsGeneralBundle\Entity\AttachmentAbstract;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Twig\TwigFunction;
use Twig_SimpleFunction;

class BgFilterExtension extends FilterExtension
{

    protected $localUrl = false;
    /**
     * @var BgCacheManager
     */
    protected $cache;

    /**
     * @param BgCacheManager $cache
     */
    public function __construct(BgCacheManager $cache)
    {
        $this->cache = $cache;

        parent::__construct($cache);
    }

    public function getFunctions() {
        return [
            new TwigFunction('imagine_thumb_size_from_attachment_and_size', [$this, 'getThumbSizeFromAttachmentAndSize']),
            new TwigFunction('imagine_thumb_size_from_attachment', [$this, 'getThumbSizeFromAttachment']),
            new TwigFunction('imagine_thumb_size_from_path', [$this, 'getThumbSizeFromPath']),
            new TwigFunction('imagine_thumb_size', [$this, 'getThumbSize']),
            new TwigFunction('pdf_imagine_filter', [$this, 'pdfImagineFilter']),
        ];
    }

    public function pdfImagineFilter($path, $filterName, $forHtml = false)
    {
        return $forHtml
            ? $this->cache->getBrowserPath(parse_url($path, PHP_URL_PATH), $filterName)
            : $this->cache->getFilteredPath($path, $filterName);
    }

    public function setLocalUrl($val) {
        $this->localUrl = $val;
    }

    public function filter($path, $filter, array $config = [], $resolver = null,
        $referenceType = UrlGeneratorInterface::ABSOLUTE_URL)
    {
        $config = array_merge(['localUrl' => $this->localUrl], $config);

        if (!empty($config['localUrl']))
            return $this->cache->getFilteredPath($path, $filter, $resolver);
        else {
            unset($config['localUrl']);
            return $this->cache->getBrowserPath($path, $filter, $config, $resolver);
        }
    }

    protected function getOrigSize($attOrPath)
    {
        if (is_object($attOrPath) and $attOrPath->getImageWidth())
            return [
                'width' => $attOrPath->getImageWidth(),
                'height' => $attOrPath->getImageHeight()
            ];
        else {
            $path = is_object($attOrPath) ? $attOrPath->getAbsolutePath() : $attOrPath;
            if (!is_file($path))
                return null;

            $res = getimagesize($path);
            if (!$res)
                return null;

            list($origWidth, $origHeight) = $res;

            return [
                'width' => $origWidth,
                'height' => $origHeight
            ];
        }
    }

    public function getThumbSizeFromPath($path, $filter, $thumbDivider = 1)
    {
        $size = $this->getOrigSize($path);

        return $this->getThumbSize(
            $size ? $size['width'] : null,
            $size ? $size['height'] : null,
            $filter, $thumbDivider
        );
    }

    public function getThumbSizeFromAttachmentAndSize(AttachmentAbstract $att, $maxWidth, $maxHeight)
    {
        $size = $this->getOrigSize($att);
        if (!$size)
            return ['width' => $maxWidth, 'height' => $maxHeight];

        $origSize = new Box($size['width'], $size['height']);
        $thumbSize = new Box($maxWidth, $maxHeight);
        return $this->calcThumbSize($origSize, $thumbSize, ImageInterface::THUMBNAIL_INSET);
    }

    public function getThumbSizeFromAttachment(AttachmentAbstract $att, $filter, $thumbDivider = 1)
    {
        if ($att->getImageWidth())
            return $this->getThumbSize($att->getImageWidth(), $att->getImageHeight(), $filter, $thumbDivider);
        else
            return $this->getThumbSizeFromPath($att->getAbsolutePath(), $filter, $thumbDivider);
    }

    public function getThumbSize($origWidth, $origHeight, $filter, $thumbDivider = 1) {
        $config = $this->cache->getFilterConfig()->get($filter);

        if (!isset($config['filters']['thumbnail']))
            return null;

        $thumbConfig = $config['filters']['thumbnail'];

        if (isset($config['filters']['background'])) {
            list($width, $height) = $config['filters']['background']['size'];
            return ['width' => $width / $thumbDivider, 'height' => $height / $thumbDivider];
        }
        else
            list($width, $height) = $thumbConfig['size'];

        if ($origWidth === null) {
            return ['width' => $width / $thumbDivider, 'height' => $height / $thumbDivider];
        }

        if (null === $width || null === $height) {
            if (null === $height) {
                $height = (int) (($width / $origWidth) * $origHeight);
            } elseif (null === $width) {
                $width = (int) (($height / $origHeight) * $origWidth);
            }
        }

        //-- outbound = crop, inset = no-crop
        $mode = (isset($thumbConfig['mode']) and $thumbConfig['mode'] == 'inset')
            ? ImageInterface::THUMBNAIL_INSET
            : ImageInterface::THUMBNAIL_OUTBOUND;

        /*if (($height > $origHeight and $width > $origWidth) and empty($thumbConfig['allow_upscale'])) {
            $height = $origHeight;
            $width = $origWidth;
        }*/


        $size = new Box($width, $height);
        $imageSize = new Box($origWidth, $origHeight);
        $box = $this->calcThumbSize($imageSize, $size, $mode);

        return ['width' => $box->getWidth() / $thumbDivider, 'height' => $box->getHeight() / $thumbDivider];
    }

    protected function calcThumbSize(Box $origSize, Box $thumbSize, $mode) {
        $ratios = array(
            $thumbSize->getWidth() / $origSize->getWidth(),
            $thumbSize->getHeight() / $origSize->getHeight()
        );

        // if target width is larger than image width
        // AND target height is longer than image height
        if ($thumbSize->contains($origSize)) {
            return $origSize;
        }

        if ($mode === ImageInterface::THUMBNAIL_INSET) {
            $ratio = min($ratios);
        } else {
            $ratio = max($ratios);
        }

        //-- Crop
        if ($mode === ImageInterface::THUMBNAIL_OUTBOUND) {
            /*//-- compliqué
            if (!$origSize->contains($thumbSize))
                return new Box(
                    min($origSize->getWidth(), $thumbSize->getWidth()),
                    min($origSize->getHeight(), $thumbSize->getHeight())
                );
            //-- simple
            else*/
            return $thumbSize;
        }
        //-- No crop
        else {
            //-- compliqué
            if (!$origSize->contains($thumbSize))
                return $origSize->scale($ratio);
            //-- simple
            else
                return $origSize->scale($ratio);
        }
    }
}
