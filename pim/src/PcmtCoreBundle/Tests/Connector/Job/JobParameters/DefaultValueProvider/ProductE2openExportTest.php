<?php
/**
 * Copyright (c) 2020, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

declare(strict_types=1);

namespace PcmtCoreBundle\Tests\Connector\Job\JobParameters\DefaultValueProvider;

use Akeneo\Tool\Component\Batch\Job\JobInterface;
use Akeneo\Tool\Component\Batch\Job\JobParameters\DefaultValuesProviderInterface;
use PcmtCoreBundle\Connector\Job\JobParameters\DefaultValueProvider\ProductE2openExport;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ProductE2openExportTest extends TestCase
{
    /** @var DefaultValuesProviderInterface|MockObject */
    private $baseDefaultValueProviderMock;

    /** @var string[] */
    private $supportedJobNames = ['example_job_name'];

    /** @var ProductE2openExport */
    private $productE2openExportProvider;

    protected function setUp(): void
    {
        $this->baseDefaultValueProviderMock = $this->createMock(DefaultValuesProviderInterface::class);
        $this->productE2openExportProvider = new ProductE2openExport(
            $this->baseDefaultValueProviderMock,
            $this->supportedJobNames
        );
    }

    public function testGetDefaultValues(): void
    {
        $baseDefaultData = [
            'some_parameter' => [
                'data' => 'custom data',
            ],
            'filters' => [
                'data' => [
                    'some_filter',
                ],
            ],
        ];
        $this->baseDefaultValueProviderMock
            ->expects($this->once())
            ->method('getDefaultValues')
            ->willReturn($baseDefaultData);
        $defaultValues = $this->productE2openExportProvider->getDefaultValues();
        $this->assertEmpty($defaultValues['filters']['data']);
        $this->assertSame($baseDefaultData['some_parameter'], $defaultValues['some_parameter']);
    }

    /**
     * @dataProvider dataSupports
     */
    public function testSupports(string $jobName, bool $expectedResult): void
    {
        $jobMock = $this->createMock(JobInterface::class);
        $jobMock
            ->expects($this->once())
            ->method('getName')
            ->willReturn($jobName);
        $result = $this->productE2openExportProvider->supports($jobMock);
        $this->assertSame($expectedResult, $result);
    }

    public function dataSupports(): array
    {
        return [
            [
                $this->supportedJobNames[0],
                true,
            ],
            [
                'not_supported_job_name',
                false,
            ],
        ];
    }
}