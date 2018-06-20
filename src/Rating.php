<?php

namespace CanadaPost;

use LSS\Array2XML;

class Rating extends ClientBase
{
    public function getRates($originPostalCode, $postalCode, $weight)
    {
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
