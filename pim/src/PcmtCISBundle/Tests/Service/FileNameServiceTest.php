<?php

declare(strict_types=1);

/*
 * Copyright (c) 2020, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

namespace PcmtCISBundle\Tests\Service;

use PcmtCISBundle\Service\FileNameService;
use PcmtCISBundle\Service\FileUniqueIdentifierGenerator;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class FileNameServiceTest extends TestCase
{
    /** @var FileUniqueIdentifierGenerator|MockObject */
    private $uniqueIdentifierGeneratorMock;

    /** @var string */
    private $source;

    protected function setUp(): void
    {
        $this->uniqueIdentifierGeneratorMock = $this->createMock(FileUniqueIdentifierGenerator::class);
        $this->source = 'RHSC';
    }

    public function testGet(): void
    {
        $destination = 'GS1Engine';
        $messageType = 'GDSNSubscription';
        $version = '1.0';
        $uniqueIdentifier = '2020-10-26T10:01:55+00:00';

        $this->uniqueIdentifierGeneratorMock
            ->method('generate')
            ->willReturn($uniqueIdentifier);

        $expectedFilename = "{$this->source}_{$destination}_{$messageType}_{$version}_{$uniqueIdentifier}.txt";

        $service = $this->getDirectoryServiceInstance();

        $this->assertEquals($expectedFilename, $service->get());
    }

    public function getDirectoryServiceInstance(): FileNameService
    {
        return new FileNameService(
            $this->uniqueIdentifierGeneratorMock,
            $this->source
        );
    }
}