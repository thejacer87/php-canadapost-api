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

    public function get($endpoint, array $headers = [])
    {
        $url = $this->baseUrl . '/' . $endpoint;

        try {
            $client = new GuzzleClient();
            $options = [
                'auth' => [$this->username, $this->password],
                'headers' => $headers,
            ];

            // Enable debug option on development environment.
            if ($this->config['env'] === self::ENV_DEVELOPMENT) {
                $options['debug'] = TRUE;
            }

            $response = $client->request('GET', $url, $options);
        }
        catch (GuzzleClientException $exception) {
            $response = $exception->getResponse();
            $body = $this->parseResponse($response);

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
        if (!isset($config['username']) || !isset($config['password'])) {
            $message = 'A username and a password are required for authenticated to the Canada Post API.';
            throw new \InvalidArgumentException($message);
        }

        $this->username = $config['username'];
        $this->password = $config['password'];
    }

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
