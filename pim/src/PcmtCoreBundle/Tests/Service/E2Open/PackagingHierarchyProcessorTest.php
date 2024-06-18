<?php
/**
 * Copyright (c) 2020, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */
declare(strict_types=1);

namespace PcmtCoreBundle\Tests\Service\E2Open;

use Akeneo\Pim\Enrichment\Component\Product\Updater\ProductUpdater;
use Akeneo\Tool\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use Monolog\Logger;
use PcmtCoreBundle\Service\E2Open\PackagingHierarchyProcessor;
use PcmtCoreBundle\Tests\TestDataBuilder\ProductBuilder;
use PHPUnit\Framework\MockObject\MockObject as Mock;
use PHPUnit\Framework\TestCase;

class PackagingHierarchyProcessorTest extends TestCase
{
    /** @var Logger|Mock */
    private $loggerMock;

    /** @var ObjectUpdaterInterface|Mock */
    private $productUpdaterMock;

    protected function setUp(): void
    {
        $this->loggerMock = $this->createMock(Logger::class);
        $this->productUpdaterMock = $this->createMock(ProductUpdater::class);
    }

    /**
     * @dataProvider dataProcess
     */
    public function testProcess(array $products): void
    {
        $this->productUpdaterMock->expects($this->exactly(count($products)))->method('update');

        $processor = $this->getPackagingHierarchyProcessorInstance();
        $processor->process($products);
    }

    public function dataProcess(): array
    {
        $product1 = (new ProductBuilder())->build();
        $product2 = (new ProductBuilder())->build();

        return [
            [[$product1]],
            [[$product1, $product2]],
        ];
    }

    private function getPackagingHierarchyProcessorInstance(): PackagingHierarchyProcessor
    {
        return new PackagingHierarchyProcessor(
            $this->productUpdaterMock,
            $this->loggerMock
        );
    }
}
