<?php

namespace CanadaPost;

use LSS\Array2XML;

class Rating extends ClientBase
{
    public function getRates($originPostalCode, $postalCode, $weight)
    {
        // Weight cannot be less than or equal to 0.
        $weight = $weight <= 0 ? 1 : $weight;
        // Canada Post API needs all postal codes to be uppercase and no spaces.
        $originPostalCode = strtoupper(str_replace(' ', '', $originPostalCode));
        $postalCode = strtoupper(str_replace(' ', '', $postalCode));

        // todo convert this to use Array2XML.
        $xmlRequest = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<mailing-scenario xmlns="http://www.canadapost.ca/ws/ship/rate-v3">
  <customer-number>{$this->customerNumber}</customer-number>
  <parcel-characteristics>
    <weight>{$weight}</weight>
  </parcel-characteristics>
  <origin-postal-code>{$originPostalCode}</origin-postal-code>
  <destination>
    <domestic>
      <postal-code>{$postalCode}</postal-code>
    </domestic>
  </destination>
</mailing-scenario>
XML;

      $response = $this->post(
        "rs/ship/price",
        [
          'Content-Type' => 'application/vnd.cpc.ship.rate-v3+xml',
          'Accept' => 'application/vnd.cpc.ship.rate-v3+xml',
        ],
        $xmlRequest
      );
      return $response;
    }
}
