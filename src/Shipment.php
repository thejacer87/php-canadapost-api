<?php

namespace CanadaPost;

use LSS\Array2XML;

/**
 * Shipment contains Canada Post API calls for contract shipments.
 *
 * @package CanadaPost
 * @see https://www.canadapost.ca/cpo/mc/business/productsservices/developers/services/shippingmanifest/default.jsf
 */
class Shipment extends ClientBase
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
    public function createShipment(
        array $sender,
        array $destination,
        array $parcel,
        array $options = []
    ) {
        $content = $this->buildShipmentArray($sender, $destination, $parcel, $options);

        $xml = Array2XML::createXML('non-contract-shipment', $content);
        $envelope = $xml->documentElement;
        $envelope->setAttribute(
            'xmlns',
            'http://www.canadapost.ca/ws/shipment-v8'
        );
        $payload = $xml->saveXML();

        $response = $this->post(
            "rs/{$this->customerNumber}/{$this->customerNumber}/shipment",
            [
                'Accept' => 'application/vnd.cpc.shipment-v8+xml',
                'Content-Type' => 'application/vnd.cpc.shipment-v8+xml',
            ],
            $payload,
            $options
        );
        return $response;
    }

    /**
     * Get Shipment from Canada Post.
     *
     * @param string $shipment_id
     *   The shipment id.
     * @param string $rel
     *   The 'rel' value from the links for a shipment. Either 'details' or 'receipt'.
     * @param array $options
     *   The options array.
     *
     * @return \DOMDocument|\Psr\Http\Message\StreamInterface
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function getShipment(
        $shipment_id,
        $rel = '',
        array $options = []
    ) {
        if (isset($rel) && ($rel !== 'details' || $rel !== 'receipt')) {
            $message = sprintf(
                'Unsupported $rel "%s". Supported endpoint are "details", "receipt" or null.',
                $rel
            );
            throw new \InvalidArgumentException($message);
        }

        $endpoint = sprintf(
            'rs/%s/%s/shipment/%s/%s',
            $this->config['customer_number'],
            $this->config['customer_number'],
            $shipment_id,
            $rel
        );
        $response = $this->get(
            $endpoint,
            ['Accept' => 'application/vnd.cpc.shipment-v8+xml'],
            $options
        );
        return $response;
    }

    /**
     * Get Shipments from Canada Post within the specified range.
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
     * @see https://www.canadapost.ca/cpo/mc/business/productsservices/developers/services/shippingmanifest/shipments.jsf
     * @return \DOMDocument|\Psr\Http\Message\StreamInterface
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function getShipments(
        $from,
        $to = '',
        $tracking_pin = '',
        array $options = []
    ) {
        // TODO convert to shipment API
        if (empty($to)) {
            $to = date('YmdHs');
        }
        $query_params = "from={$from}&to{$to}";

        if (!empty($tracking_pin)) {
            $query_params = "trackingPIN={$tracking_pin}";
        }

        $response = $this->get(
            "rs/{$this->config['customer_number']}/{$this->config['customer_number']}/shipment?" . $query_params,
            ['Accept' => 'application/vnd.cpc.shipment-v8+xml'],
            $options
        );
        return $response;
    }

    /**
     * Request a refund for a shipment that has been transmitted.
     *
     * @param string $shipment_id
     *   The shipment id.
     * @param string $email
     *   The email that will receive updates from Canada Post.
     * @param array $options
     *   The options to pass along to the Guzzle Client.
     *
     * @return \DOMDocument
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @see https://www.canadapost.ca/cpo/mc/business/productsservices/developers/services/shippingmanifest/shipmentrefund.jsf
     * for all available options for the sender,destination and parcel params.
     */
    public function requestShipmentRefund($shipment_id, $email, $options)
    {
        $content = [
            'email' => $email,
        ];

        $xml = Array2XML::createXML(
            'non-contract-shipment-refund-request',
            $content
        );
        $envelope = $xml->documentElement;
        $envelope->setAttribute(
            'xmlns',
            'http://www.canadapost.ca/ws/shipment-v8'
        );
        $payload = $xml->saveXML();

        $endpoint = sprintf(
            'rs/%s/%s/shipment/%s/refund',
            $this->config['customer_number'],
            $this->config['customer_number'],
            $shipment_id
        );
        $response = $this->post(
            $endpoint,
            [
                'Content-Type' => 'application/vnd.cpc.shipment-v8+xml',
                'Accept' => 'application/vnd.cpc.shipment-v8+xml',
            ],
            $payload,
            $options
        );
        return $response;
    }

    /**
     * Transmit shipments for pickup by Canada Post.
     *
     * @param array $manifest_address
     *   The destination info.
     *   <code>
     *     $manifest_address = [
     *       'manifest-company' => 'ACME Inc.',
     *       'phone-number'     => '778 867 5309',
     *       'address-details'  => [
     *         'address-line-1'  => '123 Main St',
     *         'city'            => 'Kelowna',
     *         'prov-state'      => 'BC',
     *         'country-code'    => 'CA',
     *         'postal-zip-code' => 'V1X 1M2',
     *       ],
     *     ]
     *   </code>
     * @param array $group_ids
     *   The group IDs. The Transmit Shipments service will create a manifest
     * for each group. The manifest will list the shipments included in the
     * group.
     * @param array $options
     *   The options to pass along to the Guzzle Client.
     *
     * @see https://www.canadapost.ca/cpo/mc/business/productsservices/developers/services/shippingmanifest/transmitshipments.jsf
     * for all available options for the sender,destination and parcel params.
     * @return \DOMDocument
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function transmitShipments(
        array $manifest_address,
        array $group_ids,
        array $options = []
    ) {
        $this->formatPostalCode($manifest_address['address-details']['postal-zip-code']);
        $content = [
            'group-ids' => [
                'group-id' => $group_ids,
            ],
            'requested-shipping-point' => $manifest_address['address-details']['postal-zip-code'],
            'cpc-pickup-indicator' => true,
            'detailed-manifests' => true,
            'manifest-address' => $manifest_address,
        ];

        if (!empty($options['option_codes'])) {
            $content['options']['option'] = $this->parseOptionCodes($options);
        }

        $xml = Array2XML::createXML('transmit-set', $content);
        $envelope = $xml->documentElement;
        $envelope->setAttribute(
            'xmlns',
            'http://www.canadapost.ca/ws/manifest-v8'
        );
        $payload = $xml->saveXML();

        $response = $this->post(
            "rs/{$this->customerNumber}/{$this->customerNumber}/shipment",
            [
                'Accept' => 'application/vnd.cpc.manifest-v8+xml',
                'Content-Type' => 'application/vnd.cpc.manifest-v8+xml',
            ],
            $payload,
            $options
        );
        return $response;
    }

    /**
     * Get the manifest from Canada Post server.
     *
     * @param string $manifest_id
     *   The manifest id.
     * @param string $rel
     *   The 'rel' value from the links for a manifest. 'details' is the only valid argument.
     * @param array $options
     *   The options array.
     *
     * @return \DOMDocument|\Psr\Http\Message\StreamInterface
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function getManifest($manifest_id, $rel = '', array $options = [])
    {
        if (isset($rel) && ($rel !== 'details')) {
            $message = sprintf(
                'Unsupported "rel" value: "%s". Supported "rel" value are "details" or null.',
                $rel
            );
            throw new \InvalidArgumentException($message);
        }

        $endpoint = sprintf(
            'rs/%s/%s/manifest/%s/%s',
            $this->config['customer_number'],
            $this->config['customer_number'],
            $manifest_id,
            $rel
        );
        $response = $this->get(
            $endpoint,
            ['Accept' => 'application/vnd.cpc.manifest-v8+xml'],
            $options
        );
        return $response;
    }

    /**
     * Get Manifests from Canada Post within the specified range.
     *
     * @param string $start
     *   The beginning range. YmdHs format, eg. 201808282359.
     * @param string $end
     *   The end range, defaults to current time. YmdHs format, eg. 201808282359.
     * @param string $tracking_pin
     *   The Tracking PIN of the shipment to retrieve.
     * @param array $options
     *   The options array.
     *
     * @see https://www.canadapost.ca/cpo/mc/business/productsservices/developers/services/shippingmanifest/manifests.jsf
     * @return \DOMDocument|\Psr\Http\Message\StreamInterface
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function getManifests(
        $start,
        $end = '',
        $tracking_pin = '',
        array $options = []
    ) {
        if (empty($end)) {
            $end = date('YmdHs');
        }
        $query_params = "start={$start}&end{$end}";

        if (!empty($tracking_pin)) {
            $query_params = "trackingPIN={$tracking_pin}";
        }

        $endpoint = sprintf(
            'rs/%s/%s/manifest?%s',
            $this->config['customer_number'],
            $this->config['customer_number'],
            $query_params
        );
        $response = $this->get(
            $endpoint,
            ['Accept' => 'application/vnd.cpc.manifest-v8+xml'],
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
        $this->formatPostalCode($sender['address-details']['postal-zip-code']);
        $this->formatPostalCode($destination['address-details']['postal-zip-code']);
        $shipment_info = [
            'requested-shipping-point' => $destination['address-details']['postal-zip-code'],
            'cpc-pickup-indicator' => true,
            'delivery-spec' => [
                'service-code' => $parcel['service_code'],
                'sender' => $sender,
                'destination' => $destination,
                'parcel-characteristics' => $parcel,
                'preferences' => [
                    'show-packing-instructions' => $options['packing_instructions'] ?? true,
                ],
                'notification' => [],
                'settlement-info' => [
                    'contract-id' => '',
                    'intended-method-of-payment' => 'Account',
                ],
            ],
        ];

        if (!empty($options['option_codes'])) {
            $shipment_info['options']['option'] = $this->parseOptionCodes($options);
        }

        return $shipment_info;
    }
}
