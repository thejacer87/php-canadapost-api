<?php

namespace CanadaPost\Tests;

use CanadaPost\Pickup;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;

/**
 * Pickup Class Tests.
 * @group Pickup
 */
class PickupTest extends CanadaPostTestBase
{

    /**
     * The Pickup service.
     *
     * @var Pickup
     */
    protected $pickupService;

    /**
     * Build the $ratingService to be tested.
     */
    public function setUp()
    {
        parent::setUp();
        $this->pickupService = new Pickup($this->config);
    }

    /**
     * Test the request to create a pickup request from Canada Post.
     *
     * @covers Pickup::createPickupRequest()
     * @test
     */
    public function createPickupRequest()
    {
        $body = file_get_contents(__DIR__
            . '/Mocks/pickup-request-success.xml');
        $mock = new MockHandler([
            new Response(200, [], $body),
        ]);
        $handler = HandlerStack::create($mock);
        $response = $this->pickupService->createPickupRequest(['handler' => $handler]);

        // Check response.
        $this->assertTrue(is_array($response['pickup-request-info']));

    }
}
