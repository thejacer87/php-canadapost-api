<?php

namespace CanadaPost;

use DOMDocument;
use Exception;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;
use SimpleXMLElement;
use stdClass;

abstract class CanadaPost implements LoggerAwareInterface
{
    const BASE_URL = 'https://ct.soa-gw.canadapost.ca/rs/';

    /**
     * @var string
     */
    protected $username;

    /**
     * @var string
     */
    protected $password;

    /**
     * @var string
     */
    protected $customerNumber;

    /**
     * @return string
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * @param string $username
     */
    public function setUsername($username)
    {
        $this->username = $username;
    }

    /**
     * @return string
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * @param string $password
     */
    public function setPassword($password)
    {
        $this->password = $password;
    }

    /**
     * @return string
     */
    public function getCustomerNumber()
    {
        return $this->customerNumber;
    }

    /**
     * @param string $customerNumber
     */
    public function setCustomerNumber($customerNumber)
    {
        $this->customerNumber = $customerNumber;
    }

    /**
     * @var string
     */
    protected $context;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * Constructor.
     *
     * @param string|null $username Canada Post username
     * @param string|null $password Canada Post password
     * @param string|null $customerNumber Canada Post customer number
     * @param LoggerInterface|null $logger PSR3 compatible logger (optional)
     */
    public function __construct(
            $username = null,
            $password = null,
            $customerNumber = null,
            LoggerInterface $logger = null
    ) {
        $this->username = $username;
        $this->password = $password;
        $this->customerNumber = $customerNumber;
        $this->logger = $logger;
    }

    /**
     * Sets the transaction / context value.
     *
     * @param string $context The transaction "guidlikesubstance" value
     */
    public function setContext($context)
    {
        $this->context = $context;
    }

    /**
     * @param LoggerInterface $logger
     */
    public function setLogger(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * @return LoggerInterface
     */
    public function getLogger()
    {
        return $this->logger;
    }

    /**
     * Format a Unix timestamp or a date time with a Y-m-d H:i:s format into a YYYYMMDDHHmmss format required by Canada Post.
     *
     * @param string
     *
     * @return string
     */
    public function formatDateTime($timestamp)
    {
        if (!is_numeric($timestamp)) {
            $timestamp = strtotime($timestamp);
        }

        return date('YmdHis', $timestamp);
    }

    /**
     * Create the access request.
     *
     * @return string
     */
    protected function createAccess()
    {
        $xml = new DOMDocument();
        $xml->formatOutput = true;

        // Create the AccessRequest element
        $accessRequest = $xml->appendChild($xml->createElement('AccessRequest'));
        $accessRequest->setAttribute('xml:lang', 'en-US');

        $accessRequest->appendChild($xml->createElement('username', $this->username));
        $accessRequest->appendChild($xml->createElement('customerNumber', $this->customerNumber));

        $p = $accessRequest->appendChild($xml->createElement('password'));
        $p->appendChild($xml->createTextNode($this->password));

        return $xml->saveXML();
    }

    /**
     * Creates the TransactionReference node for a request.
     *
     * @return \DomNode
     */
    protected function createTransactionNode()
    {
        $xml = new DOMDocument();
        $xml->formatOutput = true;

        $trxRef = $xml->appendChild($xml->createElement('TransactionReference'));

        if (null !== $this->context) {
            $trxRef->appendChild($xml->createElement('CustomerContext', $this->context));
        }

        return $trxRef->cloneNode(true);
    }

    /**
     * Send request to Canada Post.
     *
     * @param string $access The access request xml
     * @param string $request The request xml
     * @param string $endpointurl The Canada Post API Endpoint URL
     *
     * @throws Exception
     *
     * @return SimpleXMLElement
     *
     * @deprecated Untestable
     */
    protected function request($method, array $access, $request, $endpointurl, array $headers)
    {
        $requestInstance = new Request($this->logger);
        $response = $requestInstance->request($method, $access, $request, $endpointurl, $headers);
        if ($response->getResponse() instanceof SimpleXMLElement) {
            return $response->getResponse();
        }

        throw new Exception('Failure: Response is invalid.');
    }

    /**
     * Convert XMLSimpleObject to stdClass object.
     *
     * @param SimpleXMLElement $xmlObject
     *
     * @return stdClass
     */
    protected function convertXmlObject(SimpleXMLElement $xmlObject)
    {
        return json_decode(json_encode($xmlObject));
    }

    /**
     * Compiles the final endpoint URL for the request.
     *
     * @param string $segment The URL segment to build in to the endpoint
     *
     * @return string
     */
    protected function compileEndpointUrl($segment)
    {
        return $this::BASE_URL . $segment;
    }
}
