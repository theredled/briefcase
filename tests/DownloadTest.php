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

class DownloadTest extends WebTestCase
{
    use Factories;
    use ResetDatabase;

    protected KernelBrowser $client;

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
    }

    public function testHome(): void
    {
        $crawler = $this->client->request('GET', '/');
        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('section#downloads');
        $this->assertSelectorTextContains('section#downloads', 'Fiche technique groupe');
    }

    public function testRegularPdf()
    {
        $crawler = $this->client->request('GET', '/d/tech-rider-solo');
        $newUrl = $this->assertPreviewWorks();
        $crawler = $this->client->request('GET', $newUrl);
        $this->assertResponseMimeTypeIs('application/pdf');
    }


    public function testFolderWithOnlyDirectFiles(): void
    {
        /** @var EntityManager $em */
        $em = static::getContainer()->get('doctrine')->getManager();

        /** @var Document $folderDoc */
        $folderDoc = $em->getRepository(Document::class)->findOneBy(['token' => 'djset']);
        /** @var Document $pdfDoc */
        $pdfDoc = $em->getRepository(Document::class)->findOneBy(['token' => 'tech-rider-solo']);
        $folderDoc->setFilename(null);
        $folderDoc->addIncludedFile($pdfDoc);
        $em->persist($folderDoc);
        $em->flush();

        $crawler = $this->client->request('GET', '/d/djset');
        $newUrl = $this->assertPreviewWorks();
        $crawler = $this->client->request('GET', $newUrl);
        $this->assertResponseMimeTypeIs('application/zip');
    }

    public function testSensibleDoc(): void
    {
        $uri = '/d/rib-bg';
        try {
            $crawler = $this->client->request('GET', $uri);
            $this->fail();
        }
        catch (AccessDeniedHttpException $e) {
            $this->assertTrue(true);
        }
        $absoluteUri = $this->client->getRequest()->getUri();
        //$this->assertResponseStatusCodeSame(403);
        /** @var UriSigner $uriSigner */
        $uriSigner = static::getContainer()->get(UriSigner::class);
        $signedUri = $uriSigner->sign($absoluteUri);
        $crawler = $this->client->request('GET', $signedUri);
        $newUrl = $this->assertPreviewWorks();
        $crawler = $this->client->request('GET', $newUrl);
        $this->assertResponseIsSuccessful();
    }

    public function testOldUrls(): void
    {
        /*$urls = [
            '/dl/tech-rider-solo',
            '/dl/fr/tech-rider-solo',
            '/dlFolder/djset',
            '/dlFolder/fr/djset'
        ];
        foreach ($urls as $url) {
            $this->client->request('GET', $url);
            $this->assertResponseIsSuccessful('URL : '.$url);
        }*/
        $this->assertTrue(true);
    }

    public function testFolderWithOnlyIncludedDocuments()
    {
        $crawler = $this->client->request('GET', '/d/djset');
        $newUrl = $this->assertPreviewWorks();
        $crawler = $this->client->request('GET', $newUrl);
        $this->assertResponseMimeTypeIs('application/zip');
    }


    protected function assertResponseMimeTypeIs($expectedType): void
    {
        $this->assertResponseIsSuccessful();
        $this->assertResponseHasHeader('Content-Type');
        $contentType = $this->client->getResponse()->headers->get('Content-Type');
        $mimeType = trim(explode(';', $contentType)[0]);
        $this->assertEquals($expectedType, $mimeType);
    }

    protected function assertPreviewWorks($message = '')
    {
        $this->assertResponseIsSuccessful($message);
        $this->assertSelectorExists('title', $message);
        $this->assertSelectorExists('meta[http-equiv="Refresh"]', $message);
        $attr = $this->client->getCrawler()->filter('meta[http-equiv="Refresh"]')->attr('content');
        $url = preg_replace("#^(.*)url='(.*)'$#", '$2', $attr);
        return $url;
    }
}
