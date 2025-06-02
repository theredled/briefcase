<?php

/**
 * Created by PhpStorm.
 * User: Benoît Guchet
 * Date: 01/06/2025
 * Time: 16:07
 */
namespace App\Api;

use App\Entity\Document;
use App\Services\DownloadService;
use Symfony\Component\DependencyInjection\Attribute\AsDecorator;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\SerializerAwareInterface;
use Symfony\Component\Serializer\SerializerInterface;

#[AsDecorator('api_platform.serializer.normalizer.item')]
class DocumentNormalizer implements NormalizerInterface, SerializerAwareInterface
{
    public function __construct(
        private NormalizerInterface $decorated,
        private UrlGeneratorInterface $router,
        private DownloadService $downloadService,
    ) {}

    public function supportsNormalization($data, ?string $format = null, array $context = []): bool
    {
        return $this->decorated->supportsNormalization($data, $format, $context);
    }

    public function normalize($object, ?string $format = null, array $context = []): array
    {
        $data = $this->decorated->normalize($object, $format, $context);
        $data['url'] = $this->router->generate('dl_anything', ['token' => $object->getToken()], UrlGeneratorInterface::ABSOLUTE_URL);
        $data['fa_icon_name'] = $this->downloadService->getFontAwesomeIconName($object);
        return $data;
    }

    public function getSupportedTypes(?string $format): array
    {
        return $this->decorated->getSupportedTypes($format);
    }

    public function setSerializer(SerializerInterface $serializer): void
    {
        if ($this->decorated instanceof SerializerAwareInterface) {
            $this->decorated->setSerializer($serializer);
        }
    }
}