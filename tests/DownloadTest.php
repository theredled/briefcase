<?php

namespace App\Tests;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Zenstruck\Foundry\Test\Factories;
use Zenstruck\Foundry\Test\ResetDatabase;

class DownloadTest extends WebTestCase
{
    use Factories;
    use ResetDatabase;

    protected function setUp(): void
    {
        self::bootKernel();
        $this->client = static::createClient();
        self::loadFixtureFiles([
            self::$kernel->getVarDir(). '/../fixtures/downloadableFiles.yaml',
            __DIR__ . '/../fixtures/posts.yaml',
        ]);
    }
    public function testSomething(): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'Hello World');
    }
}
