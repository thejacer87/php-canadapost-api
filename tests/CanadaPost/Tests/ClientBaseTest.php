<?php

namespace CanadaPost\Tests;

use CanadaPost\ClientBase;
use PHPUnit_Framework_TestCase;

/**
 * ClientBase Class Tests.
 * @group ClientBase
 */
class ClientBaseTest extends PHPUnit_Framework_TestCase
{
    /**
     * Mock Canada Post API credentials.
     */
    const API_USERNAME = '1234567';
    const API_PASSWORD = 'my_secret_password';
    const API_CUSTOMER_NUMBER = '123456789abcdefghi';

    /**
     * @var ClientBase $clientBase
     */
    protected $clientBase;

    /**
     * Build the $clientBase to be tested.
     */
    public function setUp()
    {
        $config = [
            'username' => $this::API_USERNAME,
            'password' => $this::API_PASSWORD,
            'customer_number' => $this::API_CUSTOMER_NUMBER,
        ];
        $this->clientBase = $this->getMockForAbstractClass('\CanadaPost\ClientBase', ['config' => $config]);
    }


    /**
     * Test the config array.
     * @covers ClientBase::getCredentials()
     * @test
     */
    public function getCredentials()
    {
        $config = $this->clientBase->getCredentials();
        $this->assertEquals($this::API_USERNAME, $config['username']);
        $this->assertEquals($this::API_PASSWORD, $config['password']);
        $this->assertEquals($this::API_CUSTOMER_NUMBER, $config['customer_number']);
    }

}
