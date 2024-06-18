<?php

declare(strict_types=1);

/*
 * Copyright (c) 2020, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

namespace PcmtCISBundle\Service;

use Symfony\Component\Filesystem\Filesystem;

class DirectoryService
{
    public function __construct(
        Filesystem $filesystem,
        string $path
    ) {
        $this->filesystem = $filesystem;
        $this->path = $path;
    }

    public function getWorkDirectory(): string
    {
        return $this->path . 'work/';
    }

    public function prepare(): void
    {
        if (!$this->filesystem->exists($this->path)) {
            $this->filesystem->mkdir($this->path);
        }

        $dirs = ['work', 'done'];
        foreach ($dirs as $dir) {
            $path = $this->path . $dir . '/';
            if (!$this->filesystem->exists($path)) {
                $this->filesystem->mkdir($path);
            }
        }
    }
}