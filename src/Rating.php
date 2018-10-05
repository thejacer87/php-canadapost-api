<?php

namespace CanadaPost;

use LSS\Array2XML;

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
     * @param array $options
     *   The options to pass along to the Guzzle Client.
     *
     * @return \DOMDocument
     */
    public function getRates(
        $originPostalCode,
        $postalCode,
        $weight,
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

        if (!empty($options['service_codes'])) {
            $content['services']['service-code'] = $this->parseServiceCodes($options);
        }

        if (!empty($options['option_codes'])) {
            $content['options']['option'] = $this->parseOptionCodes($options);
        }

        $xml = Array2XML::createXML('mailing-scenario', $content);
        $envelope = $xml->documentElement;
        $envelope->setAttribute(
            'xmlns',
            'http://www.canadapost.ca/ws/ship/rate-v3'
        );
        $payload = $xml->saveXML();

        $response = $this->post(
            "rs/ship/price",
            [
                'Content-Type' => 'application/vnd.cpc.ship.rate-v3+xml',
                'Accept' => 'application/vnd.cpc.ship.rate-v3+xml',
            ],
            $payload,
            $options
        );
        return $response;
    }

    /**
     * Get the Canada Post-specific option codes,
     *
     * @return array
     *   The array of option codes.
     *
     * @see https://www.canadapost.ca/cpo/mc/business/productsservices/developers/services/rating/getrates/default.jsf
     */
    public static function getOptionCodes()
    {
        return [
            'SO' => 'Signature (SO)',
            'PA18' => 'Proof of Age Required - 18 (PA18)',
            'PA19' => 'Proof of Age Required - 19 (PA19)',
            'HFP' => 'Card for pickup (HFP)',
            'DNS' => 'Do not safe drop (DNS)',
            'LAD' => 'Leave at door - do not card (LAD)',
        ];
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
     * Helper function to extract the option codes.
     *
     * @param array $options
     *   The options array.
     *
     * @return array
     *   The list of options with the option-code.
     */
    protected function parseOptionCodes(array $options)
    {
        $valid_options = [];
        foreach ($options['option_codes'] as $optionCode) {
            if (!in_array(strtoupper($optionCode), self::getOptionCodes())) {
                continue;
            }
            // @todo Perhaps we should check for conflicts here, might be overkill.
            // From Canada Post docs:
            // There are some options that can be applied to a shipment that
            // conflict with the presence of another option. You can use the
            // "Get Option" call in advance to check the contents of the
            // <conflicting-options> group from a Get Option call for options
            // selected by end users or options available for a given service.
            $valid_options[] = [
                'option-code' => $optionCode,
            ];
        }

        return $valid_options;
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
                break;
            }
            $services[] = $serviceCode;
        }

        return $services;
    }
}
