<?php

/*
 * This file is part of the Netaxept API package.
 *
 * (c) Andrew Plank
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace FDM\Netaxept;

use FDM\Netaxept\Exception\Exception;
use FDM\Netaxept\Exception\Factory as ExceptionFactory;
use FDM\Netaxept\Response\ErrorInterface;
use FDM\Netaxept\Response\Factory as ResponseFactory;
use FDM\Netaxept\Response\ProcessInterface;
use FDM\Netaxept\Response\QueryInterface;
use FDM\Netaxept\Response\RegisterInterface;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Uri;
use Webmozart\Assert\Assert;

class Api
{
    const PRODUCTION_URL = 'https://epayment.nets.eu/';

    const SANDBOX_URL = 'https://test.epayment.nets.eu/';

    const OPERATION_AUTH = 'auth';

    const OPERATION_VERIFY = 'verify';

    const OPERATION_SALE = 'sale';

    const OPERATION_CAPTURE = 'capture';

    const OPERATION_REFUND = 'credit';

    const OPERATION_CANCEL = 'annul';

    const ENDPOINTS = [
        'register' => 'Netaxept/Register.aspx',
        'process' => 'Netaxept/Process.aspx',
        'query' => 'Netaxept/Query.aspx',
    ];

    /**
     * @var string
     */
    protected $merchantId;

    /**
     * @var string
     */
    protected $token;

    /**
     * @var ResponseFactory
     */
    protected $responseFactory;

    /**
     * @var ExceptionFactory
     */
    protected $exceptionFactory;

    /**
     * @var Client
     */
    protected $client;

    /**
     * @var bool
     */
    protected $sandbox;

    public function __construct(
        string $merchantId,
        string $token,
        ResponseFactory $responseFactory = null,
        ExceptionFactory $exceptionFactory = null,
        Client $client = null,
        bool $sandbox = false
    ) {
        $this->merchantId = $merchantId;
        $this->token = $token;
        $this->responseFactory = $responseFactory ? $responseFactory : new ResponseFactory();
        $this->exceptionFactory = $exceptionFactory ? $exceptionFactory : new ExceptionFactory();
        $this->client = $client ? $client : new Client();
        $this->sandbox = $sandbox;
    }

    /**
     * Get a Query response object for the provided transaction ID.
     *
     * @param string $transactionId
     *
     * @return QueryInterface
     */
    public function getTransaction($transactionId): QueryInterface
    {
        $uri = $this->getUri('query', $this->getParameters(['transactionId' => $transactionId]));
        /** @var QueryInterface $response */
        $response = $this->performRequest((string) $uri);

        Assert::isInstanceOf($response, QueryInterface::class, 'Invalid response');
        Assert::isInstanceOf($response, ErrorInterface::class, 'Invalid response');

        return $response;
    }

    /**
     * Registers a transaction
     *
     * @param array $transactionData
     *
     * @return RegisterInterface
     */
    public function registerTransaction(array $transactionData): RegisterInterface
    {
        $uri = $this->getUri('register', $this->getParameters($transactionData));
        /** @var RegisterInterface $response */
        $response = $this->performRequest((string) $uri);

        Assert::isInstanceOf($response, RegisterInterface::class, 'Invalid response');

        return $response;
    }

    /**
     * Processes a transaction
     *
     * @param array $transactionData
     *
     * @return ProcessInterface
     */
    public function processTransaction(array $transactionData, string $operation): ProcessInterface
    {
        $uri = $this->getUri('process', $this->getParameters($transactionData + ['operation' => $operation]));
        /** @var ProcessInterface $response */
        $response = $this->performRequest((string) $uri);

        Assert::isInstanceOf($response, ProcessInterface::class, 'Invalid response');

        return $response;
    }

    /**
     * Performs a request to the provided URI, and returns the appropriate response object, based on the content of the
     * HTTP response.
     *
     * @param string $uri
     *
     * @return mixed
     */
    protected function performRequest(string $uri)
    {
        $httpResponse = $this->client->get($uri);
        Assert::eq($httpResponse->getStatusCode(), 200, 'Request failed.');

        $content = $httpResponse->getBody()->getContents();
        $xml = simplexml_load_string($content);

        $this->exceptionOnError($xml);

        return $this->responseFactory->getResponse($xml);
    }

    /**
     * Returns the provided parameters, along with the merchantId and token that's required in every request.
     *
     * @param array $parameters
     *
     * @return array
     */
    protected function getParameters(array $parameters)
    {
        return [
            'merchantId' => $this->merchantId,
            'token' => $this->token,
        ] + $parameters;
    }

    /**
     * Builds a URI, based on the named endpoint and provided parameters. The Netaxept API claims to be a REST API, but
     * it is not in any way, shape, or form a REST API. It requires all requests to be GET requests, and all parameters
     * to be URLencoded and provided in the query string. About as far away from REST as you can get.
     *
     * @param string $endpoint
     * @param array $parameters
     *
     * @return \Psr\Http\Message\UriInterface
     */
    protected function getUri(string $endpoint, array $parameters = [])
    {
        Assert::keyExists(self::ENDPOINTS, $endpoint, "Named endpoint {$endpoint} is unknown.");

        $uri = $this->sandbox ? self::SANDBOX_URL : self::PRODUCTION_URL;
        $uri .= self::ENDPOINTS[$endpoint];

        if ($parameters) {
            $uri .= '?' . http_build_query($parameters);
        }

        return new Uri($uri);
    }

    /**
     * @param \SimpleXMLElement $xml
     *
     * @throws Exception
     */
    protected function exceptionOnError(\SimpleXMLElement $xml)
    {
        if ($xml->getName() == 'Exception') {
            throw $this->exceptionFactory->getException($xml);
        }
    }
}
