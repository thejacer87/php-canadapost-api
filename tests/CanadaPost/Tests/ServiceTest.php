<?php

namespace CanadaPost\Tests;

use CanadaPost\Request;
use PHPUnit_Framework_TestCase;
use SimpleXMLElement;

/**
 * Service Class Tests.
 *
 * @group Rate
 */
class ServiceTest extends PHPUnit_Framework_TestCase
{

    private $services;

    public function setUp()
    {
        $this->services = [
                'DOM.EP',
                'DOM.RP',
                'DOM.PC',
                'DOM.XP',
                'INT.PW.ENV',
                'USA.PW.ENV',
                'USA.PW.PAK',
                'INT.PW.PAK',
                'INT.PW.PARCEL',
                'USA.PW.PARCEL',
                'INT.XP',
                'INT.IP.AIR',
                'INT.IP.SURF',
                'INT.TP',
                'INT.SP.SURF',
                'INT.SP.AIR',
                'USA.XP',
                'USA.EP',
                'USA.TP',
                'USA.SP.AIR'
        ];
    }

    public function testGetServices()
    {
        // Your username, password and customer number are imported
        // from the following file: CanadaPost\Tests\_files\user.ini
        $userProperties = parse_ini_file(__DIR__ . '/_files/user.ini');
        $username = $userProperties['username'];
        $password = $userProperties['password'];
        $requestInstance = new Request();
        $endPointUrl = 'https://ct.soa-gw.canadapost.ca/rs/ship/service';
        $accept = 'application/vnd.cpc.ship.rate-v3+xml';
        $headers = [
                'Accept' => $accept,
                'Content-type' => ' application/vnd.cpc.ship.rate-v3+xml'
        ];
        $response = $requestInstance->request('GET', [$username, $password], '', $endPointUrl, $headers);
        if ($response->getResponse()->service instanceof SimpleXMLElement) {
            /** @var SimpleXMLElement $service */
            foreach ($response->getResponse()->service as $service) {
                self::assertTrue(in_array($service->{'service-code'}->__toString(), $this->services));
            }
        }
    }

}
