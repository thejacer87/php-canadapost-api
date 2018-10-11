<?php

namespace CanadaPost\Tests;

use CanadaPost\Tracking;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;

/**
 * Tracking Class Tests.
 * @group Tracking
 */
class TrackingTest extends CanadaPostTestBase
{

    /**
     * The Tracking service.
     *
     * @var Tracking
     */
    protected $trackingService;

    /**
     * Build the $trackingService to be tested.
     */
    public function setUp()
    {
        parent::setUp();
        $this->trackingService = new Tracking($this->config);
    }

    /**
     * @covers Tracking::getSummary()
     * @test
     */
    public function getSummary()
    {
        $body = file_get_contents(__DIR__ . '/Mocks/tracking-summary-success.xml');
        $mock = new MockHandler([
            new Response(200, [], $body),
        ]);
        $handler = HandlerStack::create($mock);
        $response = $this->trackingService->getSummary(
            '',
            'pin',
            ['handler' => $handler]
        );

        // Check response.
        $this->assertTrue(is_array($response['tracking-summary']));
        $this->assertTrue(is_array($response['tracking-summary']['pin-summary']));

        // Get the summary.
        $summary = $response['tracking-summary']['pin-summary'];
        $this->assertTrue(is_array($summary));

        // Expedited Parcel.
        $this->assertEquals('1681334332936901', $summary['pin']);
        $this->assertEquals('S6V', $summary['origin-postal-id']);
        $this->assertEquals('2010-01-05', $summary['mailed-on-date']);
        $this->assertEquals('2010-01-06', $summary['actual-delivery-date']);
        $this->assertEquals('2', $summary['delivery-option-completed-ind']);
        $this->assertEquals('20100106:084923', $summary['event-date-time']);
        $this->assertEquals('Paiement CR - SPL', $summary['event-description']);
        $this->assertEquals('2010-01-06', $summary['attempted-date']);
        $this->assertEquals('OUT', $summary['event-type']);

    }

