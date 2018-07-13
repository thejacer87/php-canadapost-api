<?php

namespace CanadaPost;

use CanadaPost\Exception\ClientException;
use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Exception\ClientException as GuzzleClientException;
use GuzzleHttp\Psr7\Response;
use LSS\XML2Array;

/**
 * Provides a base class for Canada Post API clients.
 */
abstract class ClientBase
{
    const ENV_DEVELOPMENT = 'dev';
    const ENV_PRODUCTION = 'prod';
    const BASE_URL_DEVELOPMENT = 'https://ct.soa-gw.canadapost.ca';
    const BASE_URL_PRODUCTION = 'https://soa-gw.canadapost.ca';

    protected $config;
    protected $baseUrl;
    protected $username;
    protected $password;
    protected $customerNumber;

    public function __construct(array $config = [])
    {
        // Default to development environment.
        $this->config = array_merge(
            ['env' => self::ENV_DEVELOPMENT],
            $config
        );
        $this->setCredentials($config);
        $this->baseUrl($this->config);
    }

    /**
     * Send the GET request to the Canada Post API.
     *
     * @param string $endpoint
     *   The endpoint to send the request.
     * @param array $headers
     *   The HTTP headers array.
     * @param array $options
     *   The options array.
     *
     * @return \DOMDocument
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function get($endpoint, array $headers = [], array $options = [])
    {
        $url = $this->baseUrl . '/' . $endpoint;

        try {
            $client = new GuzzleClient();
            if (isset($options['handler'])) {
                $client = new GuzzleClient(['handler' => $options['handler']]);
                unset($options['handler']);
            }
            $options += [
                'auth' => [$this->username, $this->password],
                'headers' => $headers,
            ];

            // Enable debug option on development environment.
            if ($this->config['env'] === self::ENV_DEVELOPMENT) {
                $options['debug'] = TRUE;
            }

            $response = $client->request('GET', $url, $options);
        } catch (GuzzleClientException $exception) {
            $response = $exception->getResponse();

            throw new ClientException(
                $exception->getMessage(),
                $this->parseResponse($response),
                $exception->getRequest(),
                $response,
                $exception->getPrevious(),
                $exception->getHandlerContext()
            );
        }

        return $this->parseResponse($response);
    }

    /**
     * Send the POST request to the Canada Post API.
     *
     * @param string $endpoint
     *   The endpoint to send the request.
     * @param array $headers
     *   The HTTP headers array.
     * @param string $payload
     *   The payload to POST.
     * @param array $options
     *   The options array.
     *
     * @return \DOMDocument
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function post($endpoint, array $headers = [], $payload, array $options = [])
    {
        $url = $this->baseUrl . '/' . $endpoint;

        try {
            $client = new GuzzleClient();
            if (isset($options['handler'])) {
                $client = new GuzzleClient(['handler' => $options['handler']]);
                unset($options['handler']);
            }
            $options += [
                'auth' => [$this->username, $this->password],
                'headers' => $headers,
                'body' => $payload,
            ];

            // Enable debug option on development environment.
            if ($this->config['env'] === self::ENV_DEVELOPMENT) {
                $options['debug'] = TRUE;
            }

            $response = $client->request('POST', $url, $options);
        } catch (GuzzleClientException $exception) {
            $response = $exception->getResponse();

            throw new ClientException(
                $exception->getMessage(),
                $this->parseResponse($response),
                $exception->getRequest(),
                $response,
                $exception->getPrevious(),
                $exception->getHandlerContext()
            );
        }

        return $this->parseResponse($response);
    }

    protected function setCredentials(array $config = [])
    {
        if (!isset($config['username']) || !isset($config['password']) || !isset($config['customer_number'])) {
            $message = 'A username, password and customer number are required for authenticated to the Canada Post API.';
            throw new \InvalidArgumentException($message);
        }

        $this->username = $config['username'];
        $this->password = $config['password'];
        $this->customerNumber = $config['customer_number'];
    }

    /**
     * Get the API configuration array from the Client.
     * @return array
     *   The configuration array.
     */
    public function getCredentials()
    {
        return $this->config;
    }

    /**
     * Return the base url for the Canada Post API.
     *
     * @param array $config
     *
     * @return mixed|string
     *   The base url.
     * @throws \InvalidArgumentException
     */
    protected function baseUrl(array $config = [])
    {
        if (isset($this->baseUrl)) {
            return $this->baseUrl;
        }

        if (isset($config['base_url'])) {
            $this->baseUrl = $config['base_url'];
            return $this->baseUrl;
        }

        switch ($config['env']) {
            case self::ENV_DEVELOPMENT:
                $this->baseUrl = self::BASE_URL_DEVELOPMENT;
                break;

            case self::ENV_PRODUCTION:
                $this->baseUrl = self::BASE_URL_PRODUCTION;
                break;

            default:
                $message = sprintf(
                    'Unsupported environment "%s". Supported environments are "%s"',
                    $config['env'],
                    implode(', ', [self::ENV_DEVELOPMENT, self::ENV_PRODUCTION])
                );
                throw new \InvalidArgumentException($message);
        }

        return $this->baseUrl;
    }

    protected function parseResponse(Response $response)
    {
        $xml = new \DomDocument();
        $xml->loadXML($response->getBody());

        return XML2Array::createArray($xml->saveXML());
    }
}
