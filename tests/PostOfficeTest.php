<?php

namespace CanadaPost\Tests;

use CanadaPost\PostOffice;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;

/**
 * Post Office Class Tests.
 *
 * @group PostOffice
 */
class PostOfficeTest extends CanadaPostTestBase
{

    /**
     * The Post Office service.
     *
     * @var PostOffice
     */
    protected $postOfficeService;

    /**
     * Build the $postOfficeService to be tested.
     */
    public function setUp()
    {
        parent::setUp();
        $this->postOfficeService = new PostOffice($this->config);
    }

    /**
     * Test to get nearest post office from Canada Post.
     *
     * @covers PostOffice::getNearestPostOffice()
     * @test
     */
    public function getNearestPostOffice()
    {
        $body = file_get_contents(__DIR__
            . '/Mocks/post-office-nearest-success.xml');
        $mock = new MockHandler([
            new Response(200, [], $body),
        ]);
        $handler = HandlerStack::create($mock);
        $response = $this->postOfficeService
            ->getNearestPostOffice(
                '',
                '',
                '',
                ['handler' => $handler]
            );

        // Check response.
        $this->assertTrue(is_array($response['post-office-list']));

        $list = $response['post-office-list']['post-office'];

        $this->assertTrue(is_array($list[0]));

        $this->assertEquals('0.65', $list[0]['distance']);
        $this->assertEquals('CRYSTAL BEACH PO', $list[0]['location']);
        $this->assertEquals('SHOPPERS DRUG MART # 1387', $list[0]['name']);
        $this->assertEquals('0000102978', $list[0]['office-id']);
        $this->assertEquals('true', $list[0]['bilingual-designation']);

        $this->assertTrue(is_array($list[0]['link']));
        $this->assertEquals('https://qa.ct.soa-gw.canadapost.ca/rs/postoffice/0000102978/detail', $list[0]['link']['@attributes']['href']);

        $this->assertTrue(is_array($list[1]));

        $this->assertEquals('1.94', $list[1]['distance']);
        $this->assertEquals('NEPEAN H PO', $list[1]['location']);
        $this->assertEquals('NEPEAN H PO', $list[1]['name']);
        $this->assertEquals('0000313386', $list[1]['office-id']);
        $this->assertEquals('true', $list[1]['bilingual-designation']);

        $this->assertTrue(is_array($list[1]['link']));
        $this->assertEquals('https://qa.ct.soa-gw.canadapost.ca/rs/postoffice/0000313386/detail', $list[1]['link']['@attributes']['href']);

    }

    /**
     * Test to get nearest post office from Canada Post.
     *
     * @covers PostOffice::getNearestPostOfficeByGeographicalLocation()
     * @test
     */
    public function getNearestPostOfficeByGeographicalLocation()
    {
        $body = file_get_contents(__DIR__
            . '/Mocks/post-office-nearest-success.xml');
        $mock = new MockHandler([
            new Response(200, [], $body),
        ]);
        $handler = HandlerStack::create($mock);
        $response = $this->postOfficeService
            ->getNearestPostOfficeByGeographicalLocation(
                '',
                '',
                '',
                '',
                ['handler' => $handler]
            );

        // Check response.
        $this->assertTrue(is_array($response['post-office-list']));

        $list = $response['post-office-list']['post-office'];

        $this->assertTrue(is_array($list[0]));

        $this->assertEquals('0.65', $list[0]['distance']);
        $this->assertEquals('CRYSTAL BEACH PO', $list[0]['location']);
        $this->assertEquals('SHOPPERS DRUG MART # 1387', $list[0]['name']);
        $this->assertEquals('0000102978', $list[0]['office-id']);
        $this->assertEquals('true', $list[0]['bilingual-designation']);

        $this->assertTrue(is_array($list[0]['link']));
        $this->assertEquals('https://qa.ct.soa-gw.canadapost.ca/rs/postoffice/0000102978/detail', $list[0]['link']['@attributes']['href']);

        $this->assertTrue(is_array($list[1]));

        $this->assertEquals('1.94', $list[1]['distance']);
        $this->assertEquals('NEPEAN H PO', $list[1]['location']);
        $this->assertEquals('NEPEAN H PO', $list[1]['name']);
        $this->assertEquals('0000313386', $list[1]['office-id']);
        $this->assertEquals('true', $list[1]['bilingual-designation']);

        $this->assertTrue(is_array($list[1]['link']));
        $this->assertEquals('https://qa.ct.soa-gw.canadapost.ca/rs/postoffice/0000313386/detail', $list[1]['link']['@attributes']['href']);

    }

