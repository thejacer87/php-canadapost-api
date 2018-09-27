<?php

namespace CanadaPost\Tests;

use PHPUnit_Framework_TestCase;

/**
 * Canada Post API Test Base.
 */
abstract class CanadaPostTestBase extends PHPUnit_Framework_TestCase
{

    /**
     * The configuration array.
     *
     * @var array
     */
    protected $config;

    /**
     * Build the $ratingService to be tested.
     */
    public function setUp()
    {
        $this->config = [
            'username' => 'username',
            'password' => 'password',
            'customer_number' => 'customer_number',
            'contract_id' => 'contract_id',
        ];
    }

    protected function mockAddress()
    {
        return [
            'name' => 'John Smith',
            'company' => 'ACME',
            'address-details' => [
                'address-line-1' => '123 Main St',
                'city' => 'Ottawa',
                'prov-state' => 'ON',
                'country-code' => 'CA',
                'postal-zip-code' => 'K1A0B1',
            ],
        ];
    }

    protected function mockParcel()
    {
        return [
            'weight' => '15.00',
            'dimensions' => [
                'length' => '1',
                'height' => '1',
                'width' => '1'
            ],
            'service_code' => 'DOM.EP'
        ];
    }
}
