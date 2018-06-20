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

class Query extends AbstractResponse implements QueryInterface, ErrorInterface
{
    /**
     * {@inheritdoc}
     *
     * @return string
     */
    public function getTransactionStatus(): string
    {
        // If the user cancelled in the terminal window, then we need to detect that.
        if ($this->hasError()) {
            $error = $this->getError();
            if (
                !empty($error['operation']) && $error['operation'] === 'Terminal' &&
                !empty($error['responseCode']) && $error['responseCode'] === '17' &&
                !empty($error['responseSource']) && ($error['responseSource'] === 'Terminal' || $error['responseSource'] === '05') &&
                !empty($error['responseText']) && $error['responseText'] === 'Cancelled by customer.'
            ) {
                return QueryInterface::STATUS_CANCELLED;
            }

            return QueryInterface::STATUS_FAILED;
        }

        $summary = $this->getSummary();

        // If the cancelled flag is set, then it can no longer be considered to be authed, captured or credited.
        if ($summary['cancelled']) {
            return QueryInterface::STATUS_CANCELLED;
        }

        // If there's an amount present in the amountCredited field, it's because it's not a request for payment.
        if ($summary['amountCredited']) {
            return QueryInterface::STATUS_CREDITED;
        }

        // If it's not cancelled, not credited, but is authed and not captured, then it must just be authed.
        if ($summary['authorized'] && !$summary['amountCaptured']) {
            return QueryInterface::STATUS_AUTHORIZED;
        }

        // If it's not cancelled, not credited, not authed, and not captured, then it's either pending, (Netaxept's
        // 'registered' state) or rejected.
        if (!$summary['authorized'] && !$summary['amountCaptured']) {
            return QueryInterface::STATUS_PENDING;
        }

        // The only other option is that it's been captured.
        return QueryInterface::STATUS_CAPTURED;
    }

    /**
     * {@inheritdoc}
     *
     * @return array
     */
    public function getSummary(): array
    {
        return [
            'amountCaptured' => (int) $this->xml->Summary->AmountCaptured,
            'amountCredited' => (int) $this->xml->Summary->AmountCredited,
            'cancelled' => filter_var($this->xml->Summary->Annulled, FILTER_VALIDATE_BOOLEAN),
            'authorized' => filter_var($this->xml->Summary->Authorized, FILTER_VALIDATE_BOOLEAN),
            'authorizationId' => (string) $this->xml->Summary->AuthorizationId,
        ];
    }

    /**
     * {@inheritdoc}
     *
     * @return string
     */
    public function getMerchantId(): string
    {
        return (string) $this->xml->MerchantId;
    }

    /**
     * {@inheritdoc}
     *
     * @return string
     */
    public function getTransactionId(): string
    {
        return (string) $this->xml->TransactionId;
    }

    /**
     * {@inheritdoc}
     *
     * @return array
     */
    public function getCustomerInfo(): array
    {
        return [
            'email' => (string) $this->xml->CustomerInformation->Email,
            'phoneNumber' => (string) $this->xml->CustomerInformation->PhoneNumber,
            'customerNumber' => (string) $this->xml->CustomerInformation->CustomerNumber,
            'firstName' => (string) $this->xml->CustomerInformation->FirstName,
            'lastName' => (string) $this->xml->CustomerInformation->LastName,
            'address1' => (string) $this->xml->CustomerInformation->Address1,
            'address2' => (string) $this->xml->CustomerInformation->Address2,
            'postcode' => (string) $this->xml->CustomerInformation->Postcode,
            'town' => (string) $this->xml->CustomerInformation->Town,
            'country' => (string) $this->xml->CustomerInformation->Country,
            'socialSecurityNumber' => (string) $this->xml->CustomerInformation->SocialSecurityNumber,
            'companyName' => (string) $this->xml->CustomerInformation->CompanyName,
            'companyRegistrationNumber' => (string) $this->xml->CustomerInformation->CompanyRegistrationNumber,
        ];
    }

    /**
     * {@inheritdoc}
     *
     * @return array
     */
    public function getHistory(): array
    {
        $history = [];
        foreach ($this->xml->History->children() as $logEntry) {
            $log = [];
            foreach ($logEntry as $key => $val) {
                $log[lcfirst($key)] = (string) $val;
            }
            $history[] = $log;
        }

        return $history;
    }

    /**
     * {@inheritdoc}
     *
     * @return array
     */
    public function getOrderInformation(): array
    {
        return [
            'amount' => (int) $this->xml->OrderInformation->Amount,
            'currency' => (string) $this->xml->OrderInformation->Currency,
            'orderNumber' => (string) $this->xml->OrderInformation->OrderNumber,
            'orderDescription' => (string) $this->xml->OrderInformation->OrderDescription,
            'fee' => (int) $this->xml->OrderInformation->Fee,
            'roundingAmount' => (int) $this->xml->OrderInformation->RoundingAmount,
            'total' => (int) $this->xml->OrderInformation->Total,
            'timestamp' => (string) $this->xml->OrderInformation->Timestamp,
        ];
    }

    /**
     * {@inheritdoc}
     *
     * @return int
     */
    public function getOrderTotal(): int
    {
        return (int) $this->xml->OrderInformation->Total;
    }
}
