<?php

namespace CanadaPost;

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
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function getSummary($id, $type = 'pin', array $options = [])
    {
        $response = $this->get(
            "vis/track/{$type}/{$id}/summary",
            ['Accept' => 'application/vnd.cpc.track+xml'],
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
        $response = $this->get(
            "vis/track/{$type}/{$id}/details",
            ['Accept' => 'application/vnd.cpc.track+xml'],
            $options
        );
        return $response;
    }

    public function getSignatureImage()
    {

    }

    public function getDeliveryConfirmationCertificate()
    {

    }
}
