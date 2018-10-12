<?php

namespace CanadaPost\Tests;

use CanadaPost\ServiceInfo;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;

/**
 * Service Info Class Tests.
 *
 * @group ServiceInfo
 */
class ServiceInfoTest extends CanadaPostTestBase
{

    /**
     * The ServiceInfo service.
     *
     * @var ServiceInfo
     */
    protected $serviceInfoService;

    /**
     * Build the $serviceInfoService to be tested.
     */
    public function setUp()
    {
        parent::setUp();
        $this->serviceInfoService = new ServiceInfo($this->config);
    }

    /**
     * Test the request to get service info from Canada Post.
     *
     * @covers ServiceInfo::getServiceInfo()
     * @test
     */
    public function getServiceInfo()
    {
        $body = file_get_contents(__DIR__
            . '/Mocks/service-info-success.xml');
        $mock = new MockHandler([
            new Response(200, [], $body),
        ]);
        $handler = HandlerStack::create($mock);
        $response = $this->serviceInfoService->getServiceInfo(
            ['handler' => $handler]
        );

        // Check response.
        $this->assertTrue(is_array($response['info-messages']));

        $info = $response['info-messages']['info-message'];
        $this->assertEquals('SO', $info['message-type']);
        $this->assertEquals('Please note that we will be performing scheduled maintenance from Saturday, Oct. 12 at 9 p.m. to Sunday, Oct. 13 at 4 a.m. EDT. During this time, Canada Post web services will not be available.', $info['message-text']);
        $this->assertEquals('2013-10-12T21:00:00-05:00', $info['from-datetime']);
        $this->assertEquals('2013-10-13T04:00:00-05:00', $info['to-datetime']);
    }

}
