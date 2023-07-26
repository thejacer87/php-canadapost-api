<?php

namespace CanadaPost;

use LSS\Array2XML;

/**
 * Rating contains Canada Post API calls for package rates.
 *
 * @package CanadaPost
 * @see https://www.canadapost.ca/cpo/mc/business/productsservices/developers/services/rating/default.jsf
 */
class Rating extends ClientBase
{

    /**
     * Get the shipping rates for the given locations and weight.
     *
     * @param string $originPostalCode
     *   The origin postal code.
     * @param string $postalCode
     *   The destination postal code.
     * @param float $weight
     *   The weight of the package (kg).
     * @param Dimension $dimensions
     *   The dimensions of the package.
     * @param array $options
     *   The array of options. Rating specific options:
     *     - service_codes: https://www.canadapost.ca/cpo/mc/business/productsservices/developers/services/rating/getrates/default.jsf
     *     - options_codes: https://www.canadapost.ca/cpo/mc/business/productsservices/developers/services/rating/getrates/default.jsf
     *
     * @return \DOMDocument
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function getRates(
        $originPostalCode,
        $postalCode,
        $weight,
        $dimensions = null,
        array $options = []
    ) {
        // Canada Post API needs all postal codes to be uppercase and no spaces.
        $originPostalCode = strtoupper(str_replace(' ', '', $originPostalCode));
        $postalCode = strtoupper(str_replace(' ', '', $postalCode));

        $content = [
            'customer-number' => $this->customerNumber,
            'parcel-characteristics' => [
                'weight' => $weight,
            ],
            'origin-postal-code' => $originPostalCode,
            'destination' => [
                'domestic' => [
                    'postal-code' => $postalCode,
                ],
            ],
        ];
        if ($dimensions) {
          $content['parcel-characteristics']['dimensions'] = [
            'length' => $dimensions->getLength(),
            'width' => $dimensions->getWidth(),
            'height' => $dimensions->getHeight(),
          ];
        }

        // TODO split the options for Canada Post from the options for Guzzle.
        // They can either be separate variables or the Canada Post options can
        // be within a sub-array keyed by canada_post (or the other way around).
        if (!empty($options['service_codes'])) {
            $content['services']['service-code'] = $this->parseServiceCodes($options);
        }

        if (!empty($options['option_codes'])) {
            $content['options']['option'] = $this->parseOptionCodes($options);
        }

        if (!empty($this->config['contract_id'])) {
            $content['contract-id'] = $this->config['contract_id'];
        }

        $xml = Array2XML::createXML('mailing-scenario', $content);
        $envelope = $xml->documentElement;
        $envelope->setAttribute(
            'xmlns',
            'http://www.canadapost.ca/ws/ship/rate-v4'
        );
        $payload = $xml->saveXML();

        $response = $this->post(
            "rs/ship/price",
            [
                'Content-Type' => 'application/vnd.cpc.ship.rate-v4+xml',
                'Accept' => 'application/vnd.cpc.ship.rate-v4+xml',
            ],
            $payload,
            $options
        );

        $price_quotes = $response['price-quotes']['price-quote'];
        // If only one service comes back, it does NOT return an array of quotes
        // unlike when multiple services are requested.
        if (array_key_exists('service-code', $price_quotes)) {
            $response['price-quotes']['price-quote'] = [$price_quotes];
        }
        return $response;
    }

    /**
     * Get the Canada Post specific service codes,
     *
     * @return array
     *   The array of service codes.
     *
     * @see https://www.canadapost.ca/cpo/mc/business/productsservices/developers/services/rating/getrates/default.jsf
     */
    public static function getServiceCodes()
    {
        return [
            'DOM.EP' => 'Expedited Parcel',
            'DOM.RP' => 'Regular Parcel',
            'DOM.PC' => 'Priority',
            'DOM.XP' => 'Xpresspost',
            'DOM.XP.CERT' => 'Xpresspost Certified',
            'DOM.LIB' => 'Library Materials',
            'USA.EP' => 'Expedited Parcel USA',
            'USA.PW.ENV' => 'Priority Worldwide Envelope USA',
            'USA.PW.PAK' => 'Priority Worldwide pak USA',
            'USA.PW.PARCEL' => 'Priority Worldwide Parcel USA',
            'USA.SP.AIR' => 'Small Packet USA Air',
            'USA.TP' => 'Tracked Packet – USA',
            'USA.TP.LVM' => 'Tracked Packet – USA (LVM) (large volume mailers)',
            'USA.XP' => 'Xpresspost USA',
            'INT.XP' => 'Xpresspost International',
            'INT.IP.AIR' => 'International Parcel Air',
            'INT.IP.SURF' => 'International Parcel Surface',
            'INT.PW.ENV' => 'Priority Worldwide Envelope Int’l',
            'INT.PW.PAK' => 'Priority Worldwide pak Int’l',
            'INT.PW.PARCEL' => 'Priority Worldwide parcel Int’l',
            'INT.SP.AIR' => 'Small Packet International Air',
            'INT.SP.SURF' => 'Small Packet International Surface',
            'INT.TP' => 'Tracked Packet – International',
        ];
    }

    /**
     * Helper function to extract the service codes.
     *
     * @param array $options
     *   The options array.
     *
     * @return array
     *   The list of services to look up.
     */
    protected function parseServiceCodes(array $options)
    {
        $services = [];
        foreach ($options['service_codes'] as $serviceCode) {
            if (!array_key_exists(strtoupper($serviceCode), self::getServiceCodes())) {
                $message = sprintf(
                    'Unsupported service code: "%s". Supported services are %s',
                    $serviceCode,
                    implode(', ', array_keys(self::getServiceCodes()))
                );
                throw new \InvalidArgumentException($message);
            }
            $services[] = $serviceCode;
        }

        return $services;
    }
}
