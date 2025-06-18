<?php

namespace App\Tests;

use App\Entity\Document;
use App\Kernel;
use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\UriSigner;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Security\Http\AccessToken\Oidc\Exception\InvalidSignatureException;
use Zenstruck\Foundry\Test\Factories;
use Zenstruck\Foundry\Test\ResetDatabase;

abstract class AbstractTest extends WebTestCase
{
    use Factories;
    use ResetDatabase;

    protected KernelBrowser $client;
    protected EntityManager $em;

    protected function createNewDb(): void
    {
        $projectDir = self::$kernel->getProjectDir();
        $prodDbPath = $projectDir . '/var/data.db';
        $testDbPath = $projectDir . '/var/tests/data_test.db';
        unlink($testDbPath);
        copy($prodDbPath, $testDbPath);
    }

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->client->followRedirects(false);
        $this->client->catchExceptions(false);
        $this->createNewDb();
        self::bootKernel();
        $this->em = static::$kernel->getContainer()->get('doctrine')->getManager();
    }

    protected function assertResponseMimeTypeIs($expectedType): void
    {
        $this->assertResponseIsSuccessful();
        $this->assertResponseHasHeader('Content-Type');
        $contentType = $this->client->getResponse()->headers->get('Content-Type');
        $mimeType = trim(explode(';', $contentType)[0]);
        $this->assertEquals($expectedType, $mimeType);
    }
}
