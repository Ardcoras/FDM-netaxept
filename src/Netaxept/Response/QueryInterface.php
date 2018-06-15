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

namespace FDM\Netaxept\Response;

interface QueryInterface
{
    const STATUS_UNREGISTERED = 'unregistered';

    const STATUS_PENDING = 'pending';

    const STATUS_AUTHORIZED = 'authorized';

    const STATUS_CAPTURED = 'captured';

    const STATUS_CANCELLED = 'cancelled';

    const STATUS_CREDITED = 'credited';

    const STATUS_FAILED = 'failed';

    /**
     * Should determine the transaction status based on the data available in the response, which should be one of the
     * constants defined in this interface.
     *
     * @return string
     */
    public function getTransactionStatus(): string;

    /**
     * Should extract the summary information from the response
     *
     * @return array
     */
    public function getSummary(): array;

    /**
     * Should extract get the merchantId from the response
     *
     * @return string
     */
    public function getMerchantId(): string;

    /**
     * Should extract the transactionId from the response
     *
     * @return string
     */
    public function getTransactionId(): string;

    /**
     * Should extract the customer information from the response
     *
     * @return array
     */
    public function getCustomerInfo(): array;

    /**
     * Should extract the transaction history data from the response
     *
     * @return array
     */
    public function getHistory(): array;

    /**
     * Should extract the order information data from the response
     *
     * @return array
     */
    public function getOrderInformation(): array;

    /**
     * Should extract the value of the order total, which is a value in cents. So, in the case of the DKK currency,
     * an order total of 80085 is actually 800.85 Kroner.
     *
     * @return int
     */
    public function getOrderTotal(): int;
}
