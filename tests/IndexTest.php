<?php

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
        $crawler = $this->client->request('GET', '/');
        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('section#downloads');
        $this->assertSelectorTextContains('section#downloads', 'Fiche technique groupe');
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
        $this->assertEquals(0, 1, 'TODO');
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
