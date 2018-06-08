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

interface ErrorInterface
{
    /**
     * Should return whether or not any errors are present in the response data
     *
     * @return bool
     */
    public function hasError(): bool;

    /**
     * Should return an array representing the extracted data from the Error object in the response data
     *
     * @return array
     */
    public function getError(): array;
}
