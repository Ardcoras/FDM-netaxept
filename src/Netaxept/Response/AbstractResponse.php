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

class AbstractResponse
{
    /**
     * @var \SimpleXMLElement
     */
    protected $xml;

    public function __construct(\SimpleXMLElement $xml)
    {
        $this->xml = $xml;

        if (count($this->xml->Error)) {
            throw new Exception(
                (string) $this->xml->Error->ResponseText,
                (int) $this->xml->Error->ResponseCode,
                (string) $this->xml->Error->ResponseSource
            );
        }
    }
}
