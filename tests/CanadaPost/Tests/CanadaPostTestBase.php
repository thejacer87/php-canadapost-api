<?php

namespace CanadaPost\Tests;

use PHPUnit_Framework_TestCase;

/**
 * CanadaPostTestBase Class Tests.
 */
class CanadaPostTestBase extends PHPUnit_Framework_TestCase
{
    protected $config;

    public function setUp()
    {
     $this->config = [
        'customer_number' => API_CUSTOMER_NUMBER,
        'username' => API_USERNAME,
        'password' => API_PASSWORD,
     ];
    }


    /**
     * Test the authentication.
     *
     * @test
     */
    public function testAuth()
    {
        $this->assertEquals(API_USERNAME, $this->config['username']);
        $this->assertEquals(API_CUSTOMER_NUMBER, $this->config['customer_number']);
        $this->assertEquals(API_PASSWORD, $this->config['password']);
    }

}
