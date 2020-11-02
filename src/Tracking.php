<?php

namespace CanadaPost;

/**
 * Canada Post API calls to get tracking details.
 *
 * @package CanadaPost
 * @see https://www.canadapost.ca/cpo/mc/business/productsservices/developers/services/tracking/default.jsf
 */
class Tracking extends ClientBase
{

    /**
     * Get the shipping rates for the given locations and weight.
     *
     * @param string $id
     *   The tracking identification number, either a pin or dnc.
     * @param string $type
     *   The tracking identification type, either pin or dnc. Defaults to pin.
     * @param array $options
     *   The options to pass along to the Guzzle Client.
     *
     * @see https://www.canadapost.ca/cpo/mc/business/productsservices/developers/services/tracking/trackingsummary.jsf
     * @return \DOMDocument
     * @throws \GuzzleHttp\Exception\GuzzleException|\InvalidArgumentException
     */
    public function getSummary($id, $type = 'pin', array $options = [])
    {
        if ($type !== 'pin' && $type !== 'dnc') {
            $message = sprintf(
                'Unsupported type: "%s". Supported types are "pin" and "dnc".',
                $type
            );
            throw new \InvalidArgumentException($message);
        }
        $response = $this->get(
            "vis/track/{$type}/{$id}/summary",
            ['Accept' => 'application/vnd.cpc.track-v2+xml'],
            $options
        );
        return $response;
    }

    /**
     * Get the shipping rates for the given locations and weight.
     *
     * @param string $id
     *   The tracking identification number, either a pin or dnc.
     * @param string $type
     *   The tracking identification type, either pin or dnc. Defaults to pin.
     * @param array $options
     *   The options to pass along to the Guzzle Client.
     *
     * @see https://www.canadapost.ca/cpo/mc/business/productsservices/developers/services/tracking/trackingdetails.jsf
     * @return \DOMDocument
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function getDetails($id, $type = 'pin', array $options = [])
    {
        if ($type !== 'pin' && $type !== 'dnc') {
            $message = sprintf(
                'Unsupported type: "%s". Supported types are "pin" and "dnc".',
                $type
            );
            throw new \InvalidArgumentException($message);
        }
        $response = $this->get(
            "vis/track/{$type}/{$id}/detail",
            ['Accept' => 'application/vnd.cpc.track-v2+xml'],
            $options
        );
        return $response;
    }

    /**
     * Get the image of the signature provided for a specific parcel.
     *
     * @param string $pin
     *   The tracking pin.
     * @param array $options
     *   The options to pass along to the Guzzle Client.
     *
     * @return \DOMDocument
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function getSignatureImage($pin, array $options = [])
    {
        $response = $this->getFile(
            "vis/signatureimage/{$pin}",
            'vnd.cpc.track-v2+xml',
            $options
        );
        return $response;
    }

    /**
     * Get the image of the delivery confirmation certificate for a parcel.
     *
     * @param string $pin
     *   The tracking pin.
     * @param array $options
     *   The options to pass along to the Guzzle Client.
     *
     * @return \DOMDocument
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function getDeliveryConfirmationCertificate(
        $pin,
        array $options = []
    ) {
        $response = $this->getFile(
            "vis/certificate/{$pin}",
            'vnd.cpc.track-v2+xml',
            $options
        );
        return $response;
    }
}
