<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class AppControllerTest extends WebTestCase {

    public function testHomepage()
    {
        $client = static::createClient();
        $client->request('GET', '/');
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
	}

    public function testTrickDetailsPage()
    {
        $client = static::createClient();
        $value = '1080';
        $client->request('GET', "/details_trick/$value");
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
	}    

	public function testFakePage()
    {
        $client = static::createClient();
        $client->request('GET', '/signin');
        $this->assertEquals(404, $client->getResponse()->getStatusCode());
	}
}

