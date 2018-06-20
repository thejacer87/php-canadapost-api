<?php

namespace CanadaPost;

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
        // Set up curl request.
        $curl = curl_init('https://ct.soa-gw.canadapost.ca/rs/ship/price');
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 2);
        curl_setopt($curl, CURLOPT_POST, TRUE);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $xmlRequest);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
        curl_setopt($curl, CURLOPT_USERPWD, $this->username . ':' . $this->password);
        curl_setopt(
                $curl, CURLOPT_HTTPHEADER, array(
                        'Content-Type: application/vnd.cpc.ship.rate-v3+xml',
                        'Accept: application/vnd.cpc.ship.rate-v3+xml',
                )
        );
        $curl_response = curl_exec($curl);

        $xml = simplexml_load_string('<root>' . preg_replace('/<\?xml.*\?>/','',$curl_response) . '</root>');
        $canadapost_rates = [];
        if ($xml && $xml->{'price-quotes'}) {
            $priceQuotes = $xml->{'price-quotes'}->children('http://www.canadapost.ca/ws/ship/rate-v3');
            if ($priceQuotes->{'price-quote'}) {
                foreach ($priceQuotes as $priceQuote) {
                    $code = $priceQuote->{'service-code'}->__toString();
                    $price = $priceQuote->{'price-details'}->{'due'}->__toString();
                    $service_name = $priceQuote->{'service-name'}->__toString();
                    $canadapost_rates[] = [
                            'code' => $code,
                            'price' => $price,
                            'name' => $service_name,
                    ];
                }
            }
        }
        return $canadapost_rates;
    }
}
