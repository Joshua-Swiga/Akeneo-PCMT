<?php
/**
 * Copyright (c) 2019, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

declare(strict_types=1);

namespace PcmtCoreBundle\Util\Adapter;

class DirectoryCreator
{
    public static function createDirectory(string $path): void
    {
        $path_split = explode('/', $path); //array
        $buildPath = '';
        foreach ($path_split as $pathElem) {
            if ('' === $pathElem) {
                continue;
            }
            $buildPath .= $pathElem . '/';
            if (is_dir($buildPath)) {
                continue;
            }

            try {
                mkdir($buildPath, 0777);
            } catch (\Throwable $exception) {
                throw new \Exception('Error creating directory: ' . $exception->getMessage());
            }
        }
    }
}