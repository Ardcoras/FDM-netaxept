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

use FDM\Netaxept\Response\Exception;
use FDM\Netaxept\Response\Query;
use FDM\Netaxept\Response\QueryInterface;
use PHPUnit\Framework\Assert;

class ApiQueryTest extends ApiTest
{
    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Test: Unable to authenticate merchant (credentials not passed)
     */
    public function testInvalidAuthMissingCredentials()
    {
        $this->getInstanceForRequestFixture('responses/query/auth_failed.xml')->getTransaction('placeholder');
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Authentication failed (Test) MerchantId: 1337
     */
    public function testInvalidAuthInvalidToken()
    {
        $this->getInstanceForRequestFixture('responses/query/auth_failed2.xml')->getTransaction('placeholder');
    }

    public function testGetInfo()
    {
        $instance = $this->getInstanceForRequestFixture('responses/query/paymentinfo.xml');
        /** @var Query $trans */
        $trans = $instance->getTransaction('placeholder');

        Assert::assertInstanceOf(Query::class, $trans);

        Assert::assertEquals([
            'amountCaptured' => 0,
            'amountCredited' => 0,
            'cancelled' => false,
            'authorized' => true,
            'authorizationId' => '047132',
        ], $trans->getSummary());

        Assert::assertEquals('1337', $trans->getMerchantId());

        Assert::assertEquals('thisisarandomtransactionid', $trans->getTransactionId());

        Assert::assertEquals([
            'email' => 'bilbo@bagend.sh',
            'phoneNumber' => '+4555378008',
            'customerNumber' => '',
            'firstName' => 'Bilbo',
            'lastName' => 'Baggins',
            'address1' => 'Bag End',
            'address2' => '',
            'postcode' => 'SH1',
            'town' => 'The Shire',
            'country' => 'Middle Earth',
            'socialSecurityNumber' => '',
            'companyName' => '',
            'companyRegistrationNumber' => '',
        ], $trans->getCustomerInfo());

        Assert::assertEquals([
            [
                'amount' => '246500',
                'dateTime' => '2018-05-30T14:31:12.867',
                'description' => '12000353-1 OfflineTransaction Test 4',
                'operation' => 'Setup',
                'transactionReconRef' => 'offlinetxn',
            ], [
                'dateTime' => '2018-05-30T14:31:13.867',
                'description' => '127.0.0.1: AutoAuth',
                'operation' => 'Auth',
                'batchNumber' => '309',
                'transactionReconRef' => 'offlinetxn',
            ],
        ], $trans->getHistory());

        Assert::assertEquals([
            'amount' => 246500,
            'currency' => 'DKK',
            'orderNumber' => 'thisisarandomtransactionid',
            'orderDescription' => 'Test generated transaction',
            'fee' => 0,
            'roundingAmount' => 0,
            'total' => 246500,
            'timestamp' => '2018-05-30T14:31:12.867',
        ], $trans->getOrderInformation());

        Assert::assertEquals(QueryInterface::STATUS_AUTHORIZED, $trans->getTransactionStatus());
    }

    public function testCaptured()
    {
        $instance = $this->getInstanceForRequestFixture('responses/query/captured.xml');
        /** @var Query $trans */
        $trans = $instance->getTransaction('placeholder');

        Assert::assertEquals(QueryInterface::STATUS_CAPTURED, $trans->getTransactionStatus());

        Assert::assertEquals(73700, $trans->getOrderTotal());
    }

    public function testCredited()
    {
        $instance = $this->getInstanceForRequestFixture('responses/query/credited.xml');
        /** @var Query $trans */
        $trans = $instance->getTransaction('placeholder');

        Assert::assertEquals(QueryInterface::STATUS_CREDITED, $trans->getTransactionStatus());

        Assert::assertEquals(73700, $trans->getOrderTotal());
    }

    public function testCancelled()
    {
        $instance = $this->getInstanceForRequestFixture('responses/query/cancelled.xml');
        /** @var Query $trans */
        $trans = $instance->getTransaction('placeholder');

        Assert::assertEquals(QueryInterface::STATUS_CANCELLED, $trans->getTransactionStatus());

        Assert::assertEquals(535800, $trans->getOrderTotal());
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage Cancelled by customer.
     * @expectedExceptionCode 17
     */
    public function testErrorThrowsCorrectException()
    {
        $this->getInstanceForRequestFixture('responses/query/user_cancelled.xml')->getTransaction('placeholder');
    }

    public function testErrorThrowsCorrectException2()
    {
        $instance = $this->getInstanceForRequestFixture('responses/query/user_cancelled.xml');

        try {
            $instance->getTransaction('placeholder');
        } catch (Exception $e) {
            Assert::assertEquals(17, $e->getCode(), 'Invalid code!');
            Assert::assertEquals('Cancelled by customer.', $e->getMessage(), 'Invalid message!');
            Assert::assertEquals('Terminal', $e->getSource(), 'Invalid source!');

            return;
        }

        throw new \Exception("Shouldn't get here!");
    }
}
