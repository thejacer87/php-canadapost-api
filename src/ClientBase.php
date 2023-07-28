<?php

namespace CanadaPost;

use CanadaPost\Exception\ClientException;
use DateTime;
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
     * Standard 2-character country codes.
     */
    const USA_COUNTRY_CODE = 'US';
    const CANADA_COUNTRY_CODE = 'CA';

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
     * The Canada Post API contract ID.
     *
     * @var array
     */
    protected $contractId;

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
     * @return \DOMDocument|\Psr\Http\Message\StreamInterface
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

        if (empty($options['raw_response'])) {
            return $this->parseResponse($response);
        }

        return $response->getBody();
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
    public function post(
        $endpoint,
        array $headers = [],
        $payload,
        array $options = []
    ) {
        $url = $this->baseUrl . '/' . $endpoint;

        try {
            $client = $this->buildClient($options);
            $options += [
                'auth' => [$this->username, $this->password],
                'headers' => $headers,
                'body' => $payload,
            ];

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
     * Send the DELETE request to the Canada Post API.
     *
     * @param string $endpoint
     *   The endpoint to send the request.
     * @param array $headers
     *   The HTTP headers array.
     * @param array $options
     *   An array of options.
     *
     * @return \DOMDocument|\Psr\Http\Message\StreamInterface
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function delete($endpoint, array $headers = [], array $options = [])
    {
        $url = $this->baseUrl . '/' . $endpoint;

        try {
            $client = $this->buildClient($options);
            $options += [
                'auth' => [$this->username, $this->password],
                'headers' => $headers,
            ];

            $response = $client->request('DELETE', $url, $options);
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
     * Send the GET request to the Canada Post API for a specific file.
     *
     * @param string $endpoint
     *   The endpoint to send the request.
     * @param string $fileType
     *   The file type of the file to retrieve.
     * @param array $options
     *   An array of options.
     *
     * @return \DOMDocument|\Psr\Http\Message\StreamInterface
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function getFile($endpoint, $fileType, array $options = [])
    {
        return $this->get(
            $endpoint,
            [
                'Accept' => 'application/' . $fileType,
                'Accept-Language' => 'en-CA',
            ],
            $options
        );
    }

    /**
     * Get an artifact from Canada Post server.
     * @param string $endpoint
     *   The endpoint of the file to retrieve from the Canada Post server.
     * @param array $options
     *   The options array.
     *
     * @return \DOMDocument|\Psr\Http\Message\StreamInterface
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function getArtifact($endpoint, array $options = [])
    {
        $response = $this->getFile(
            $endpoint,
            'pdf',
            array_merge($options, ['raw_response' => true])
        );
        return $response;
    }

    /**
     * Get the Canada Post-specific option codes,
     *
     * @return array
     *   The array of option codes.
     *
     * @see https://www.canadapost.ca/cpo/mc/business/productsservices/developers/services/rating/getrates/default.jsf
     */
    public static function getOptionCodes()
    {
        return [
            'SO' => 'Signature (SO)',
            'PA18' => 'Proof of Age Required - 18 (PA18)',
            'PA19' => 'Proof of Age Required - 19 (PA19)',
            'HFP' => 'Card for pickup (HFP)',
            'DNS' => 'Do not safe drop (DNS)',
            'LAD' => 'Leave at door - do not card (LAD)',
        ];
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
        $this->contractId = isset($config['contract_id']) ? $config['contract_id'] : null;
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
    protected function buildClient(array &$options)
    {
        if (!isset($options['debug']) && $this->config['env'] === self::ENV_DEVELOPMENT) {
            $options['debug'] = true;
        }

        if (!isset($options['handler'])) {
            return new GuzzleClient();
        }

        $client = new GuzzleClient(['handler' => $options['handler']]);
        unset($options['handler']);
        return $client;
    }

    /**
     * Helper function to format the postal code for an address.
     *
     * Canada Post API requires no spaces and uppercase postal code.
     *
     * @param string $postal_code
     *   The postal code to verify.
     */
    protected function formatPostalCode(&$postal_code)
    {
        strtoupper(str_replace(' ', '', $postal_code));
    }

    /**
     * Helper function to verify the dates are valid.
     *
     * @param string $from
     *   The beginning date.
     * @param $to
     *   The end date.
     * @param string $format
     *   The date format to verify.
     *
     * @throws \InvalidArgumentException
     */
    protected function verifyDates($from, $to, $format = 'YmdHs')
    {
        if (!DateTime::createFromFormat($format, $from)) {
            $message = 'The $from date is improperly formatted. Please use "YmdHs".';
            throw new \InvalidArgumentException($message);
        }
        if (!DateTime::createFromFormat($format, $to)) {
            $message = 'The $to date is improperly formatted. Please use "YmdHs".';
            throw new \InvalidArgumentException($message);
        }
        if (new DateTime($from) > new DateTime($to)) {
            $message = 'The $from date cannot be a later date than the $to date.';
            throw new \InvalidArgumentException($message);
        }
    }

    /**
     * Helper function to extract the option codes.
     *
     * @param array $options
     *   The options array.
     *
     * @return array
     *   The list of options with the option-code.
     */
    protected function parseOptionCodes(array $options)
    {
        $valid_options = [];
        foreach ($options['option_codes'] as $optionCode) {
            if (!array_key_exists(strtoupper($optionCode), self::getOptionCodes())) {
                $message = sprintf(
                    'Unsupported option code: "%s". Supported options are %s',
                    $optionCode,
                    implode(', ', array_keys(self::getOptionCodes()))
                );
                throw new \InvalidArgumentException($message);
            }
            // @todo Perhaps we should check for conflicts here, might be overkill.
            // From Canada Post docs:
            // There are some options that can be applied to a shipment that
            // conflict with the presence of another option. You can use the
            // "Get Option" call in advance to check the contents of the
            // <conflicting-options> group from a Get Option call for options
            // selected by end users or options available for a given service.
            $valid_options[] = [
                'option-code' => $optionCode,
            ];
        }

        return $valid_options;
    }
}
