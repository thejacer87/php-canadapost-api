<?php

namespace CanadaPost\Tests;

use CanadaPost\Dimension;
use CanadaPost\Rating;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;

/**
 * Rating Class Tests.
 * @group Rating
 */
class RatingTest extends CanadaPostTestBase
{

    /**
     * The Rating service.
     *
     * @var Rating
     */
    protected $ratingService;

    /**
     * Build the $ratingService to be tested.
     */
    public function setUp()
    {
        parent::setUp();
        $this->ratingService = new Rating($this->config);
    }

    /**
     * Test the POST request to get rates from Canada Post.
     * @covers Rating::getRates()
     * @test
     */
    public function getRates()
    {
        $body = file_get_contents(__DIR__
            . '/Mocks/rating-response-success.xml');
        $mock = new MockHandler([
            new Response(200, [], $body),
        ]);
        $handler = HandlerStack::create($mock);
        $response = $this->ratingService->getRates('', '', 1, new Dimension(1, 1, 1), ['handler' => $handler]);

        // Check response.
        $this->assertTrue(is_array($response['price-quotes']));

        // Test the rates.
        $rates = $response['price-quotes']['price-quote'];
        $this->assertTrue(is_array($rates));

        // Expedited Parcel.
        $this->assertTrue(is_array($rates[0]));
        $this->assertEquals('DOM.EP', $rates[0]['service-code']);
        $this->assertEquals('Expedited Parcel', $rates[0]['service-name']);
        $this->assertTrue(is_array($rates[0]['price-details']));
        $this->assertEquals('9.59', $rates[0]['price-details']['base']);

        // Priority Courier.
        $this->assertTrue(is_array($rates[1]));
        $this->assertEquals('DOM.PC', $rates[1]['service-code']);
        $this->assertEquals('Priority Courier', $rates[1]['service-name']);
        $this->assertTrue(is_array($rates[1]['price-details']));
        $this->assertEquals('22.64', $rates[1]['price-details']['base']);

        // Priority Courier.
        $this->assertTrue(is_array($rates[2]));
        $this->assertEquals('DOM.RP', $rates[2]['service-code']);
        $this->assertEquals('Regular Parcel', $rates[2]['service-name']);
        $this->assertTrue(is_array($rates[2]['price-details']));
        $this->assertEquals('9.59', $rates[2]['price-details']['base']);

        // Priority Courier.
        $this->assertTrue(is_array($rates[3]));
        $this->assertEquals('DOM.XP', $rates[3]['service-code']);
        $this->assertEquals('Xpresspost', $rates[3]['service-name']);
        $this->assertTrue(is_array($rates[3]['price-details']));
        $this->assertEquals('12.26', $rates[3]['price-details']['base']);
    }

}
