<?php

/**
 * Created by PhpStorm.
 * User: Benoît Guchet
 * Date: 02/06/2025
 * Time: 05:52
 */

namespace App\Api;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Entity\Document;
use App\Repository\DocumentRepository;

class DocumentProvider implements ProviderInterface
{
    public function __construct(
        private DocumentRepository $repository
    ) {}

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): object|null|array
    {
        if ($operation instanceof \ApiPlatform\Metadata\GetCollection) {
            return $this->repository->findBy(['sensible' => false]);
        }

        /** @var Document $doc */
        $doc = $this->repository->find($uriVariables['id'] ?? null);
        return $doc && !$doc->getSensible() ? $doc : null;
    }
}
