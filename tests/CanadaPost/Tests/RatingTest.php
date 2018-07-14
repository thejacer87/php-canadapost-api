<?php

namespace CanadaPost\Tests;

use CanadaPost\Rating;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use PHPUnit_Framework_TestCase;

/**
 * Rating Class Tests.
 * @group Rating
 */
class RatingTest extends PHPUnit_Framework_TestCase
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
        $config = [
            'username' => 'username',
            'password' => 'password',
            'customer_number' => 'customer_number',
        ];
        $this->ratingService = new Rating($config);
    }

    /**
     * Test the POST request to get rates from Canada Post.
     * @covers Rating::getRates()
     * @test
     */
    public function getRates()
    {
        $body = file_get_contents(__DIR__ . '/../Mocks/rating-response-success.xml');
        $mock = new MockHandler([
            new Response(200, [], $body),
        ]);
        $handler = HandlerStack::create($mock);
        $response = $this->ratingService->getRates('', '', 1, ['handler' => $handler]);
        foreach ($response['price-quotes'] as $quotes) {
            $this->assertTrue(is_array($quotes));
            foreach ($quotes as $rate) {
                $this->assertTrue(is_array($rate));
                $this->assertNotEmpty($rate['service-code']);
                $this->assertNotEmpty($rate['service-name']);
                $this->assertGreaterThan(0, $rate['price-details']['base']);
            }
        }
    }

}
