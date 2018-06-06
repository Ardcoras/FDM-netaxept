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

namespace FDM\Netaxept\Traits;

trait FileLoaderTrait
{
    /**
     * Fetches the contents of the specified file, relative to the Resources directory.
     *
     * @param string $resourcesRelativePath
     *
     * @return bool|string
     */
    public function fileGetContents(string $resourcesRelativePath)
    {
        $f = $_SERVER['PWD'] . '/Resources/' . $resourcesRelativePath;

        return file_get_contents($f);
    }
}
