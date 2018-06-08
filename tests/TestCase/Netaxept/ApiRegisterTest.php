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

use PHPUnit\Framework\Assert;

class ApiRegisterTest extends ApiTest
{
    /**
     * @expectedException \FDM\Netaxept\Exception\ValidationException
     * @expectedExceptionMessage Missing parameter: 'Order Number'
     */
    public function testMissingOrderId()
    {
        $this->getInstanceForRequestFixture('responses/register/missing_order_number.xml')->registerTransaction([]);
    }

    /**
     * @expectedException \FDM\Netaxept\Exception\ValidationException
     * @expectedExceptionMessage Missing parameter: 'Amount'
     */
    public function testMissingAmount()
    {
        $this->getInstanceForRequestFixture('responses/register/missing_amount.xml')->registerTransaction([]);
    }

    /**
     * @expectedException \FDM\Netaxept\Exception\ValidationException
     * @expectedExceptionMessage Missing parameter: 'Currency Code'
     */
    public function testMissingCurrencyCode()
    {
        $this->getInstanceForRequestFixture('responses/register/missing_currency_code.xml')->registerTransaction([]);
    }

    /**
     * @expectedException \FDM\Netaxept\Exception\GenericError
     * @expectedExceptionMessage Unable to translate supermerchant to submerchant, please check currency code and merchant ID
     */
    public function testInvalidCurrencyCode()
    {
        $this->getInstanceForRequestFixture('responses/register/invalid_currency_code.xml')->registerTransaction([]);
    }

    /**
     * @expectedException \FDM\Netaxept\Exception\UniqueTransactionIdException
     * @expectedExceptionMessage Transaction ID not unique
     */
    public function testNonUniqueTransactionId()
    {
        $this->getInstanceForRequestFixture('responses/register/non_unique_transaction_id.xml')->registerTransaction([]);
    }

    public function testRegisterSuccess()
    {
        $registerResponse = $this->getInstanceForRequestFixture('responses/register/success.xml')->registerTransaction([]);
        Assert::assertEquals('fdm-medlem-0000021', $registerResponse->getTransactionId(), 'Unexpected transaction ID');
    }
}
