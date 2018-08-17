<?php

namespace CanadaPost;

use LSS\Array2XML;

class Rating extends ClientBase
{

    /**
     * The Canada Post-specific option codes,
     *
     * @see https://www.canadapost.ca/cpo/mc/business/productsservices/developers/services/rating/getrates/default.jsf
     */
    public static $VALID_OPTION_CODES = [
      'SO',
      'PA18',
      'PA19',
      'HFP',
      'DNS',
      'LAD',
    ];

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
            $content['options']['option'] = $this->getOptionCodes($options);
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
     * Helper function to extract the option codes.
     *
     * @param array $options
     *  The options array.
     *
     * @return array
     *  The list of options with the option-code.
     */
    protected function getOptionCodes(array $options) {
      $valid_options= [];
      foreach ($options['option_codes'] as $optionCode) {
        if (!in_array(strtoupper($optionCode), $this::$VALID_OPTION_CODES)) {
          break;
        }
        // @todo Perhaps we should check for conflicts here, might be overkill.
        // From Canada Post docs:
        // There are some options that can be applied to a shipment that
        // conflict with the presence of another option. You can use the
        // "Get Option" call in advance to check the contents of the
        // <conflicting-options> group from a Get Option call for options
        // selected by end users or options available for a given service.
        $valid_options[] = [
          'option-code' => $optionCode
        ];
      }

      return $valid_options;
    }
}
