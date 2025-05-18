<?php
/**
 * Created by PhpStorm.
 * User: Benoît Guchet
 * Date: 18/05/2025
 * Time: 10:46
 */

namespace App\Services;

use App\Entity\Document;
use Vich\UploaderBundle\Mapping\PropertyMapping;
use Vich\UploaderBundle\Naming\NamerInterface;

class UploadedFileNamer implements NamerInterface
{
    public function name(object $object, PropertyMapping $mapping): string
    {
        /** @var Document $object */
        $file = $mapping->getFile($object);
        $originalBaseName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);

        return sprintf('%s_%d.%s',
            preg_replace('/[^a-z0-9._-]+/i', '-', $originalBaseName),
            random_int(1, 999),
            $file->guessExtension()
        );
    }
}