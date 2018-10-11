<?php

namespace CanadaPost;

use LSS\Array2XML;

/**
 * Returns contains Canada Post API calls for returns.
 *
 * @package CanadaPost
 * @see https://www.canadapost.ca/cpo/mc/business/productsservices/developers/services/returns/default.jsf
 */
class Returns extends ClientBase
{

    /**
     * Create an authorized return.
     *
     * @param array $returner
     *   The returner info.
     *   <code>
     *     $returner = [
     *       'name'    => 'Jane Doe',
     *       'company' => 'Acro Media',
     *       'domestic-address' => [
     *         'address-line-1' => '103-2303 Leckie Rd',
     *         'city'           => 'Kelowna',
     *         'province'     => 'BC',
     *         'postal-code'    => 'V1X 6Y5',
     *       ],
     *     ]
     *   </code>
     * @param array $receiver
     *   The receiver info.
     *   <code>
     *     $receiver = [
     *       'name'            => 'John Smith',
     *       'company' => 'Acro Media',
     *       'domestic-address' => [
     *         'address-line-1'  => '123 Main St',
     *         'city'            => 'Kelowna',
     *         'province'      => 'BC',
     *         'postal-code' => 'V1X 1M2',
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
     *   The options array.
     *
     * @return \DOMDocument
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function createAuthorizedReturn(
        array $returner,
        array $receiver,
        array $parcel,
        array $options = []
    ) {
        $content = $this->buildReturnsArray(
            $returner,
            $receiver,
            $parcel,
            $options
        );

        $xml = Array2XML::createXML('authorized-return', $content);
        $envelope = $xml->documentElement;
        $envelope->setAttribute('xmlns',
            'http://www.canadapost.ca/ws/authreturn-v2');
        $payload = $xml->saveXML();

        $response = $this->post(
            "rs/{$this->customerNumber}/{$this->customerNumber}/authorizedreturn",
            [
                'Accept' => 'application/vnd.cpc.authreturn-v2+xml',
                'Content-Type' => 'application/vnd.cpc.authreturn-v2+xml',
            ],
            $payload,
            $options
        );
        return $response;
    }

    /**
     * Create an open return.
     *
     * @param array $receiver
     *   The receiver info.
     *   <code>
     *     $receiver = [
     *       'name'            => 'John Smith',
     *       'company' => 'Acro Media',
     *       'domestic-address' => [
     *         'address-line-1'  => '123 Main St',
     *         'city'            => 'Kelowna',
     *         'province'      => 'BC',
     *         'postal-code' => 'V1X 1M2',
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
     *   The options array.
     *
     * @return \DOMDocument
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function createOpenReturn(
        array $receiver,
        array $parcel,
        array $options = []
    ) {

        $this->formatPostalCode($receiver['domestic-address']['postal-code']);
        $content = [
            'max-number-of-artifacts' => 10,
            'service-code' => $parcel['service_code'],
            'receiver' => $receiver,
            'settlement-info' => [
                'contract-id' => $this->contractId,
            ],
        ];

        if (!empty($options['option_codes'])) {
            $return_info['options']['option'] = $this->parseOptionCodes($options);
        }

        $xml = Array2XML::createXML('open-return', $content);
        $envelope = $xml->documentElement;
        $envelope->setAttribute('xmlns',
            'http://www.canadapost.ca/ws/openreturn-v2');
        $payload = $xml->saveXML();

        $response = $this->post(
            "rs/{$this->customerNumber}/{$this->customerNumber}/openreturn",
            [
                'Accept' => 'application/vnd.cpc.openreturn-v2+xml',
                'Content-Type' => 'application/vnd.cpc.openreturn-v2+xml',
            ],
            $payload,
            $options
        );
        return $response;
    }

    /**
     * Get open return template.
     *
     * @param string $endpoint
     *   The endpoint of the file to retrieve from the Canada Post server.
     * @param array $options
     *   The options array.
     *
     * @return \DOMDocument|\Psr\Http\Message\StreamInterface
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function getOpenReturn($endpoint, array $options = []) {
        $response = $this->getFile(
            $endpoint,
            'xml',
            $options
        );
        return $response;
    }

    /**
     * Get open return details.
     *
     * @param string $endpoint
     *   The endpoint of the file to retrieve from the Canada Post server.
     * @param array $options
     *   The options array.
     *
     * @return \DOMDocument|\Psr\Http\Message\StreamInterface
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function getOpenReturnDetails($endpoint, array $options = []) {
        $response = $this->getOpenReturn(
            $endpoint,
            $options
        );
        return $response;
    }

    /**
     * Get next open return artifact.
     *
     * @param string $endpoint
     *   The endpoint of the file to retrieve from the Canada Post server.
     * @param array $options
     *   The options array.
     *
     * @return \DOMDocument|\Psr\Http\Message\StreamInterface
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function getNextOpenReturnArtifact($endpoint, array $options = []) {
        $response = $this->getArtifact(
            $endpoint,
            $options
        );
        return $response;
    }

    /**
     * Get next open return artifact.
     *
     * @param string $from
     *   The beginning range. YmdHs format, eg. 201808282359.
     * @param string $to
     *   The end range, defaults to current time. YmdHs format, eg. 201808282359.
     * @param array $options
     *   The options array.
     *
     * @return \DOMDocument|\Psr\Http\Message\StreamInterface
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function getAllOpenReturns($from, $to = '', array $options = []) {
        if (empty($to)) {
            $to = date('YmdHs');
        }
        $query_params = "from={$from}&to{$to}";

        $this->verifyDates($from, $to);

        $response = $this->get(
            "rs/{$this->config['customer_number']}/{$this->config['customer_number']}/openreturn?{$query_params}",
            [
                'Accept' => 'application/vnd.cpc.openreturn+xml',
                'Content-Type' => 'application/vnd.cpc.openreturn+xml',
            ],
            $options
        );
        return $response;

    }

    /**
     * Delete the open return template.
     *
     * @param string $endpoint
     *   The endpoint of the file to delete from the Canada Post server.
     * @param array $options
     *   The options array.
     *
     * @return \DOMDocument|\Psr\Http\Message\StreamInterface
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function deleteOpenReturnTemplate($endpoint, array $options = []) {
        $response = $this->delete(
            $endpoint,
            [
                'Accept' => 'application/vnd.cpc.openreturn+xml',
                'Content-Type' => 'application/vnd.cpc.openreturn+xml',
                'Accept-Language' => 'en-CA'
            ],
            $options
        );
        return $response;
    }

    /**
     * Helper function to build the content array.
     *
     * @param array $returner
     *   The returner info.
     * @param array $receiver
     *   The receiver info.
     * @param array $parcel
     *   The parcel characteristics.
     * @param array $options
     *   The options array.
     *
     * @return array
     *   The content array.
     */
    protected function buildReturnsArray(
        array $returner,
        array $receiver,
        array $parcel,
        array $options = []
    ) {
        $this->formatPostalCode($returner['domestic-address']['postal-code']);
        $this->formatPostalCode($receiver['domestic-address']['postal-code']);
        $return_info = [
            'service-code' => $parcel['service_code'],
            'returner' => $returner,
            'receiver' => $receiver,
            'parcel-characteristics' => $parcel,
        ];

        if (!empty($options['option_codes'])) {
            $return_info['options']['option'] = $this->parseOptionCodes($options);
        }

        return $return_info;
    }

}
