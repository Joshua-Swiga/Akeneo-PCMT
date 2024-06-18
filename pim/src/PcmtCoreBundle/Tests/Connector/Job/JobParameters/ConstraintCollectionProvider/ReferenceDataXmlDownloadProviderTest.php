<?php

declare(strict_types=1);

/**
 * Copyright (c) 2020, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

namespace PcmtCoreBundle\Tests\Connector\Job\JobParameters\ConstraintCollectionProvider;

use Akeneo\Tool\Component\Batch\Job\JobInterface;
use PcmtCoreBundle\Connector\Job\JobParameters\ConstraintCollectionProvider\ReferenceDataXmlDownloadProvider;
use PHPUnit\Framework\TestCase;

class ReferenceDataXmlDownloadProviderTest extends TestCase
{
    /**
     * @dataProvider dataSupports
     */
    public function testSupports(string $jobName): void
    {
        $o = new ReferenceDataXmlDownloadProvider([$jobName]);

        $job = $this->createMock(JobInterface::class);
        $job->method('getName')->willReturn($jobName);
        $this->assertTrue($o->supports($job));

        $job = $this->createMock(JobInterface::class);
        $job->method('getName')->willReturn($jobName.'yyy');
        $this->assertFalse($o->supports($job));
    }

    public function dataSupports(): array
    {
        return [
            ['xxx'],
            ['cccccc_2323'],
        ];
    }

    public function testGetConstraintCollection(): void
    {
        $o = new ReferenceDataXmlDownloadProvider([]);
        $collection = $o->getConstraintCollection();
        $this->assertArrayHasKey('dirPath', $collection->fields);
        $this->assertArrayHasKey('filePath', $collection->fields);
    }
}