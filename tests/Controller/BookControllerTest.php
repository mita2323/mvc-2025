<?php
namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * Test cases for BookController class.
 */
class BookControllerTest extends WebTestCase
{
    /**
     * Tests the /library/show route.
     */
    public function testIndex(): void
    {
        $client = static::createClient();
        $client->request('GET', '/library/show');
        $this->assertResponseIsSuccessful();
    }
}
