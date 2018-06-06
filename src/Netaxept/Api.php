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

use FDM\Netaxept\Response\Factory;
use FDM\Netaxept\Response\QueryInterface;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Uri;
use Webmozart\Assert\Assert;

class Api
{
    const PRODUCTION_URL = 'https://epayment.nets.eu/';

    const SANDBOX_URL = 'https://test.epayment.nets.eu/';

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
     * @var Factory
     */
    protected $responseFactory;

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
        Factory $responseFactory,
        Client $client = null,
        bool $sandbox = false
    ) {
        $this->merchantId = $merchantId;
        $this->token = $token;
        $this->responseFactory = $responseFactory;
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
    public function getTransaction($transactionId)
    {
        $uri = $this->getUri('query', $this->getParameters(['transactionId' => $transactionId]));
        /** @var QueryInterface $response */
        $response = $this->performRequest((string) $uri);

        Assert::isInstanceOf($response, QueryInterface::class, 'Invalid response');

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

        Assert::notEq($xml->getName(), 'Exception', $xml->Error->Message);

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
}
