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

use Throwable;

class Exception extends \Exception
{
    protected $source;

    public function __construct(string $message, int $code, string $source, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);

        $this->source = $source;
    }

    /**
     * @return string
     */
    public function getSource(): string
    {
        return $this->source;
    }
}
