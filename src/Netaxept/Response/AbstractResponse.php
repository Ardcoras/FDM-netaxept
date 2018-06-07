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

class AbstractResponse implements ErrorInterface
{
    /**
     * @var \SimpleXMLElement
     */
    protected $xml;

    public function __construct(\SimpleXMLElement $xml)
    {
        $this->xml = $xml;
    }

    public function hasError(): bool
    {
        return (bool) count($this->xml->Error);
    }

    public function getError(): array
    {
        return [
            'dateTime' => (string) $this->xml->Error->DateTime,
            'code' => (string) $this->xml->Error->ResponseCode,
            'source' => (string) $this->xml->Error->ResponseSource,
            'text' => (string) $this->xml->Error->ResponseText,
        ];
    }
}
