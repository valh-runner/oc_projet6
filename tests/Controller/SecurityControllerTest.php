<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class SecurityControllerTest extends WebTestCase {

    public function testLoginWithBadCredentials()
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/connexion');
        $form = $crawler->selectButton('Connexion')->form([
            '_username' => 'john@doe.fr',
            '_password' => 'fakepassword'
        ]);
        $client->submit($form);
        $this->assertResponseRedirects('http://localhost/connexion');
    }
}