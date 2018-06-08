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

namespace FDM\Netaxept\Exception;

class BBSException extends Exception
{
    private $result = [];

    public function __construct(\SimpleXMLElement $xml)
    {
        foreach ($xml->Error->Result->children() as $tag) {
            $this->result[lcfirst($tag->getName())] = (string) $tag;
        }

        parent::__construct($xml);
    }

    /**
     * @return array
     */
    public function getResult(): array
    {
        return $this->result;
    }
}
