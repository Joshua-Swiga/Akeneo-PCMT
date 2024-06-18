<?php

declare(strict_types=1);

/*
 * Copyright (c) 2020, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

namespace PcmtCISBundle\Tests\Service;

use PcmtCISBundle\Service\DirectoryService;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Filesystem\Filesystem;

class DirectoryServiceTest extends TestCase
{
    /** @var Filesystem|MockObject */
    private $filesystemMock;

    /** @var string */
    private $path;

    protected function setUp(): void
    {
        $this->filesystemMock = $this->createMock(Filesystem::class);
        $this->path = 'example/path/';
    }

    public function testPrepare(): void
    {
        $this->filesystemMock
            ->method('exists')
            ->willReturn(true);

        $this->filesystemMock->expects($this->never())->method('mkdir');

        $service = $this->getDirectoryServiceInstance();
        $service->prepare();
    }

    public function testPrepareNotExists(): void
    {
        $this->filesystemMock
            ->method('exists')
            ->willReturn(false);

        $this->filesystemMock->expects($this->exactly(3))->method('mkdir');

        $service = $this->getDirectoryServiceInstance();
        $service->prepare();
    }

    public function testGetWorkingDirectory(): void
    {
        $service = $this->getDirectoryServiceInstance();
        $dir = $service->getWorkDirectory();

        $this->assertEquals($this->path . 'work/', $dir);
    }

    public function getDirectoryServiceInstance(): DirectoryService
    {
        return new DirectoryService(
            $this->filesystemMock,
            $this->path
        );
    }
}