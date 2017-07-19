<?php

namespace Tests\AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class DefaultControllerTest extends WebTestCase
{
    public function testIndex()
    {
        $client = static::createClient();

        $client->request('GET', '/');

        $response = $client->getResponse();
        // Test if response is OK
        $this->assertSame(200, $client->getResponse()->getStatusCode());
        // Test that a string is a valid JSON string
        $this->assertJson($response->getContent());
        // Test if Content-Type is valid application/json
        $this->assertSame('application/json', $response->headers->get('Content-Type'));
        // Test if company was inserted
        $this->assertEquals('{"hello":"world"}', $response->getContent());
        // Test that response is not empty
        $this->assertNotEmpty($client->getResponse()->getContent());
    }

    public function testHazardous()
    {
        $client = static::createClient();

        $client->request('GET', '/neo/hazardous');

        $response = $client->getResponse();
        // Test if response is OK
        $this->assertSame(200, $client->getResponse()->getStatusCode());
        // Test that a string is a valid JSON string
        $this->assertJson($response->getContent());
        // Test if Content-Type is valid application/json
        $this->assertSame('application/json', $response->headers->get('Content-Type'));
        // Test that response is not empty
        $this->assertNotEmpty($client->getResponse()->getContent());
    }

    public function testFastest()
    {
        $client = static::createClient();

        $client->request('GET', '/neo/fastest');

        $response = $client->getResponse();
        // Test if response is OK
        $this->assertSame(200, $client->getResponse()->getStatusCode());
        // Test that a string is a valid JSON string
        $this->assertJson($response->getContent());
        // Test if Content-Type is valid application/json
        $this->assertSame('application/json', $response->headers->get('Content-Type'));
        // Test that response is not empty
        $this->assertNotEmpty($client->getResponse()->getContent());
    }
}
