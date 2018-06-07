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

class Register extends AbstractResponse implements RegisterInterface
{
    /**
     * {@inheritdoc}
     *
     * @return string
     */
    public function getTransactionId(): string
    {
        return (string) $this->xml->TransactionId;
    }
}
