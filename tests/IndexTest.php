<?php
namespace App\Tests;

use App\Entity\Document;
use App\Tests\AbstractTest;

/**
 * Created by PhpStorm.
 * User: Benoît Guchet
 * Date: 02/06/2025
 * Time: 05:27
 */

class IndexTest extends AbstractTest
{

    public function testHome(): void
    {
        //-- liste-kara = invalide / 1
        $docListeKara = $this->em->getRepository(Document::class)->findOneByToken('liste-karaoklm');
        $docListeKara->setFilename('Whatever-657657657.pdf');
        $this->em->persist($docListeKara);
        $this->em->flush();

        $crawler = $this->client->request('GET', '/');
        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('section#downloads');
        $this->assertSelectorTextContains('section#downloads', 'Fiche technique groupe');

        $this->assertSelectorExists('li[data-token="presskit"]');
        $this->assertStringEndsWith('/presskit', $crawler->filter('li[data-token="presskit"] a')->attr('href'));;


        //-- liste-kara = invalide / 2
        $this->assertSelectorExists('li[data-token="liste-karaoklm"]');
        $this->assertSelectorExists('li.invalid-item[data-token="liste-karaoklm"]');
        $this->assertSelectorExists('li[data-token="liste-karaoklm"] .invalid-msg');

    }


    public function testApiDocuments(): void
    {
        $crawler = $this->client->request('GET', '/api/documents');
        $this->assertResponseIsSuccessful();
        $this->assertResponseMimeTypeIs('application/json');
        $json = json_decode($this->client->getResponse()->getContent(), true);

        $sensibleCount = 0;
        $allFilesCount = count($json);

        foreach ($json as $file) {
            if ($file['sensible'])
                $sensibleCount++;
        }

        $this->assertGreaterThan(0, $allFilesCount, 'Total');
        $this->assertEquals(0, $sensibleCount, 'Sensible');
    }

    public function testAuthApiDocuments(): void
    {
        //$this->assertEquals(0, 1, 'TODO');
        return;

        //-- login

        //-- req with token
        $crawler = $this->client->request('GET', '/api/documents');
        $this->assertResponseIsSuccessful();
        $this->assertResponseMimeTypeIs('application/json');
        $json = json_decode($this->client->getResponse()->getContent(), true);

        $sensibleCount = 0;
        $allFilesCount = count($json);

        foreach ($json as $file) {
            if ($file['sensible'])
                $sensibleCount++;
        }

        $this->assertGreaterThan(0, $allFilesCount, 'Total');
        $this->assertGreaterThan(0, $sensibleCount, 'Sensible');
    }
}
