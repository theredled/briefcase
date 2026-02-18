<?php

/**
 * Created by PhpStorm.
 * User: Benoît Guchet
 * Date: 01/06/2025
 * Time: 16:07
 */

namespace App\Api;

use App\Entity\Briefcase;
use App\Entity\Document;
use App\Repository\DocumentRepository;
use App\Services\DownloadService;
use Symfony\Component\DependencyInjection\Attribute\AsDecorator;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\SerializerAwareInterface;
use Symfony\Component\Serializer\SerializerInterface;

#[AsDecorator('api_platform.serializer.normalizer.item')]
class ApiNormalizer implements NormalizerInterface, SerializerAwareInterface
{
    public function __construct(
        private NormalizerInterface   $decorated,
        private DownloadService       $downloadService,
        private DocumentRepository    $documentRepository
    )
    {
    }

    public function supportsNormalization($data, ?string $format = null, array $context = []): bool
    {
        return $this->decorated->supportsNormalization($data, $format, $context);
    }

    public function normalize($data, ?string $format = null, array $context = []): array
    {
        $object = $data;
        $normData = $this->decorated->normalize($object, $format, $context);

        if ($object instanceof Briefcase) {
            $documents = $this->documentRepository->findFromBriefcase($data);
            $normData['documents'] = array_map(function (Document $doc) use ($format, $context) {
                return $this->normalize($doc, $format, $context);
            }, $documents);
        }
        else if ($object instanceof Document) {
            /** @var Document $object */
            $normData['url'] = $this->downloadService->getDownloadUrl($object);
            $normData['fa_icon_name'] = $this->downloadService->getFontAwesomeIconName($object);
            $normData['is_valid'] = $this->downloadService->checkDocumentValidity($object);
            if (!$normData['is_valid'])
                $normData['original_filename'] = $object->getFilename();

            if (in_array('document:detail', $context['groups'])) {
                $normData['included_simple_files'] = array_map(function ($path) {
                    return [
                        'name' => basename($path),
                        'extension' => pathinfo($path, PATHINFO_EXTENSION),
                        'size' => $path ? filesize($path) : null,
                        'mime_type' => $path ? mime_content_type($path) : null,
                        'fa_icon_name' => $this->downloadService->getFontAwesomeIconName($path),
                        'is_valid' => is_file($path)
                    ];
                }, $this->downloadService->findSimpleFilesFromFolder($object));
            }
        }

        return $normData;
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