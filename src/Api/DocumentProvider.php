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
use App\Services\UserService;

class DocumentProvider implements ProviderInterface
{
    public function __construct(
        private DocumentRepository $repository,
        protected UserService $userService
    ) {}

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): object|null|array
    {
        $isAuth = $this->userService->getCurrentUser();

        if ($operation instanceof \ApiPlatform\Metadata\GetCollection) {
            return $this->repository->findBy($isAuth ? [] : ['sensible' => false]);
        }

        /** @var Document $doc */
        $doc = $this->repository->find($uriVariables['id'] ?? null);

        if (null === $doc )
            return null;
        elseif (!$isAuth && $doc->getSensible())
            return null;
        else
            return $doc;
    }
}
