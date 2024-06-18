<?php

declare(strict_types=1);

/*
 * Copyright (c) 2020, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

namespace PcmtCISBundle\Tests\Service;

use PcmtCISBundle\Service\FileCommandService;
use PcmtCISBundle\Service\FileService;
use PcmtCISBundle\Tests\TestDataBuilder\SubscriptionBuilder;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class FileServiceTest extends TestCase
{
    /** @var FileCommandService|MockObject */
    private $fileCommandServiceMock;

    protected function setUp(): void
    {
        $this->fileCommandServiceMock = $this->createMock(FileCommandService::class);
    }

    public function testCreateFileCommandAdd(): void
    {
        $subscription = (new SubscriptionBuilder())->build();
        $this->fileCommandServiceMock->expects($this->once())
            ->method('createFile')
            ->with($subscription, FileService::DOCUMENT_COMMAND_TYPE_ADD);
        $service = $this->getFileServiceInstance();
        $service->createFileCommandAdd($subscription);
    }

    public function testCreateFileCommandDelete(): void
    {
        $subscription = (new SubscriptionBuilder())->build();
        $this->fileCommandServiceMock->expects($this->once())
            ->method('createFile')
            ->with($subscription, FileService::DOCUMENT_COMMAND_TYPE_DELETE);
        $service = $this->getFileServiceInstance();
        $service->createFileCommandDelete($subscription);
    }

    public function getFileServiceInstance(): FileService
    {
        return new FileService($this->fileCommandServiceMock);
    }
}