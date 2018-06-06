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

use Webmozart\Assert\Assert;

class Factory
{
    private $classMap = [];

    public function __construct(
        $queryClass = Query::class,
        $processClass = Process::class,
        $registerClass = Register::class
    ) {
        $this->classMap = [
            'PaymentInfo' => $queryClass,
            'ProcessResponse' => $processClass,
            'RegisterResponse' => $registerClass,
        ];
    }

    public function getResponse(\SimpleXMLElement $xml)
    {
        Assert::notEmpty(
            $this->classMap[$xml->getName()],
            'Unable to instantiate response class for ' . $xml->getName()
        );

        $className = $this->classMap[$xml->getName()];

        return new $className($xml);
    }
}
