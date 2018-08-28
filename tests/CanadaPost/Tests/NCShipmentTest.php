<?php

namespace CanadaPost\Tests;

use CanadaPost\Shipment;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use PHPUnit_Framework_TestCase;

/**
 * Rating Class Tests.
 *
 * @group Shipment
 */
class NCShipmentTest extends PHPUnit_Framework_TestCase
{

    /**
     * The Shipment service.
     *
     * @var Shipment
     */
    protected $shipmentService;

    /**
     * Build the $ratingService to be tested.
     */
    public function setUp()
    {
        $config = [
            'username' => 'username',
            'password' => 'password',
            'customer_number' => 'customer_number',
        ];
        $this->shipmentService = new Shipment($config);
    }

    /**
     * Test the POST request to create a non-contract shipment from Canada Post.
     *
     * @covers Shipment::createNCShipment()
     * @test
     */
    public function createNCShipment()
    {
        $body = file_get_contents(__DIR__
            . '/../Mocks/shipment-response-success.xml');
        $mock = new MockHandler([
            new Response(200, [], $body),
        ]);
        $handler = HandlerStack::create($mock);
        $response = $this->shipmentService->createNCShipment('', [], [], [],
            ['handler' => $handler]);

        // Check response.
        $this->assertTrue(is_array($response['non-contract-shipment-info']));

        // Test the rates.
        $links = $response['non-contract-shipment-info']['links']['link'];
        $this->assertTrue(is_array($links));
        $this->assertEquals('406951321983787352',
            $response['non-contract-shipment-info']['shipment-id']);

        $this->assertTrue(is_array($links));

        // Self.
        $this->assertEquals('https://ct.soa-gw.canadapost.ca/rs/0007023211/ncshipment/406951321983787352',
            $links[0]['@attributes']['href']);
        $this->assertEquals('self', $links[0]['@attributes']['rel']);
        $this->assertEquals('application/vnd.cpc.ncshipment-v4+xml', $links[0]['@attributes']['media-type']);

        // Details.
        $this->assertEquals('https://ct.soa-gw.canadapost.ca/rs/0007023211/ncshipment/406951321983787352/details',
            $links[1]['@attributes']['href']);
        $this->assertEquals('details', $links[1]['@attributes']['rel']);
        $this->assertEquals('application/vnd.cpc.ncshipment-v4+xml', $links[1]['@attributes']['media-type']);

        // Receipt.
        $this->assertEquals('https://ct.soa-gw.canadapost.ca /rs/0007023211/ncshipment/406951321983787352/receipt',
            $links[2]['@attributes']['href']);
        $this->assertEquals('receipt', $links[2]['@attributes']['rel']);
        $this->assertEquals('application/vnd.cpc.ncshipment-v4+xml', $links[2]['@attributes']['media-type']);

        // Label.
        $this->assertEquals('https://ct.soa-gw.canadapost.ca/rs/artifact/76108cb5192002d5/10238/0',
            $links[3]['@attributes']['href']);
        $this->assertEquals('label', $links[3]['@attributes']['rel']);
        $this->assertEquals('application/pdf', $links[3]['@attributes']['media-type']);

    }

}
