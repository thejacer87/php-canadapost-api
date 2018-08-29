<?php

namespace CanadaPost\Tests;

use CanadaPost\NCShipment;
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
     * @var NCShipment
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
        $this->shipmentService = new NCShipment($config);
    }

    /**
     * Test the POST request to create a non-contract shipment from Canada Post.
     *
     * @covers NCShipment::createNCShipment()
     * @test
     */
    public function createNCShipment()
    {
        $body = file_get_contents(__DIR__
            . '/../Mocks/ncshipment-response-success.xml');
        $mock = new MockHandler([
            new Response(200, [], $body),
        ]);
        $handler = HandlerStack::create($mock);
        $shipment = $this->shipmentService->createNCShipment(
            '123456789012',
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
     * @covers NCShipment::getArtifact()
     * @test
     */
    public function getArtifact()
    {
        $body = file_get_contents(__DIR__
            . '/../Mocks/canadapost.pdf');
        $mock = new MockHandler([
            new Response(200, [], $body),
        ]);
        $handler = HandlerStack::create($mock);
        $response = $this->shipmentService->getArtifact('http://test.com', ['handler' => $handler]);

        // Compares the pdfs.
        $this->assertEquals(0, strcmp($body, $response->getContents()));
    }

    /**
     * Test the GET request to retrieve an artifact from Canada Post.
     *
     * @covers NCShipment::getNCShipment()
     * @test
     */
    public function getNCShipment()
    {
        $getShipmentBody = file_get_contents(__DIR__
            . '/../Mocks/ncshipment-response-success.xml');
        $getDetailsBody = file_get_contents(__DIR__
            . '/../Mocks/ncshipment-details-success.xml');
        $getReceiptBody = file_get_contents(__DIR__
            . '/../Mocks/ncshipment-receipt-success.xml');
        $mock = new MockHandler([
            new Response(200, [], $getShipmentBody),
            new Response(200, [], $getDetailsBody),
            new Response(200, [], $getReceiptBody),
        ]);
        $handler = HandlerStack::create($mock);
        $shipment = $this->shipmentService->getNCShipment('0123456789', '', ['handler' => $handler]);
        $details = $this->shipmentService->getNCShipment('0123456789', '', ['handler' => $handler]);
        $receipt = $this->shipmentService->getNCShipment('0123456789', '', ['handler' => $handler]);

        $this->checkShippingResponse($shipment);
        $this->checkShippingDetails($details);
        $this->checkShippingReceipt($receipt);
    }

    /**
     * Test the GET request to retrieve an artifact from Canada Post.
     *
     * @covers NCShipment::getNCShipments()
     * @test
     */
    public function getNCShipments()
    {
        $body = file_get_contents(__DIR__
            . '/../Mocks/ncshipments-response-success.xml');
        $mock = new MockHandler([
            new Response(200, [], $body),
        ]);
        $handler = HandlerStack::create($mock);
        $shipments = $this->shipmentService->getNCShipments('', '', '', ['handler' => $handler]);

        // Check response.
        $this->assertTrue(is_array($shipments['non-contract-shipments']));

        // Test the shipments.
        $links = $shipments['non-contract-shipments']['link'];

        $this->assertEquals('https://ct.soa-gw.canadapost.ca/rs/0007023211/ncshipment/406951321983787234',
            $links[0]['@attributes']['href']);

        $this->assertEquals('https://ct.soa-gw.canadapost.ca/rs/0007023211/ncshipment/406951321983787352',
            $links[1]['@attributes']['href']);

        $this->assertEquals('https://ct.soa-gw.canadapost.ca/rs/0007023211/ncshipment/406951321983787123',
            $links[2]['@attributes']['href']);
    }

    /**
     * Test the POST request to refund a shipment Canada Post.
     *
     * @covers NCShipment::requestNCShipmentRefund()
     * @test
     */
    public function requestNCShipmentRefund()
    {
        $body = file_get_contents(__DIR__
            . '/../Mocks/ncshipment-refund-request-info.xml');
        $mock = new MockHandler([
            new Response(200, [], $body),
        ]);
        $handler = HandlerStack::create($mock);
        $refund = $this->shipmentService->requestNCShipmentRefund('', '', ['handler' => $handler]);

        // Check response.
        $this->assertTrue(is_array($refund['non-contract-shipment-refund-request-info']));

        $this->assertEquals('2018-08-28', $refund['non-contract-shipment-refund-request-info']['service-ticket-date']);
        $this->assertEquals('GT12345678RT', $refund['non-contract-shipment-refund-request-info']['service-ticket-id']);

    }

    protected function checkShippingResponse($shipment)
    {
        // Check response.
        $this->assertTrue(is_array($shipment['non-contract-shipment-info']));

        // Test the rates.
        $links = $shipment['non-contract-shipment-info']['links']['link'];
        $this->assertTrue(is_array($links));
        $this->assertEquals('406951321983787352',
            $shipment['non-contract-shipment-info']['shipment-id']);

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

    protected function checkShippingDetails($details)
    {
        // Check details.
        $this->assertTrue(is_array($details['non-contract-shipment-details']));

        $shipment = $details['non-contract-shipment-details'];
        $this->assertEquals('J4W4T0', $shipment['final-shipping-point']);
        $this->assertEquals('11111118901234', $shipment['tracking-pin']);

        $delivery_spec = $shipment['delivery-spec'];
        $this->assertTrue(is_array($delivery_spec));
        $this->assertEquals('DOM.EP', $delivery_spec['service-code']);

        // Sender info.
        $this->assertEquals('Canada Post Corporation', $delivery_spec['sender']['company']);
        $this->assertEquals('555-555-5555', $delivery_spec['sender']['contact-phone']);
        $sender_address = $delivery_spec['sender']['address-details'];
        $this->assertTrue(is_array($sender_address));
        $this->assertEquals('2701 Riverside Drive', $sender_address['address-line-1']);
        $this->assertEquals('Ottawa', $sender_address['city']);
        $this->assertEquals('ON', $sender_address['prov-state']);
        $this->assertEquals('K1A0B1', $sender_address['postal-zip-code']);

        // Destination info.
        $this->assertEquals('Consumer', $delivery_spec['destination']['company']);
        $this->assertEquals('John Doe', $delivery_spec['destination']['name']);
        $destination_address = $delivery_spec['destination']['address-details'];
        $this->assertTrue(is_array($destination_address));
        $this->assertEquals('2701 Receiver Drive', $destination_address['address-line-1']);
        $this->assertEquals('Ottawa', $destination_address['city']);
        $this->assertEquals('ON', $destination_address['prov-state']);
        $this->assertEquals('CA', $destination_address['country-code']);
        $this->assertEquals('K1A0B1', $destination_address['postal-zip-code']);

        // Options.
        $this->assertEquals('DC', $delivery_spec['options']['option']['option-code']);

        // Parcel.
        $parcel = $delivery_spec['parcel-characteristics'];
        $this->assertEquals('15.000', $parcel['weight']);
        $this->assertEquals('1', $parcel['dimensions']['length']);
        $this->assertEquals('1', $parcel['dimensions']['width']);
        $this->assertEquals('1', $parcel['dimensions']['height']);
        $this->assertEquals('false', $parcel['document']);
        $this->assertEquals('false', $parcel['unpackaged']);
        $this->assertEquals('false', $parcel['mailing-tube']);

        // Preferences.
        $this->assertEquals('true', $delivery_spec['preferences']['show-packing-instructions']);
        $this->assertEquals('false', $delivery_spec['preferences']['show-postage-rate']);
        $this->assertEquals('false', $delivery_spec['preferences']['show-insured-value']);
    }

    protected function checkShippingReceipt($response)
    {
        // Check receipt.
        $this->assertTrue(is_array($response['non-contract-shipment-receipt']));

        $receipt = $response['non-contract-shipment-receipt'];

        $this->assertEquals('J4W4T0', $receipt['final-shipping-point']);
        $this->assertEquals('BP BROSSARD', $receipt['shipping-point-name']);
        $this->assertEquals('0192', $receipt['shipping-point-id']);
        $this->assertEquals('0001111111', $receipt['mailed-by-customer']);
        $this->assertEquals('DOM.EP', $receipt['service-code']);
        $this->assertEquals('15.000', $receipt['rated-weight']);
        $this->assertEquals('18.10', $receipt['base-amount']);
        $this->assertEquals('19.46', $receipt['pre-tax-amount']);
        $this->assertEquals('0.00', $receipt['gst-amount']);
        $this->assertEquals('0.00', $receipt['pst-amount']);
        $this->assertEquals('2.53', $receipt['hst-amount']);

        // Check price.
        $this->assertTrue(is_array($receipt['priced-options']));
        $this->assertEquals('DC', $receipt['priced-options']['priced-option']['option-code']);
        $this->assertEquals('0', $receipt['priced-options']['priced-option']['option-price']);

        // Check adjustments.
        $this->assertTrue(is_array($receipt['adjustments']));
        $this->assertEquals('FUELSC', $receipt['adjustments']['adjustment']['adjustment-code']);
        $this->assertEquals('1.36', $receipt['adjustments']['adjustment']['adjustment-amount']);

        // Check CC details.
        $cc_receipt = $receipt['cc-receipt-details'];
        $this->assertTrue(is_array($cc_receipt));
        $this->assertEquals('Canada Post Corporation', $cc_receipt['merchant-name']);
        $this->assertEquals('www.canadapost.ca', $cc_receipt['merchant-url']);
        $this->assertEquals('John Doe', $cc_receipt['name-on-card']);
        $this->assertEquals('076838', $cc_receipt['auth-code']);
        $this->assertEquals('2012-03-13T08:27:20-05:00', $cc_receipt['auth-timestamp']);
        $this->assertEquals('VIS', $cc_receipt['card-type']);
        $this->assertEquals('21.99', $cc_receipt['charge-amount']);
        $this->assertEquals('CAD', $cc_receipt['currency']);
        $this->assertEquals('Sale', $cc_receipt['transaction-type']);

        // Check service standard.
        $this->assertTrue(is_array($receipt['service-standard']));
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
                'width' => '1'
            ],
            'service_code' => 'DOM.EP'
        ];
    }

}
