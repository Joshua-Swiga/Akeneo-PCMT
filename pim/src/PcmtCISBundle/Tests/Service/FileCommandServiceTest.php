<?php

declare(strict_types=1);

/*
 * Copyright (c) 2020, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

namespace PcmtCISBundle\Tests\Service;

use PcmtCISBundle\Service\DirectoryService;
use PcmtCISBundle\Service\FileCommandService;
use PcmtCISBundle\Service\FileContentService;
use PcmtCISBundle\Service\FileNameService;
use PcmtCISBundle\Service\FileSearchService;
use PcmtCISBundle\Tests\TestDataBuilder\SubscriptionBuilder;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Filesystem\Exception\FileNotFoundException;
use Symfony\Component\Filesystem\Filesystem;

class FileCommandServiceTest extends TestCase
{
    /** @var Filesystem|MockObject */
    private $filesystemMock;

    /** @var FileContentService|MockObject */
    private $fileContentServiceMock;

    /** @var FileNameService|MockObject */
    private $fileNameServiceMock;

    /** @var DirectoryService|MockObject */
    private $directoryServiceMock;

    /** @var FileSearchService|MockObject */
    private $fileSearchServiceMock;

    /** @var string */
    private $commandType = 'EXAMPLE_TYPE';

    protected function setUp(): void
    {
        $this->filesystemMock = $this->createMock(Filesystem::class);
        $this->fileContentServiceMock = $this->createMock(FileContentService::class);
        $this->fileNameServiceMock = $this->createMock(FileNameService::class);
        $this->directoryServiceMock = $this->createMock(DirectoryService::class);
        $this->fileSearchServiceMock = $this->createMock(FileSearchService::class);
    }

    public function testCreateFile(): void
    {
        $subscription = (new SubscriptionBuilder())->build();

        $service = $this->getFileServiceInstance();

        $filename = 'xxxxxxxxx.txt';
        $this->fileNameServiceMock->method('get')->willReturn($filename);

        $this->filesystemMock
            ->expects($this->once())
            ->method('touch')
            ->with($filename);

        $this->filesystemMock
            ->method('exists')
            ->willReturn(true);

        $header = 'xxxx';
        $this->fileContentServiceMock->method('getHeader')->willReturn($header);

        $content = 'yyyy';
        $this->fileContentServiceMock->method('getSubscriptionContent')->willReturn($content);

        $this->filesystemMock
            ->expects($this->exactly(2))
            ->method('appendToFile')
            ->withConsecutive(
                [
                    $filename,
                    $header . PHP_EOL,
                ],
                [
                    $filename,
                    $content,
                ]
            );

        $service->createFile($subscription, $this->commandType);
    }

    public function testCreateFileWhenFileHasNotBeenCreated(): void
    {
        $subscription = (new SubscriptionBuilder())->build();

        $service = $this->getFileServiceInstance();

        $filename = 'xxxxxxxxx.txt';
        $this->fileNameServiceMock->method('get')->willReturn($filename);

        $this->filesystemMock
            ->expects($this->once())
            ->method('touch')
            ->with($filename);

        $this->filesystemMock
            ->method('exists')
            ->willReturn(false);

        $this->expectException(FileNotFoundException::class);

        $service->createFile($subscription, $this->commandType);
    }

    public function testCreateFileWhenPreviousOneHasNotBeenYetUploaded(): void
    {
        $subscription = (new SubscriptionBuilder())->build();

        $service = $this->getFileServiceInstance();

        $this->fileSearchServiceMock
            ->method('isFileWaitingForUploadByContent')
            ->willReturn(true);

        $this->directoryServiceMock
            ->expects($this->once())
            ->method('prepare');

        $this->directoryServiceMock
            ->expects($this->never())
            ->method('getWorkDirectory');

        $this->expectException(\RuntimeException::class);

        $service->createFile($subscription, $this->commandType);
    }

    private function getFileServiceInstance(): FileCommandService
    {
        return new FileCommandService(
            $this->filesystemMock,
            $this->fileContentServiceMock,
            $this->fileNameServiceMock,
            $this->directoryServiceMock,
            $this->fileSearchServiceMock
        );
    }
}