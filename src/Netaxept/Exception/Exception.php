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

class Exception extends \Exception
{
    public function __construct(\SimpleXMLElement $xml)
    {
        $message = (string) $xml->Error->Message;
        parent::__construct($message);
    }
}
