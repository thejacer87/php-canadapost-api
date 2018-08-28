<?php

namespace CanadaPost;

use LSS\Array2XML;

class NCShipment extends ClientBase
{

    /**
     * Create the shipment.
     *
     * @param string $customerNumber
     *   The customer number.
     * @param array $sender
     *   The sender info.
     * @param array $destination
     *   The destination info.
     * @param array $parcel
     *   The parcel info.
     * @param array $options
     *   The options to pass along to the Guzzle Client.
     *
     * @return \DOMDocument
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function createNCShipment(
        $customerNumber,
        array $sender,
        array $destination,
        $parcel,
        array $options = []
    ) {
        $content = $this->buildShipmentArray($sender, $destination, $parcel, $options);

        $xml = Array2XML::createXML('non-contract-shipment', $content);
        $envelope = $xml->documentElement;
        $envelope->setAttribute('xmlns',
            'http://www.canadapost.ca/ws/ncshipment-v4');
        $payload = $xml->saveXML();

        $response = $this->post(
            "rs/{$customerNumber}/ncshipment",
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
     * Get an artifact from Canada Post server.
     * @param string $url
     *   The url of the file to retrieve from the Canada Post server.
     * @param array $options
     *   The options array.
     *
     * @return \DOMDocument|\Psr\Http\Message\StreamInterface
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function getArtifact($url, array $options = [])
    {
        $url = str_replace('https://ct.soa-gw.canadapost.ca/', '', $url);
        $response = $this->getFile(
            $url,
            'pdf',
            $options + [
                'raw_response' => true
            ]
        );
        return $response;
    }

    protected function buildShipmentArray(
        $sender,
        $destination,
        $parcel,
        $options
    ) {
        $shipment_info = [
            'requested-shipping-point' => strtoupper(str_replace(' ', '', $destination['postal_code'])),
            'delivery-spec' => [
                'service-code' => $parcel['service_code'],
                'sender' => $this->buildSender($sender),
                'destination' => $this->buildDestination($destination),
                'parcel-characteristics' => [
                    'weight' => $parcel['weight'],
                    'dimensions' => [
                        'length' => $parcel['length'],
                        'width' => $parcel['width'],
                        'height' => $parcel['height'],
                    ],
                ],
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

    protected function buildSender($sender)
    {
        return [
            'company' => $sender['company'],
            'contact-phone' => $sender['phone'],
            'address-details' => [
                'address-line-1' => $sender['address'],
                'city' => $sender['city'],
                'prov-state' => $sender['province'],
                'postal-zip-code' => strtoupper(str_replace(' ', '', $sender['postal_code'])),
            ],
        ];
    }

    protected function buildDestination($destination)
    {
        return [
            'name' => $destination['name'],
            'company' => $destination['company'],
            'address-details' => [
                'address-line-1' => $destination['address'],
                'city' => $destination['city'],
                'prov-state' => $destination['province'],
                'country-code' => $destination['country'],
                'postal-zip-code' => strtoupper(str_replace(' ', '', $destination['postal_code'])),
            ],
        ];
    }
}
