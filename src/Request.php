<?php

namespace CanadaPost;

use DateTime;
use Exception;
use GuzzleHttp\Client as Guzzle;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use SimpleXMLElement;
use CanadaPost\Exception\InvalidResponseException;
use CanadaPost\Exception\RequestException;

class Request implements RequestInterface, LoggerAwareInterface
{
    /**
     * @var string
     */
    protected $access;

    /**
     * @var string
     */
    protected $request;

    /**
     * @var string
     */
    protected $endpointUrl;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var Guzzle
     */
    protected $client;

    /**
     * @param LoggerInterface $logger
     */
    public function __construct(LoggerInterface $logger = null)
    {
        if ($logger !== null) {
            $this->setLogger($logger);
        } else {
            $this->setLogger(new NullLogger);
        }

        $this->setClient();
    }

    /**
     * Sets a logger instance on the object.
     *
     * @param LoggerInterface $logger
     *
     * @return null
     */
    public function setLogger(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * Creates a single instance of the Guzzle client
     *
     * @return null
     */
    public function setClient()
    {
        $this->client = new Guzzle();
    }

    /**
     * Send request to Canada Post.
     *
     * @param string $method The cURL method to send to Canada Post. (eg. GET, POST, etc.)
     * @param string[] $access The basic auth split in two strings. (eg. ['username', 'password'])
     * @param string $request The request xml
     * @param string $endpointurl The Canada Post API Endpoint URL
     * @param array $headers Extra headers to add to cURL request
     *
     * @throws Exception
     * @throws \GuzzleHttp\Exception\GuzzleException
     *
     * @return ResponseInterface
     */
    public function request($method, array $access, $request, $endpointurl, array $headers)
    {
        $this->setAccess($access);
        $this->setRequest($request);
        $this->setEndpointUrl($endpointurl);
        $default_headers = [
                'Content-type' => 'application/x-www-form-urlencoded; charset=utf-8',
                'Accept-Charset' => 'UTF-8',
        ];

        $headers = array_merge($default_headers, $headers);

        // Log request
        $date = new DateTime();
        $id = $date->format('YmdHisu');
        $this->logger->info('Request To Canada Post API', [
                'id' => $id,
                'endpointurl' => $this->getEndpointUrl(),
        ]);

        $this->logger->debug('Request: ' . $this->getRequest(), [
                'id' => $id,
                'endpointurl' => $this->getEndpointUrl(),
        ]);

        try {
            $response = $this->client->request(
                    $method,
                    $this->getEndpointUrl(),
                    [
                            'body' => $this->getRequest(),
                            'headers' => $headers,
                            'http_errors' => true,
                            'auth' => $this->getAccess(),
                            'verify' => true,
                    ]
            );

            $body = (string)$response->getBody();

            $this->logger->info('Response from Canada Post API', [
                    'id' => $id,
                    'endpointurl' => $this->getEndpointUrl(),
            ]);

            $this->logger->debug('Response: ' . $body, [
                    'id' => $id,
                    'endpointurl' => $this->getEndpointUrl(),
            ]);

            if ($response->getStatusCode() === 200) {
                $body = $this->convertEncoding($body);

                $xml = new SimpleXMLElement($body);
                // todo handle invalid response.
                if (!empty($xml)) {
                    $responseInstance = new Response();

                    return $responseInstance->setText($body)->setResponse($xml);
                } else {
                    throw new InvalidResponseException('Failure: response is in an unexpected format.');
                }
            }
        } catch (\GuzzleHttp\Exception\TransferException $e) { // Guzzle: All of the exceptions extend from GuzzleHttp\Exception\TransferException
            $this->logger->alert($e->getMessage(), [
                    'id' => $id,
                    'endpointurl' => $this->getEndpointUrl(),
            ]);

            throw new RequestException('Failure: ' . $e->getMessage());
        }
    }

    /**
     * @param $access
     *
     * @return $this
     */
    public function setAccess($access)
    {
        $this->access = $access;

        return $this;
    }

    /**
     * @return string
     */
    public function getAccess()
    {
        return $this->access;
    }

    /**
     * @param $request
     *
     * @return $this
     */
    public function setRequest($request)
    {
        $this->request = $request;

        return $this;
    }

    /**
     * @return string
     */
    public function getRequest()
    {
        return $this->request;
    }

    /**
     * @param $endpointUrl
     *
     * @return $this
     */
    public function setEndpointUrl($endpointUrl)
    {
        $this->endpointUrl = $endpointUrl;

        return $this;
    }

    /**
     * @return string
     */
    public function getEndpointUrl()
    {
        return $this->endpointUrl;
    }

    /**
     * @param $body
     * @return string
     */
    protected function convertEncoding($body)
    {
        if (!function_exists('mb_convert_encoding')) {
            return $body;
        }

        $encoding = mb_detect_encoding($body);
        if ($encoding) {
            return mb_convert_encoding($body, 'UTF-8', $encoding);
        }

        return utf8_encode($body);
    }
}
