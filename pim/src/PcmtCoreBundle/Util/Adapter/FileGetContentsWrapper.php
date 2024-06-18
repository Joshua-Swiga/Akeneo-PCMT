<?php
/**
 * Copyright (c) 2019, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

declare(strict_types=1);

namespace PcmtCoreBundle\Util\Adapter;

class FileGetContentsWrapper
{
    /**
     * @return false|string
     */
    public function fileGetContents(string $filename)
    {
        return file_get_contents($filename);
    }
}