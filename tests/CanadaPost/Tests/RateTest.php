<?php

namespace CanadaPost\Tests;

use CanadaPost\Rate;
use CanadaPost\Request;
use PHPUnit_Framework_TestCase;
use SimpleXMLElement;

/**
 * Rate Class Tests.
 *
 * @group Rate
 */
class RateTest extends PHPUnit_Framework_TestCase
{

    /**
     * @var Rate
     */
    private $rate;

    public function setUp()
    {
        // Your username, password and customer number are imported
        // from the following file: CanadaPost\Tests\_files\user.ini
        $userProperties = parse_ini_file(__DIR__ . '/_files/user.ini');
        $username = $userProperties['username'];
        $password = $userProperties['password'];
        $customer_number = $userProperties['customerNumber'];
        $this->rate = new Rate($username, $password, $customer_number);
    }


    public function testGetRates()
    {
        $requestInstance = new Request();
        $endPointUrl = 'https://ct.soa-gw.canadapost.ca/rs/ship/price';
        $originPostalCode = 'H2B1A0';
        $postalCode = 'K1K4T3';
        $weight = 1;
        $request = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<mailing-scenario xmlns="http://www.canadapost.ca/ws/ship/rate-v3">
  <customer-number>{$this->rate->getCustomerNumber()}</customer-number>
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
        $headers = [
                'Accept' => 'application/vnd.cpc.ship.rate-v3+xml',
                'Content-type' => 'application/vnd.cpc.ship.rate-v3+xml'
        ];
        $response = $requestInstance->request('POST', [$this->rate->getUsername(), $this->rate->getPassword()], $request, $endPointUrl, $headers);
        if ($response->getResponse()->{'price-quote'} instanceof SimpleXMLElement) {
            /** @var SimpleXMLElement $service */
            foreach ($response->getResponse()->{'price-quote'} as $quote) {
                $price_due = $quote->{'price-details'}->due->__toString();
                self::assertTrue((float)$price_due >= 0);
            }
        }
    }

}
