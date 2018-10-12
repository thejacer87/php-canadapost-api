<?php

namespace CanadaPost;

/**
 * ServiceInfo contains Canada Post API calls for service information.
 *
 * @package CanadaPost
 * @see     https://www.canadapost.ca/cpo/mc/business/productsservices/developers/services/serviceinfo/default.jsf
 */
class ServiceInfo extends ClientBase
{
    /**
     * Get messages about upcoming outages to Canada Post web services.
     *
     * @param array $options The options to pass along to the Guzzle Client.
     *
     * @see https://www.canadapost.ca/cpo/mc/business/productsservices/developers/services/serviceinfo/getserviceinfo.jsf
     * @return \DOMDocument
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function getServiceInfo(array $options = [])
    {
        $response = $this->get(
            "rs/serviceinfo/shipment?messageType=SO",
            ['Accept' => 'application/vnd.cpc.serviceinfo-v2+xml'],
            $options
        );

        return $response;
    }
}
