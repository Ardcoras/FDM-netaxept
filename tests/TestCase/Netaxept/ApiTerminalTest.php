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
use PHPUnit\Framework\Assert;

class ApiTerminalTest extends ApiTest
{
    /**
     * Test that the terminal URI is correct when in the sandbox
     */
    public function testSandboxTerminalUri()
    {
        Assert::assertEquals(
            'https://test.epayment.nets.eu/Terminal/Default.aspx?merchantId=placeholdermerchant&transactionId=123456',
            (new Api('placeholdermerchant', 'placeholdertoken', null, null, null, true))->getTerminalUri('123456')
        );
    }

    /**
     * Test that the terminal URI is correct when in production
     */
    public function testProductionTerminalUri()
    {
        Assert::assertEquals(
            'https://epayment.nets.eu/Terminal/Default.aspx?merchantId=placeholdermerchant&transactionId=123456',
            (new Api('placeholdermerchant', 'placeholdertoken'))->getTerminalUri('123456')
        );
    }
}