    /**
     * @covers Tracking::getDetails()
     * @test
     */
    public function getDetails()
    {
        $body = file_get_contents(__DIR__ . '/Mocks/tracking-details-success.xml');
        $mock = new MockHandler([
            new Response(200, [], $body),
        ]);
        $handler = HandlerStack::create($mock);
        $response = $this->trackingService->getDetails(
            '',
            'dnc',
            ['handler' => $handler]
        );

        // Check response.
        $this->assertTrue(is_array($response['tracking-detail']));

        // Get the summary.
        $details = $response['tracking-detail'];
        $this->assertTrue(is_array($details));
        $this->assertEquals('1371134583769923', $details['pin']);
        $this->assertEquals('1', $details['active-exists']);
        $this->assertEquals('2011-02-11', $details['changed-expected-date']);
        $this->assertEquals('G1K4M7', $details['destination-postal-id']);
        $this->assertEquals('2011-02-01', $details['expected-delivery-date']);
        $this->assertEquals('Customer addressing error found; attempting to correct', $details['changed-expected-delivery-reason']);
        $this->assertEquals('0001234567', $details['mailed-by-customer-number']);
        $this->assertEquals('0008765432', $details['mailed-on-behalf-of-customer-number']);
        $this->assertEquals('Xpresspost', $details['service-name']);
        $this->assertEquals('Xpresspost', $details['service-name-2']);
        $this->assertEquals('955-0398', $details['customer-ref-1']);
        $this->assertEquals('true', $details['signature-image-exists']);
        $this->assertEquals('false', $details['suppress-signature']);

        // Delivery Options.
        $delivery_options = $details['delivery-options'];
        $this->assertTrue(is_array($delivery_options));
        $this->assertTrue(is_array($delivery_options['item']));
        $this->assertEquals('CH_SGN_OPTION', $delivery_options['item']['delivery-option']);
        $this->assertEquals('Signature Required', $delivery_options['item']['delivery-option-description']);

        // Get the events.
        $events = $response['tracking-detail']['significant-events'];
        $this->assertTrue(is_array($events));

        $this->assertEquals('20', $events['occurrence'][0]['event-identifier']);
        $this->assertEquals('2011-02-03', $events['occurrence'][0]['event-date']);
        $this->assertEquals('11:59:59', $events['occurrence'][0]['event-time']);
        $this->assertEquals('EST', $events['occurrence'][0]['event-time-zone']);
        $this->assertEquals('Signature image recorded for Online viewing', $events['occurrence'][0]['event-description']);
        $this->assertEquals('SAINTE-FOY', $events['occurrence'][0]['event-site']);
        $this->assertEquals('QC', $events['occurrence'][0]['event-province']);

        $this->assertEquals('0174', $events['occurrence'][1]['event-identifier']);
        $this->assertEquals('2011-02-03', $events['occurrence'][1]['event-date']);
        $this->assertEquals('08:27:43', $events['occurrence'][1]['event-time']);
        $this->assertEquals('EST', $events['occurrence'][1]['event-time-zone']);
        $this->assertEquals('Item out for delivery', $events['occurrence'][1]['event-description']);
        $this->assertEquals('SAINTE-FOY', $events['occurrence'][1]['event-site']);
        $this->assertEquals('QC', $events['occurrence'][1]['event-province']);

        $this->assertEquals('0100', $events['occurrence'][2]['event-identifier']);
        $this->assertEquals('2011-02-02', $events['occurrence'][2]['event-date']);
        $this->assertEquals('14:45:48', $events['occurrence'][2]['event-time']);
        $this->assertEquals('EST', $events['occurrence'][2]['event-time-zone']);
        $this->assertEquals('Item processed at postal facility', $events['occurrence'][2]['event-description']);
        $this->assertEquals('QUEBEC', $events['occurrence'][2]['event-site']);
        $this->assertEquals('QC', $events['occurrence'][2]['event-province']);

        $this->assertEquals('0173', $events['occurrence'][3]['event-identifier']);
        $this->assertEquals('2011-02-02', $events['occurrence'][3]['event-date']);
        $this->assertEquals('06:19:57', $events['occurrence'][3]['event-time']);
        $this->assertEquals('EST', $events['occurrence'][3]['event-time-zone']);
        $this->assertEquals('Customer addressing error found; attempting to correct. Possible delay', $events['occurrence'][3]['event-description']);
        $this->assertEquals('QUEBEC', $events['occurrence'][3]['event-site']);
        $this->assertEquals('QC', $events['occurrence'][3]['event-province']);

        $this->assertEquals('1496', $events['occurrence'][4]['event-identifier']);
        $this->assertEquals('2011-02-01', $events['occurrence'][4]['event-date']);
        $this->assertEquals('07:59:52', $events['occurrence'][4]['event-time']);
        $this->assertEquals('EST', $events['occurrence'][4]['event-time-zone']);
        $this->assertEquals('Item successfully delivered', $events['occurrence'][4]['event-description']);
        $this->assertEquals('QUEBEC', $events['occurrence'][4]['event-site']);
        $this->assertEquals('QC', $events['occurrence'][4]['event-province']);

        $this->assertEquals('20', $events['occurrence'][5]['event-identifier']);
        $this->assertEquals('2011-02-01', $events['occurrence'][5]['event-date']);
        $this->assertEquals('07:59:52', $events['occurrence'][5]['event-time']);
        $this->assertEquals('EST', $events['occurrence'][5]['event-time-zone']);
        $this->assertEquals('Signature image recorded for Online viewing', $events['occurrence'][5]['event-description']);
        $this->assertEquals('QUEBEC', $events['occurrence'][5]['event-site']);
        $this->assertEquals('QC', $events['occurrence'][5]['event-province']);

    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Unsupported type: "wrong". Supported types are "pin" and "dnc".
     */
    public function testGetSummaryInvalidArgument()
    {
        $mock = new MockHandler([
            new Response(200),
        ]);
        $handler = HandlerStack::create($mock);
        $this->trackingService->getSummary(
            '',
            'wrong',
            ['handler' => $handler]
        );
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Unsupported type: "wrong". Supported types are "pin" and "dnc".
     */
    public function testGetDetailsInvalidArgument()
    {
        $mock = new MockHandler([
            new Response(200),
        ]);
        $handler = HandlerStack::create($mock);
        $this->trackingService->getDetails(
            '',
            'wrong',
            ['handler' => $handler]
        );
    }

}
