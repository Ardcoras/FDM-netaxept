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

use FDM\Netaxept\Exception\BBSException;
use PHPUnit\Framework\Assert;

class ApiProcessTest extends ApiTest
{
    /**
     * @expectedException \FDM\Netaxept\Exception\AuthenticationException
     * @expectedExceptionMessage Test: Unable to authenticate merchant (credentials not passed)
     */
    public function testMissingParameters()
    {
        $this->getInstanceForRequestFixture('responses/process/no_parameters.xml')->processTransaction([]);
    }

    /**
     * @expectedException \FDM\Netaxept\Exception\ValidationException
     * @expectedExceptionMessage Missing operation
     */
    public function testMissingOperation()
    {
        $this->getInstanceForRequestFixture('responses/process/missing_operation.xml')->processTransaction([]);
    }

    /**
     * @expectedException \FDM\Netaxept\Exception\ValidationException
     * @expectedExceptionMessage Unknown operation: FRED
     */
    public function testUnknownOperation()
    {
        $this->getInstanceForRequestFixture('responses/process/unknown_operation.xml')
            ->processTransaction([], 'required but unused');
    }

    /**
     * @expectedException \FDM\Netaxept\Exception\TransactionNotFoundException
     * @expectedExceptionMessage Unable to find transaction
     */
    public function testInvalidTransactionId()
    {
        $this->getInstanceForRequestFixture('responses/process/invalid_transaction_id.xml')
            ->processTransaction([], 'required but unused');
    }

    /**
     * If the user hasn't clicked through and proceeded to the payment window and entered his card details, then the
     * transaction in Netaxept will not have any registered card details.... Which, when trying to auth, will result in
     * a "Issuer is undetermined" error.
     *
     * @expectedException \FDM\Netaxept\Exception\ValidationException
     * @expectedExceptionMessage Issuer is undetermined
     */
    public function testThatAuthingANonCompletedPaymentFails()
    {
        $this->getInstanceForRequestFixture('responses/process/no_credit_card_details.xml')
            ->processTransaction([], 'required but unused');
    }

    /**
     * @expectedException \FDM\Netaxept\Exception\TransactionNotAuthorizedException
     * @expectedExceptionMessage You cannot run capture on a transaction that never is authorized
     */
    public function testThatCapturingWithoutAuthFails()
    {
        $this->getInstanceForRequestFixture('responses/process/capture_without_auth.xml')
            ->processTransaction([], 'required but unused');
    }

    /**
     * @expectedException \FDM\Netaxept\Exception\GenericError
     * @expectedExceptionMessage You cannot run credit on a transaction that has not been captured.
     */
    public function testThatCreditingWithoutCapturingFails()
    {
        $this->getInstanceForRequestFixture('responses/process/credit_without_capture.xml')
            ->processTransaction([], 'required but unused');
    }

    /**
     * @expectedException \FDM\Netaxept\Exception\GenericError
     * @expectedExceptionMessage Unable to annul, wrong state
     */
    public function testThatAttemptingToCancelANonAuthedTransactionFails()
    {
        $this->getInstanceForRequestFixture('responses/process/cancel_non_authed.xml')
            ->processTransaction([], 'required but unused');
    }

    public function testThatAuthingAlreadyAuthedFails()
    {
        try {
            $this->getInstanceForRequestFixture('responses/process/already_authed.xml')
                ->processTransaction([], 'required but unused');
        } catch (BBSException $e) {
            Assert::assertEquals('Unable to auth', $e->getMessage());
            Assert::assertEquals([
                'issuerId' => '50',
                'responseCode' => '98',
                'responseText' => 'Transaction already processed',
                'responseSource' => 'Netaxept',
                'transactionId' => 'fdm-medlem-0000022',
                'executionTime' => '2018-06-08T12:01:02.7705311+02:00',
                'merchantId' => '200970',
                'extraInfoOut' => '2030010',
                'maskedPan' => '',
                'messageId' => '0aa8e5472ca64707b036121ba22525b7',
            ], $e->getResult(), 'Unexpected result!');

            return;
        }

        throw new \Exception('It shouldn\'t get here');
    }

    public function testThatRegisterWorksButAuthFailsOnATestCard()
    {
        try {
            $this->getInstanceForRequestFixture('responses/process/register_ok_but_auth_fails.xml')
                ->processTransaction([], 'required but unused');
        } catch (BBSException $e) {
            Assert::assertEquals('Unable to auth', $e->getMessage());
            Assert::assertEquals([
                'issuerId' => '3',
                'responseCode' => '99',
                'responseText' => 'Auth Reg Comp Failure)',
                'responseSource' => 'Netaxept',
                'transactionId' => 'fdm-medlem-0000026',
                'executionTime' => '2018-06-08T13:11:40.5696656+02:00',
                'merchantId' => '200970',
                'extraInfoOut' => '2030010',
                'maskedPan' => '',
                'messageId' => '09c8eceacd4f4b4fb2fe74be1513524c',
            ], $e->getResult(), 'Unexpected result!');

            return;
        }

        throw new \Exception('It shouldn\'t get here');
    }

    public function testThatVerifyingSucceeds()
    {
        $response = $this->getInstanceForRequestFixture('responses/process/verify.xml')
            ->processTransaction([], 'required but unused');
        Assert::assertEquals('OK', $response->getStatus(), 'Unexpected status!');
        Assert::assertEquals('VERIFY', $response->getOperation(), 'Unexpected operation!');
    }

    public function testThatAuthSucceeds()
    {
        $response = $this->getInstanceForRequestFixture('responses/process/auth.xml')
            ->processTransaction([], 'required but unused');
        Assert::assertEquals('OK', $response->getStatus(), 'Unexpected status!');
        Assert::assertEquals('AUTH', $response->getOperation(), 'Unexpected operation!');
    }

    public function testThatCancelSucceeds()
    {
        $response = $this->getInstanceForRequestFixture('responses/process/cancel.xml')
            ->processTransaction([], 'required but unused');
        Assert::assertEquals('OK', $response->getStatus(), 'Unexpected status!');
        Assert::assertEquals('ANNUL', $response->getOperation(), 'Unexpected operation!');
    }

    public function testThatCaptureSucceeds()
    {
        $response = $this->getInstanceForRequestFixture('responses/process/capture.xml')
            ->processTransaction([], 'required but unused');
        Assert::assertEquals('OK', $response->getStatus(), 'Unexpected status!');
        Assert::assertEquals('CAPTURE', $response->getOperation(), 'Unexpected operation!');
    }

    public function testThatCreditSucceeds()
    {
        $response = $this->getInstanceForRequestFixture('responses/process/credit.xml')
            ->processTransaction([], 'required but unused');
        Assert::assertEquals('OK', $response->getStatus(), 'Unexpected status!');
        Assert::assertEquals('CREDIT', $response->getOperation(), 'Unexpected operation!');
    }

    public function testThatSaleSucceeds()
    {
        $response = $this->getInstanceForRequestFixture('responses/process/sale.xml')
            ->processTransaction([], 'required but unused');
        Assert::assertEquals('OK', $response->getStatus(), 'Unexpected status!');
        Assert::assertEquals('SALE', $response->getOperation(), 'Unexpected operation!');
    }
}
