<?php

declare(strict_types=1);

/*
 * Copyright (c) 2020, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

namespace PcmtCISBundle\Service;

use Symfony\Component\Finder\Finder;

class FileSearchService
{
    /** @var Finder */
    private $finder;

    /** @var string */
    private $path;

    public function __construct(
        Finder $finder,
        string $path
    ) {
        $this->finder = $finder;
        $this->path = $path;
    }

    public function isFileWaitingForUploadByContent(
        string $content
    ): bool {
        return $this->finder
            ->files()
            ->in($this->path . 'work/')
            ->contains($content)
            ->hasResults();
    }
}