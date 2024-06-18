<?php
/**
 * Copyright (c) 2020, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

declare(strict_types=1);

namespace PcmtCoreBundle\Tests\Service\Builder;

use PcmtCoreBundle\Service\Builder\PathBuilder;
use PHPUnit\Framework\TestCase;

class PathBuilderTest extends TestCase
{
    public function testGetFileName(): void
    {
        $pathBuilder = new PathBuilder();
        $path = 'some/path/file.txt';
        $pathBuilder->setPath($path);
        $this->assertSame($path, $pathBuilder->getPath());
        $fileName = $pathBuilder->getFileName(false);
        $fileNameWithExtension = $pathBuilder->getFileName();
        $this->assertSame('file', $fileName);
        $this->assertSame('file.txt', $fileNameWithExtension);
    }

    public function testGetFileNameWithTime(): void
    {
        $pathBuilder = new PathBuilder();
        $fileName = 'random.name';
        $newFileName = $pathBuilder->getFileNameWithTime($fileName);
        $this->assertStringContainsString($fileName, $newFileName);
        $this->assertNotSame($fileName, $newFileName);
    }
}