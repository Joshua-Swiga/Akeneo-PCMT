<?php
/**
 * Copyright (c) 2020, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

declare(strict_types=1);

namespace PcmtCustomDatasetBundle\Tests\Reader\Database;

use Oro\Bundle\PimDataGridBundle\Repository\DatagridViewRepository;
use PcmtCustomDatasetBundle\Reader\Database\PcmtDatagridViewReader;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class PcmtDatagridViewReaderTest extends TestCase
{
    /** @var DatagridViewRepository|MockObject */
    protected $datagridViewRepositoryMock;

    protected function setUp(): void
    {
        $this->datagridViewRepositoryMock = $this->createMock(DatagridViewRepository::class);
    }

    /**
     * @dataProvider dataGetResults
     *
     * @throws \ReflectionException
     */
    public function testGetResults(array $findAllResults): void
    {
        $this->datagridViewRepositoryMock->expects($this->once())->method('findAll')->willReturn($findAllResults);
        $reader = new PcmtDatagridViewReader($this->datagridViewRepositoryMock);
        $reflection = new \ReflectionClass(get_class($reader));
        $method = $reflection->getMethod('getResults');
        $method->setAccessible(true);
        $arrayIterator = $method->invoke($reader);
        $this->assertSame($arrayIterator->count(), count($findAllResults));
    }

    public function dataGetResults(): array
    {
        return [
            'empty array' => [
                [],
            ],
            'not empty array' => [
                [0, 1],
            ],
        ];
    }
}