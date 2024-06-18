<?php
/**
 * Copyright (c) 2020, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */
declare(strict_types=1);

namespace PcmtCoreBundle\Tests\Service\E2Open;

use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Updater\ProductUpdater;
use Akeneo\Tool\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use Monolog\Logger;
use PcmtCoreBundle\Entity\E2OpenAttributeData;
use PcmtCoreBundle\Service\E2Open\E2OpenAttributesService;
use PcmtCoreBundle\Service\E2Open\TradeItemProductUpdater;
use PcmtCoreBundle\Tests\TestDataBuilder\AttributeBuilder;
use PcmtCoreBundle\Tests\TestDataBuilder\ProductBuilder;
use PHPUnit\Framework\MockObject\MockObject as Mock;
use PHPUnit\Framework\TestCase;

class TradeItemProductUpdaterTest extends TestCase
{
    /** @var Logger|Mock */
    private $loggerMock;

    /** @var E2OpenAttributesService|Mock */
    private $attributeServiceMock;

    /** @var ObjectUpdaterInterface|Mock */
    private $productUpdaterMock;

    protected function setUp(): void
    {
        $this->loggerMock = $this->createMock(Logger::class);
        $this->attributeServiceMock = $this->createMock(E2OpenAttributesService::class);
        $this->productUpdaterMock = $this->createMock(ProductUpdater::class);
    }

    /**
     * @dataProvider dataProcess
     */
    public function testProcess(array $data, ProductInterface $product): void
    {
        $tradeItemProductUpdater = $this->getTradeItemProductUpdaterInstance();

        $attribute = (new AttributeBuilder())->build();
        $this->attributeServiceMock->expects($this->exactly(count($data)))
            ->method('getForCode')
            ->willReturn($attribute);

        $tradeItemProductUpdater->update($product, $data);
    }

    public function dataProcess(): array
    {
        $data = [
            'GS1_PACKAGINGTYPECODE' => new E2OpenAttributeData(
                '{}packagingTypeCode',
                'GS1_PACKAGINGTYPECODE',
                'BPG',
                [
                    'measurementUnitCode' => 'xxx',
                ]
            ),
            'GS1_EFFECTIVEDATETIME' => new E2OpenAttributeData(
                '{}effectiveDateTime',
                'GS1_EFFECTIVEDATETIME',
                '2019-11-26T00:00:00',
                []
            ),
        ];
        $product = (new ProductBuilder())->build();

        return [
            [
                $data,
                $product,
            ],
        ];
    }

    /**
     * @dataProvider dataProcess
     */
    public function testProcessMetricUnitAttributes(array $data, ProductInterface $product): void
    {
        $tradeItemProductUpdater = $this->getTradeItemProductUpdaterInstance();

        $attribute = (new AttributeBuilder())->withMetricFamily(E2OpenAttributesService::MEASURE_UNIT)->build();

        $this->attributeServiceMock->expects($this->exactly(count($data)))
            ->method('getForCode')
            ->willReturn($attribute);

        $this->attributeServiceMock->expects($this->once())
            ->method('getMeasureUnitForSymbol');

        $tradeItemProductUpdater->update($product, $data);
    }

    /**
     * @dataProvider dataProcess
     */
    public function testErrorWhenMappingNotFound(array $data, ProductInterface $product): void
    {
        $tradeItemProductUpdater = $this->getTradeItemProductUpdaterInstance();

        $this->attributeServiceMock->expects($this->exactly(count($data)))
            ->method('getForCode')
            ->willReturn(null);

        $this->loggerMock->expects($this->exactly(count($data)))
            ->method('error');

        $tradeItemProductUpdater->update($product, $data);
    }

    private function getTradeItemProductUpdaterInstance(): TradeItemProductUpdater
    {
        return new TradeItemProductUpdater(
            $this->attributeServiceMock,
            $this->productUpdaterMock,
            $this->loggerMock
        );
    }
}