    /**
     * Test to get nearest post office from Canada Post.
     *
     * @covers PostOffice::getNearestPostOfficeByAddress()
     * @test
     */
    public function getNearestPostOfficeByAddress()
    {
        $body = file_get_contents(__DIR__
            . '/Mocks/post-office-nearest-success.xml');
        $mock = new MockHandler([
            new Response(200, [], $body),
        ]);
        $handler = HandlerStack::create($mock);
        $response = $this->postOfficeService
            ->getNearestPostOfficeByAddress(
                '',
                '',
                '',
                '',
                '',
                ['handler' => $handler]
            );

        // Check response.
        $this->assertTrue(is_array($response['post-office-list']));

        $list = $response['post-office-list']['post-office'];

        $this->assertTrue(is_array($list[0]));

        $this->assertEquals('0.65', $list[0]['distance']);
        $this->assertEquals('CRYSTAL BEACH PO', $list[0]['location']);
        $this->assertEquals('SHOPPERS DRUG MART # 1387', $list[0]['name']);
        $this->assertEquals('0000102978', $list[0]['office-id']);
        $this->assertEquals('true', $list[0]['bilingual-designation']);

        $this->assertTrue(is_array($list[0]['link']));
        $this->assertEquals('https://qa.ct.soa-gw.canadapost.ca/rs/postoffice/0000102978/detail', $list[0]['link']['@attributes']['href']);

        $this->assertTrue(is_array($list[1]));

        $this->assertEquals('1.94', $list[1]['distance']);
        $this->assertEquals('NEPEAN H PO', $list[1]['location']);
        $this->assertEquals('NEPEAN H PO', $list[1]['name']);
        $this->assertEquals('0000313386', $list[1]['office-id']);
        $this->assertEquals('true', $list[1]['bilingual-designation']);

        $this->assertTrue(is_array($list[1]['link']));
        $this->assertEquals('https://qa.ct.soa-gw.canadapost.ca/rs/postoffice/0000313386/detail', $list[1]['link']['@attributes']['href']);

    }

    /**
     * Test to get post office detail from Canada Post.
     *
     * @covers PostOffice::getPostOfficeDetail()
     * @test
     */
    public function getPostOfficeDetail()
    {
        $body = file_get_contents(__DIR__
            . '/Mocks/post-office-details-success.xml');
        $mock = new MockHandler([
            new Response(200, [], $body),
        ]);
        $handler = HandlerStack::create($mock);
        $response = $this->postOfficeService
            ->getPostOfficeDetail(
                '',
                ['handler' => $handler]
            );

        // Check response.
        $this->assertTrue(is_array($response['post-office-detail']));

        $address = $response['post-office-detail']['address'];
        $this->assertTrue(is_array($address));

        $this->assertEquals('GATINEAU', $address['city']);
        $this->assertEquals('45.5856', $address['latitude']);
        $this->assertEquals('-75.4132', $address['longitude']);
        $this->assertEquals('J8L1N0', $address['postal-code']);
        $this->assertEquals('QC', $address['province']);
        $this->assertEquals('563 RUE BELANGER', $address['office-address']);

        $this->assertEquals('BUCKINGHAM PO', $response['post-office-detail']['location']);
        $this->assertEquals('DÉPANNEUR MAUZEROLL', $response['post-office-detail']['name']);
        $this->assertEquals('0000319376', $response['post-office-detail']['office-id']);
        $this->assertEquals('true', $response['post-office-detail']['bilingual-designation']);
        $this->assertEquals('BUCKINGHAM PO', $response['post-office-detail']['location']);

        $hours = $response['post-office-detail']['hours-list'];

        $this->assertEquals('1', $hours[0]['day']);
        $this->assertEquals('08:00', $hours[0]['time'][0]);
        $this->assertEquals('00:00', $hours[0]['time'][1]);

        $this->assertEquals('2', $hours[1]['day']);
        $this->assertEquals('08:00', $hours[1]['time'][0]);
        $this->assertEquals('00:00', $hours[1]['time'][1]);

        $this->assertEquals('3', $hours[2]['day']);
        $this->assertEquals('08:00', $hours[2]['time'][0]);
        $this->assertEquals('00:00', $hours[2]['time'][1]);

        $this->assertEquals('4', $hours[3]['day']);
        $this->assertEquals('08:00', $hours[3]['time'][0]);
        $this->assertEquals('00:00', $hours[3]['time'][1]);

        $this->assertEquals('5', $hours[4]['day']);
        $this->assertEquals('08:00', $hours[4]['time'][0]);
        $this->assertEquals('00:00', $hours[4]['time'][1]);

        $this->assertEquals('6', $hours[5]['day']);
        $this->assertEquals('08:00', $hours[5]['time'][0]);
        $this->assertEquals('00:00', $hours[5]['time'][1]);

        $this->assertEquals('7', $hours[6]['day']);
        $this->assertEquals('08:00', $hours[6]['time'][0]);
        $this->assertEquals('00:00', $hours[6]['time'][1]);

    }

}
