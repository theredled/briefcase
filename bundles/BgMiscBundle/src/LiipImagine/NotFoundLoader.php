<?php

namespace Bg\MiscBundle\LiipImagine;
use Liip\ImagineBundle\Binary\Loader\FileSystemLoader;
use Liip\ImagineBundle\Binary\Locator\FileSystemLocator;
use Liip\ImagineBundle\Exception\Binary\Loader\NotLoadableException;
use Liip\ImagineBundle\Model\FileBinary;
use Symfony\Component\HttpFoundation\File\MimeType\ExtensionGuesserInterface as DeprecatedExtensionGuesserInterface;
use Symfony\Component\Mime\MimeTypeGuesserInterface;
use Symfony\Component\Mime\MimeTypesInterface;

/**
 * Created by IntelliJ IDEA.
 * User: Benoît Guchet
 * Date: 13/07/2017
 * Time: 19:47
 */
class NotFoundLoader extends FileSystemLoader
{
    protected $rootPath;

    public function __construct(
        MimeTypeGuesserInterface $mimeTypeGuesser,
        MimeTypesInterface $extensionGuesser,
        $dataRoots
    ) {
        $this->rootPath = $dataRoots;
        if (!file_exists($this->rootPath))
            mkdir($this->rootPath);
        $locator = new FileSystemLocator([$this->rootPath]);
        parent::__construct($mimeTypeGuesser, $extensionGuesser, $locator);
    }

    public function find($path)
    {
        if (false !== strpos($path, '../')) {
            throw new NotLoadableException(sprintf("Source image was searched with '%s' out side of the defined root path", $path));
        }

        $absolutePath = $this->rootPath.'/'.ltrim($path, '/');
        $exists = file_exists($absolutePath);
        $mimeType = $exists ? $this->mimeTypeGuesser->guessMimeType($absolutePath) : null;

        if ($exists === false || strpos($mimeType, 'image/') === false) {
            //throw new \Exception('nf : '.$absolutePath);
            $absolutePath = realpath($this->rootPath.'/../../web/images/not-found.png');
        }

        $mimeType = $mimeType ?: $this->mimeTypeGuesser->guessMimeType($absolutePath);

        return new FileBinary(
            $absolutePath,
            $mimeType,
            $this->getExtension($mimeType)
        );
    }

    private function getExtension(?string $mimeType): ?string
    {
        if (null === $mimeType) {
            return null;
        }

        return $this->extensionGuesser->getExtensions($mimeType)[0] ?? null;
    }
}
