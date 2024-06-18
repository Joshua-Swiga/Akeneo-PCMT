<?php

declare(strict_types=1);

/*
 * Copyright (c) 2021, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

namespace PcmtSharedBundle\Tests\TestDataBuilder;

use Akeneo\Tool\Component\FileStorage\Model\FileInfo;

class FileInfoBuilder
{
    /** @var FileInfo */
    private $fileInfo;

    public function __construct()
    {
        $this->fileInfo = new FileInfo();
    }

    public function withOriginalFilename(string $filename): self
    {
        $this->fileInfo->setOriginalFilename($filename);

        return $this;
    }

    public function withSize(int $size): self
    {
        $this->fileInfo->setSize($size);

        return $this;
    }

    public function build(): FileInfo
    {
        return $this->fileInfo;
    }
}