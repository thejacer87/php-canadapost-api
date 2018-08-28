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
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function getRates($originPostalCode, $postalCode, $weight, array $options = [])
    {
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

        if (!empty($options['option_codes'])) {
            $content['options']['option'] = $this->parseOptionCodes($options);
        }

        $xml = Array2XML::createXML('mailing-scenario', $content);
        $envelope = $xml->documentElement;
        $envelope->setAttribute('xmlns', 'http://www.canadapost.ca/ws/ship/rate-v3');
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
          'SO'   => 'Signature (SO)',
          'PA18' => 'Proof of Age Required - 18 (PA18)',
          'PA19' => 'Proof of Age Required - 19 (PA19)',
          'HFP'  => 'Card for pickup (HFP)',
          'DNS'  => 'Do not safe drop (DNS)',
          'LAD'  => 'Leave at door - do not card (LAD)',
        ];
    }
}
