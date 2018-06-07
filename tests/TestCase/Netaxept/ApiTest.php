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

namespace Tests\TestCase\Netaxept;

use FDM\Netaxept\Api;
use FDM\Netaxept\Response\Factory;
use FDM\Netaxept\Traits\FileLoaderTrait;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;

class ApiTest extends TestCase
{
    use FileLoaderTrait;

    /**
     * Prepares an instance of the API object, mocked with the appropriate Guzzle client so it returns the specified
     * XML fixtures file as a response.
     *
     * @param string $fixturesFile
     *
     * @return Api
     */
    protected function getInstanceForRequestFixture($fixturesFile)
    {
        $mock = new MockHandler([
            new Response(200, ['Content-Type' => 'text/xml'], $this->fileGetContents($fixturesFile)),
        ]);
        $handler = HandlerStack::create($mock);
        $client = new Client(['handler' => $handler]);

        return new Api('placeholdermerchant', 'placeholdertoken', new Factory(), $client, true);
    }
}
