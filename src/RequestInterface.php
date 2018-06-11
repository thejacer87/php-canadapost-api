<?php

namespace CanadaPost;

use Psr\Log\LoggerInterface;

interface RequestInterface
{
    /**
     * @param LoggerInterface $logger
     */
    public function __construct(LoggerInterface $logger = null);

    /**
     * @param string $method The cURL method to send to Canada Post. (eg. GET, POST, etc.)
     * @param string[] $access The basic auth split in two strings. (eg. ['username', 'password'])
     * @param string $request The request xml
     * @param string $endpointurl The Canada Post API Endpoint URL
     * @param array $headers Extra headers to add to cURL request
     *
     * @return ResponseInterface
     */
    public function request($method, array $access, $request, $endpointurl, array $headers);

    /**
     * @param $access
     */
    public function setAccess($access);

    /**
     * @return string
     */
    public function getAccess();

    /**
     * @param $request
     */
    public function setRequest($request);

    /**
     * @return string
     */
    public function getRequest();

    /**
     * @param $endpointUrl
     */
    public function setEndpointUrl($endpointUrl);

    /**
     * @return string
     */
    public function getEndpointUrl();
}
