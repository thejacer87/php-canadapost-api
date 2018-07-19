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
    /**
     * Environment variables.
     */
    const ENV_DEVELOPMENT = 'dev';
    const ENV_PRODUCTION = 'prod';

    /**
     * Base URLs.
     */
    const BASE_URL_DEVELOPMENT = 'https://ct.soa-gw.canadapost.ca';
    const BASE_URL_PRODUCTION = 'https://soa-gw.canadapost.ca';

    /**
     * The configuration array.
     *
     * @var array
     */
    protected $config;

    /**
     * The base Canada Post API url.
     *
     * @var string
     */
    protected $baseUrl;

    /**
     * The Canada Post API username.
     *
     * @var array
     */
    protected $username;

    /**
     * The Canada Post API password.
     *
     * @var array
     */
    protected $password;

    /**
     * The Canada Post API customer number.
     *
     * @var array
     */
    protected $customerNumber;

    /**
     * ClientBase constructor.
     *
     * @param array $config
     *   The configuration array.
     */
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
     *   An array of options. Supported options are all request options
     * supported by Guzzle http://docs.guzzlephp.org/en/stable/request-options.html
     * plus the following:
     *     - handler: Don't use unless you have a valid reason or for unit
     *       testing - http://docs.guzzlephp.org/en/stable/testing.html#mock-handler
     *
     * @return \DOMDocument
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function get($endpoint, array $headers = [], array $options = [])
    {
        $url = $this->baseUrl . '/' . $endpoint;

        try {
            $client = $this->buildClient($options);
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
     *   The options array. Supported options are all request options
     * supported by Guzzle http://docs.guzzlephp.org/en/stable/request-options.html
     * plus the following:
     *     - handler: Don't use unless you have a valid reason or for unit
     *       testing - http://docs.guzzlephp.org/en/stable/testing.html#mock-handler
     *
     * @return \DOMDocument
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function post($endpoint, array $headers = [], $payload, array $options = [])
    {
        $url = $this->baseUrl . '/' . $endpoint;

        try {
            $client = $this->buildClient($options);
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

    /**
     * Set the API configuration array for the Client.
     *
     * @param array $config
     *   The configuration array.
     */
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

    /**
     * Parse the xml response into an array,
     *
     * @param Response $response
     *   The xml response.
     *
     * @return \DOMDocument
     *   The response array.
     * @throws \Exception
     */
    protected function parseResponse(Response $response)
    {
        $xml = new \DomDocument();
        $xml->loadXML($response->getBody());

        return XML2Array::createArray($xml->saveXML());
    }

    /**
     * Build the Guzzle client.
     *
     * @param array $options
     *   The options array.
     *
     * @return \GuzzleHttp\Client
     */
    protected function buildClient(array &$options) {
        if (!isset($options['debug']) && $this->config['env'] === self::ENV_DEVELOPMENT) {
            $options['debug'] = TRUE;
        }

        if (!isset($options['handler'])) {
            return new GuzzleClient();
        }

        $client = new GuzzleClient(['handler' => $options['handler']]);
        unset($options['handler']);
        return $client;
    }
}
