<?php

namespace CanadaPost\Tests;

use CanadaPost\Shipment;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use PHPUnit_Framework_TestCase;

/**
 * Shipment Class Tests.
 *
 * @group Shipment
 */
class ShipmentTest extends PHPUnit_Framework_TestCase
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
            'contract_id' => 'contract_id',
        ];
        $this->shipmentService = new Shipment($config);
    }

    /**
     * Test the POST request to create a non-contract shipment from Canada Post.
     *
     * @covers Shipment::createShipment()
     * @test
     */
    public function createShipment()
    {
        $body = file_get_contents(__DIR__ . '/../Mocks/shipment-response-success.xml');
        $mock = new MockHandler([
            new Response(200, [], $body),
        ]);
        $handler = HandlerStack::create($mock);
        $shipment = $this->shipmentService->createShipment(
            $this->mockAddress(),
            $this->mockAddress(),
            $this->mockParcel(),
            ['handler' => $handler]
        );

        $this->checkShippingResponse($shipment);
    }

    /**
     * Test the GET request to retrieve an artifact from Canada Post.
     *
     * @covers Shipment::getShipment()
     * @test
     */
    public function getShipment()
    {
        $getShipmentBody = file_get_contents(__DIR__ . '/../Mocks/shipment-response-success.xml');
        $getDetailsBody = file_get_contents(__DIR__ . '/../Mocks/shipment-details-success.xml');
        $getReceiptBody = file_get_contents(__DIR__ . '/../Mocks/shipment-receipt-success.xml');
        $mock = new MockHandler([
            new Response(200, [], $getShipmentBody),
            new Response(200, [], $getDetailsBody),
            new Response(200, [], $getReceiptBody),
        ]);
        $handler = HandlerStack::create($mock);
        $shipment = $this->shipmentService->getShipment(
            '0123456789',
            '',
            ['handler' => $handler]
        );
        $details = $this->shipmentService->getShipment(
            '0123456789',
            '',
            ['handler' => $handler]
        );
        $receipt = $this->shipmentService->getShipment(
            '0123456789',
            '',
            ['handler' => $handler]
        );

        $this->checkShippingResponse($shipment);
        $this->checkShippingDetails($details);
        $this->checkShippingReceipt($receipt);
    }

    /**
     * Test the GET request to retrieve an artifact from Canada Post.
     *
     * @covers Shipment::getShipments()
     * @test
     */
    public function getShipments()
    {
        $body = file_get_contents(__DIR__ . '/../Mocks/ncshipments-response-success.xml');
        $mock = new MockHandler([
            new Response(200, [], $body),
        ]);
        $handler = HandlerStack::create($mock);
        $shipments = $this->shipmentService->getShipments(
            '201809270000',
            '201810240000',
            '',
            ['handler' => $handler]
        );

        // Check response.
        $this->assertTrue(is_array($shipments['non-contract-shipments']));

        // Test the shipments.
        $links = $shipments['non-contract-shipments']['link'];

        $this->assertEquals(
            'https://ct.soa-gw.canadapost.ca/rs/0007023211/ncshipment/406951321983787234',
            $links[0]['@attributes']['href']
        );

        $this->assertEquals(
            'https://ct.soa-gw.canadapost.ca/rs/0007023211/ncshipment/406951321983787352',
            $links[1]['@attributes']['href']
        );

        $this->assertEquals(
            'https://ct.soa-gw.canadapost.ca/rs/0007023211/ncshipment/406951321983787123',
            $links[2]['@attributes']['href']
        );
    }

    /**
     * Test the POST request to refund a shipment Canada Post.
     *
     * @covers Shipment::requestShipmentRefund()
     * @test
     */
    public function requestShipmentRefund()
    {
        $body = file_get_contents(__DIR__ . '/../Mocks/ncshipment-refund-request-info.xml');
        $mock = new MockHandler([
            new Response(200, [], $body),
        ]);
        $handler = HandlerStack::create($mock);
        $refund = $this->shipmentService->requestShipmentRefund(
            '',
            '',
            ['handler' => $handler]
        );

        // Check response.
        $this->assertTrue(is_array($refund['non-contract-shipment-refund-request-info']));

        $this->assertEquals(
            '2018-08-28',
            $refund['non-contract-shipment-refund-request-info']['service-ticket-date']
        );
        $this->assertEquals(
            'GT12345678RT',
            $refund['non-contract-shipment-refund-request-info']['service-ticket-id']
        );
    }

    /**
     * Test the GET request to retrieve an artifact from Canada Post.
     *
     * @covers \CanadaPost\ClientBase::getArtifact()
     * @test
     */
    public function getArtifact()
    {
        $body = file_get_contents(__DIR__ . '/../Mocks/canadapost.pdf');
        $mock = new MockHandler([
            new Response(200, [], $body),
        ]);
        $handler = HandlerStack::create($mock);
        $response = $this->shipmentService->getArtifact(
            'the/endpoint',
            ['handler' => $handler]
        );

        // Compares the pdfs.
        $this->assertEquals(0, strcmp($body, $response->getContents()));
    }

    protected function checkShippingResponse($shipment)
    {
        // Check response.
        $this->assertTrue(is_array($shipment['shipment-info']));

        // Test the rates.
        $links = $shipment['shipment-info']['links']['link'];
        $this->assertTrue(is_array($links));
        $this->assertEquals('347881315405043891',
            $shipment['shipment-info']['shipment-id']);

        $this->assertTrue(is_array($links));

        // Self.
        $this->assertEquals('https://XX/rs/111111111/2222222222/shipment/347881315405043891',
            $links[0]['@attributes']['href']);
        $this->assertEquals('self', $links[0]['@attributes']['rel']);
        $this->assertEquals(
            'application/vnd.cpc.shipment-v8+xml',
            $links[0]['@attributes']['media-type']
        );

        // Details.
        $this->assertEquals('https://XX/rs/111111111/2222222222/shipment/347881315405043891/details',
            $links[1]['@attributes']['href']);
        $this->assertEquals('details', $links[1]['@attributes']['rel']);
        $this->assertEquals(
            'application/vnd.cpc.shipment-v8+xml',
            $links[1]['@attributes']['media-type']
        );

        // Group.
        $this->assertEquals(
            'https://XX/rs/111111111/2222222222/shipment?groupid=bobo',
            $links[2]['@attributes']['href']
        );
        $this->assertEquals('group', $links[2]['@attributes']['rel']);
        $this->assertEquals(
            'application/vnd.cpc.shipment-v8+xml',
            $links[2]['@attributes']['media-type']
        );

        // Price.
        $this->assertEquals(
            'https://XX/rs/111111111/2222222222/shipment/347881315405043891/price',
            $links[3]['@attributes']['href']
        );
        $this->assertEquals('price', $links[3]['@attributes']['rel']);
        $this->assertEquals(
            'application/vnd.cpc.shipment-v8+xml',
            $links[3]['@attributes']['media-type']
        );

        // Label.
        $this->assertEquals('https://XX/rs/artifact/11111111/5555555/0',
            $links[4]['@attributes']['href']);
        $this->assertEquals('label', $links[4]['@attributes']['rel']);
        $this->assertEquals(
            'application/pdf',
            $links[4]['@attributes']['media-type']
        );
    }

    protected function checkShippingDetails($details)
    {
        // Check details.
        $this->assertTrue(is_array($details['shipment-details']));

        $shipment = $details['shipment-details'];
        $this->assertEquals('K1G1C0', $shipment['final-shipping-point']);
        $this->assertEquals('1234567890123456', $shipment['tracking-pin']);

        $shipment_detail = $shipment['shipment-detail'];
        $this->assertTrue(is_array($shipment_detail));
        $this->assertEquals('bobo', $shipment_detail['group-id']);
        $this->assertEquals('K1G1C0',
            $shipment_detail['requested-shipping-point']);
        $this->assertEquals('2011-09-01',
            $shipment_detail['expected-mailing-date']);

        $delivery_spec = $shipment_detail['delivery-spec'];
        $this->assertTrue(is_array($delivery_spec));
        $this->assertEquals('DOM.EP', $delivery_spec['service-code']);

        // Sender info.
        $this->assertEquals('CGI', $delivery_spec['sender']['company']);
        $sender_address = $delivery_spec['sender']['address-details'];
        $this->assertTrue(is_array($sender_address));
        $this->assertEquals(
            '502 MAIN ST N',
            $sender_address['address-line-1']
        );
        $this->assertEquals('MONTREAL', $sender_address['city']);
        $this->assertEquals('QC', $sender_address['prov-state']);
        $this->assertEquals('H2B1A0', $sender_address['postal-zip-code']);

        // Destination info.
        $this->assertEquals('CGI', $delivery_spec['destination']['company']);
        $this->assertEquals('Jain', $delivery_spec['destination']['name']);
        $destination_address = $delivery_spec['destination']['address-details'];
        $this->assertTrue(is_array($destination_address));
        $this->assertEquals('23 jardin private',
            $destination_address['address-line-1']);
        $this->assertEquals('Ottawa', $destination_address['city']);
        $this->assertEquals('ON', $destination_address['prov-state']);
        $this->assertEquals('CA', $destination_address['country-code']);
        $this->assertEquals(
            'K1K4T3',
            $destination_address['postal-zip-code']
        );

        // Options.
        $this->assertEquals('DC',
            $delivery_spec['options']['option']['option-code']);

        // Parcel.
        $parcel = $delivery_spec['parcel-characteristics'];
        $this->assertEquals('20.000', $parcel['weight']);
        $this->assertEquals('12', $parcel['dimensions']['length']);
        $this->assertEquals('9', $parcel['dimensions']['width']);
        $this->assertEquals('6', $parcel['dimensions']['height']);

        // Preferences.
        $this->assertEquals('true',
            $delivery_spec['preferences']['show-packing-instructions']);
        $this->assertEquals('false',
            $delivery_spec['preferences']['show-postage-rate']);
        $this->assertEquals('true',
            $delivery_spec['preferences']['show-insured-value']);
    }

    protected function checkShippingReceipt($response)
    {
        // Check receipt.
        $this->assertTrue(is_array($response['shipment-receipt']));

        $receipt = $response['shipment-receipt'];
        $this->assertTrue(is_array($receipt));

        // CC Receipt Details.
        $cc_receipt_details = $receipt['cc-receipt-details'];
        $this->assertTrue(is_array($cc_receipt_details));

        $this->assertEquals('Canada Post Corporation',
            $cc_receipt_details['merchant-name']);
        $this->assertEquals('www.canadapost.ca',
            $cc_receipt_details['merchant-url']);
        $this->assertEquals('John Doe', $cc_receipt_details['name-on-card']);
        $this->assertEquals('076838', $cc_receipt_details['auth-code']);
        $this->assertEquals('2013-06-17T08:27:20-05:00',
            $cc_receipt_details['auth-timestamp']);
        $this->assertEquals('VIS', $cc_receipt_details['card-type']);
        $this->assertEquals('21.99', $cc_receipt_details['charge-amount']);
        $this->assertEquals('CAD', $cc_receipt_details['currency']);
        $this->assertEquals(
            'Sale',
            $cc_receipt_details['transaction-type']
        );
    }

    protected function mockAddress()
    {
        return [
            'name' => 'John Smith',
            'company' => 'ACME',
            'address-details' => [
                'address-line-1' => '123 Main St',
                'city' => 'Ottawa',
                'prov-state' => 'ON',
                'country-code' => 'CA',
                'postal-zip-code' => 'K1A0B1',
            ],
        ];
    }

    protected function mockParcel()
    {
        return [
            'weight' => '15.00',
            'dimensions' => [
                'length' => '1',
                'height' => '1',
                'width' => '1',
            ],
            'service_code' => 'DOM.EP',
        ];
    }

}
