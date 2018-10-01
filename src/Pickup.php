<?php

namespace CanadaPost;

use LSS\Array2XML;

class Pickup extends ClientBase
{

    /**
     * Create a request for a one-time on-demand pickup.
     *
     * @param array $contact
     *   Contact info for the pickup.
     *   <code>
     *     $contact = [
     *       'name'  => 'John Smith',
     *       'email' => 'jsmith@email.com',
     *       'phone' => '250-555-5555'
     *     ]
     *   </code>
     * @param string $instructions
     *   Instructions for the driver. Max 132 characters.
     * @param string $pickup_volume
     *   The expected number of items to be picked up. You can also include
     * additional details, such as '50 parcels and 10 packets'.
     * @param array $pickup_time
     *   The preferred time for the on-demand pickup. Must be between noon
     * (12:00) and 4 p.m. (16:00), in 15-minute intervals (i.e. minutes must
     * be 00, 15, 30 or 45).
     *   <code>
     *     $pickup_time = [
     *       'date'           => '2018-10-01',  // YYYY-MM-DD
     *       'preferred-time' => '14:15'        // hh:mm
     *       'closing-time'   => '16:00'        // hh:mm
     *     ]
     *   </code>
     * @param array $pickup_location
     *   If this parameter is not set, the pickup location is automatically set
     *   to the business address specified in your Canada Post profile.
     *   <code>
     *     $pickup_location = [
     *       'company'        => 'John Smith',
     *       'address-line-1' => '103-2303 Leckie Rd',
     *       'city'           => 'Kelowna'
     *       'postal-code'    => 'V1X 6Y5'
     *     ]
     *   </code>
     * @param array $options
     *   The options to pass along to the Guzzle Client.
     *
     * @see https://www.canadapost.ca/cpo/mc/business/productsservices/developers/services/parcelpickup/createpickup.jsf
     * @return \DOMDocument
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function createPickupRequest(
        array $contact,
        $instructions,
        $pickup_volume,
        array $pickup_time,
        array $pickup_location = [],
        array $options = []
    ) {
        $content = $this->buildPickupRequest($contact, $instructions,
            $pickup_volume, $pickup_time, $pickup_location, $options);

        $xml = Array2XML::createXML('pickup-request-details', $content);
        $envelope = $xml->documentElement;
        $envelope->setAttribute('xmlns',
            'http://www.canadapost.ca/ws/pickuprequest');
        $payload = $xml->saveXML();

        $response = $this->post(
            "enab/{$this->customerNumber}/pickuprequest",
            [
                'Accept' => 'application/vnd.cpc.pickuprequest+xml',
                'Content-Type' => 'application/vnd.cpc.pickuprequest+xml',
            ],
            $payload,
            $options
        );

        return $response;
    }

    /**
     * Helper function to build the content array.
     *
     * @param array $contact
     *   Contact info for the pickup.
     * @param $instructions
     * @param string $pickup_volume
     *   The expected number of items to be picked up.
     * @param array $pickup_time
     * @param array $pickup_location
     * @param array $options
     *   The options array.
     *
     * @return array
     *   The content array.
     */
    protected function buildPickupRequest(
        array $contact,
        $instructions,
        $pickup_volume,
        array $pickup_time,
        array $pickup_location = [],
        array $options = []
    ) {
        $pickup_location = [
            'business-address-flag' => true,
        ];

        if (!empty($pickup_location_details)) {
            $pickup_location = [
                'business-address-flag' => false,
                'alternate-address' => [
                    'company' => $pickup_location_details['company'],
                    'address-line-1' => $pickup_location_details['address-line-1'],
                    'city' => $pickup_location_details['city'],
                    'postal-code' => $pickup_location_details['postal-code'],
                ]
            ];
        }

        $pickup_info = [
            'pickup-type' => 'OnDemand',
            'pickup-location' => $pickup_location,
            'contact-info' => [
                'contact-name' => $contact['name'],
                'email' => $contact['email'],
                'contact-phone' => $contact['phone'],
                'opt-out-email-updates-flag' => true,
                'receive-updates-flag' => true,
            ],
            'location-details' => [
                'pickup-instructions' => $instructions,
            ],
            'pickup-volume' => $pickup_volume,
            'pickup-times' => [
                'on-demand-pickup-time' => $pickup_time,
            ],
        ];

        if (!empty($options['option_codes'])) {
            $pickup_info['options']['option'] = $this->parseOptionCodes($options);
        }

        return $pickup_info;
    }
}
