<?php

namespace CanadaPost;

use LSS\Array2XML;

class NCShipment extends ClientBase
{

    /**
     * Create the shipment.
     *
     * @param array $sender
     *   The sender info.
     *   <code>
     *     $sender = [
     *       'company'         => 'Acro Media',
     *       'contact-phone'   => '(250) 763-8884',
     *       'address-details' => [
     *         'address-line-1'  => '103-2303 Leckie Rd',
     *         'city'            => 'Kelowna',
     *         'prov-state'      => 'BC',
     *         'postal-zip-code' => 'V1X 6Y5',
     *       ],
     *     ]
     *   </code>
     * @param array $destination
     *   The destination info.
     *   <code>
     *     $destination = [
     *       'name'            => 'John Smith',
     *       'address-details' => [
     *         'address-line-1'  => '123 Main St',
     *         'city'            => 'Kelowna',
     *         'prov-state'      => 'BC',
     *         'country-code'    => 'CA',
     *         'postal-zip-code' => 'V1X 1M2',
     *       ],
     *     ]
     *   </code>
     * @param array $parcel
     *   The parcel characteristics.
     *   <code>
     *     $parcel = [
     *       'weight'     => 0.500,   // in kg.
     *       'dimensions' => [        // in cm.
     *         'length' => 30,
     *         'width'  => 10,
     *         'height' => 20,
     *         ],
     *       ],
     *     ]
     *   </code>
     * @param array $options
     *   The options to pass along to the Guzzle Client.
     *
     * @see https://www.canadapost.ca/cpo/mc/business/productsservices/developers/services/onestepshipping/createshipment.jsf
     * for all available options for the sender,destination and parcel params.
     * @return \DOMDocument
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function createNCShipment(
        array $sender,
        array $destination,
        array $parcel,
        array $options = []
    ) {
        $content = $this->buildShipmentArray($sender, $destination, $parcel, $options);

        $xml = Array2XML::createXML('non-contract-shipment', $content);
        $envelope = $xml->documentElement;
        $envelope->setAttribute('xmlns',
            'http://www.canadapost.ca/ws/ncshipment-v4');
        $payload = $xml->saveXML();

        $response = $this->post(
            "rs/{$this->customerNumber}/ncshipment",
            [
                'Accept' => 'application/vnd.cpc.ncshipment-v4+xml',
                'Content-Type' => 'application/vnd.cpc.ncshipment-v4+xml',
            ],
            $payload,
            $options
        );
        return $response;
    }

    /**
     * Get NCShipment from Canada Post.
     * @param string $shipment_id
     *   The shipment id
     * @param string $extra
     *   Gets the specific details for a shipment. Either 'details' or 'receipt'.
     * @param array $options
     *   The options array.
     *
     * @return \DOMDocument|\Psr\Http\Message\StreamInterface
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function getNCShipment($shipment_id, $extra = '', array $options = [])
    {
        $response = $this->get(
            "rs/{$this->config['customer_number']}/ncshipment/{$shipment_id}/{$extra}",
            ['Accept' => 'application/vnd.cpc.ncshipment-v4+xml'],
            $options
        );
        return $response;

    }

    /**
     * Get NCShipments from Canada Post within the specified range.
     *
     * @param string $from
     *   The beginning range. YmdHs format, eg. 201808282359.
     * @param string $to
     *   The end range, defaults to current time. YmdHs format, eg. 201808282359.
     * @param string $tracking_pin
     *   The Tracking PIN of the shipment to retrieve.
     * @param array $options
     *   The options array.
     *
     * @return \DOMDocument|\Psr\Http\Message\StreamInterface
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @see https://www.canadapost.ca/cpo/mc/business/productsservices/developers/services/onestepshipping/onestepshipments.jsf
     */
    public function getNCShipments($from = '', $to = '', $tracking_pin = '', array $options = [])
    {
        if (empty($to)) {
            $to = date('YmdHs');
        }
        $query_params = "from={$from}&to{$to}";

        if (!empty($tracking_pin)) {
            $query_params = "trackingPIN={$tracking_pin}";
        }

        $response = $this->get(
            "rs/{$this->config['customer_number']}/ncshipment?" . $query_params,
            ['Accept' => 'application/vnd.cpc.ncshipment-v4+xml'],
            $options
        );
        return $response;

    }

    public function requestNCShipmentRefund($shipment_id, $email, $options) {
        $content = [
            'email' => $email
        ];

        $xml = Array2XML::createXML('non-contract-shipment-refund-request', $content);
        $envelope = $xml->documentElement;
        $envelope->setAttribute('xmlns',
            'http://www.canadapost.ca/ws/ncshipment-v4');
        $payload = $xml->saveXML();
        $response = $this->post(
            "rs/{$this->config['customer_number']}/ncshipment/{$shipment_id}/refund",
            [
                'Content-Type' => 'application/vnd.cpc.ncshipment-v4+xml',
                'Accept' => 'application/vnd.cpc.ncshipment-v4+xml',
            ],
            $payload,
            $options
        );
        return $response;
    }

    /**
     * Helper function to build the content array.
     *
     * @param array $sender
     *   The sender array.
     * @param array $destination
     *   The destination array.
     * @param array $parcel
     *   The parcel array.
     * @param array $options
     *   The options array.
     *
     * @return array
     *   The content array.
     */
    protected function buildShipmentArray(
        array $sender,
        array $destination,
        array $parcel,
        array $options = []
    ) {
        $this->verifyPostalCode($sender);
        $this->verifyPostalCode($destination);
        $shipment_info = [
            'requested-shipping-point' => $destination['address-details']['postal-zip-code'],
            'delivery-spec' => [
                'service-code' => $parcel['service_code'],
                'sender' => $sender,
                'destination' => $destination,
                'parcel-characteristics' => $parcel,
                'preferences' => [
                    'show-packing-instructions' => $options['packing_instructions'] ?? true,
                ],
            ],
        ];

        if (!empty($options['option_codes'])) {
            $shipment_info['options']['option'] = $this->parseOptionCodes($options);
        }

        return $shipment_info;
    }
}
