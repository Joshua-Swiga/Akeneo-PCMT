<?php

declare(strict_types=1);

/*
 * Copyright (c) 2020, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

namespace PcmtRulesBundle\Tests\Service;

use Akeneo\Channel\Component\Repository\ChannelRepositoryInterface;
use Akeneo\Channel\Component\Repository\LocaleRepositoryInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\EntityWithValuesInterface;
use Akeneo\Pim\Enrichment\Component\Product\ProductModel\Filter\ProductAttributeFilter;
use Akeneo\Pim\Enrichment\Component\Product\ProductModel\Filter\ProductModelAttributeFilter;
use Akeneo\Pim\Enrichment\Component\Product\Value\ScalarValue;
use Akeneo\Tool\Component\StorageUtils\Updater\PropertyCopierInterface;
use PcmtRulesBundle\Service\RuleProcessorCopier;
use PcmtRulesBundle\Tests\TestDataBuilder\AttributeBuilder;
use PcmtRulesBundle\Tests\TestDataBuilder\AttributeMappingBuilder;
use PcmtRulesBundle\Tests\TestDataBuilder\AttributeMappingCollectionBuilder;
use PcmtRulesBundle\Tests\TestDataBuilder\ProductBuilder;
use PcmtRulesBundle\Tests\TestDataBuilder\ProductModelBuilder;
use PcmtRulesBundle\Value\AttributeMappingCollection;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class RuleProcessorCopierTest extends TestCase
{
    /** @var PropertyCopierInterface|MockObject */
    private $propertyCopierMock;

    /** @var ChannelRepositoryInterface|MockObject */
    private $channelRepositoryMock;

    /** @var LocaleRepositoryInterface|MockObject */
    private $localeRepositoryMock;

    /** @var ProductAttributeFilter|MockObject */
    private $productAttributeFilterMock;

    /** @var ProductModelAttributeFilter|MockObject */
    private $productModelAttributeFilterMock;

    /** @var NormalizerInterface|MockObject */
    private $normalizerMock;

    /** @var EventDispatcherInterface|MockObject */
    private $eventDispatcherMock;

    /** @var LoggerInterface|MockObject */
    private $loggerMock;

    protected function setUp(): void
    {
        $this->propertyCopierMock = $this->createMock(PropertyCopierInterface::class);
        $this->channelRepositoryMock = $this->createMock(ChannelRepositoryInterface::class);
        $this->localeRepositoryMock = $this->createMock(LocaleRepositoryInterface::class);
        $this->productAttributeFilterMock = $this->createMock(ProductAttributeFilter::class);
        $this->productModelAttributeFilterMock = $this->createMock(ProductModelAttributeFilter::class);
        $this->normalizerMock = $this->createMock(NormalizerInterface::class);
        $this->eventDispatcherMock = $this->createMock(EventDispatcherInterface::class);
        $this->loggerMock = $this->createMock(LoggerInterface::class);
    }

    public function dataCopy(): array
    {
        $attribute1 = (new AttributeBuilder())->withCode('attributeCode')->build();

        $mapping = (new AttributeMappingBuilder())
            ->withSourceAttribute($attribute1)
            ->withDestinationAttribute($attribute1)
            ->build();

        $value = ScalarValue::value($attribute1->getCode(), 'xxx');
        $product1 = (new ProductBuilder())->addValue($value)->build();

        $product2 = (new ProductBuilder())->withId(2223)->build();

        $productModel1 = (new ProductModelBuilder())->build();

        $mappingCollection = (new AttributeMappingCollectionBuilder())->withAttributeMapping($mapping)->build();

        return [
            [$product1, $product2, $mappingCollection],
            [$product1, $productModel1, $mappingCollection],
        ];
    }

    /**
     * @dataProvider dataCopy
     */
    public function testCopy(
        EntityWithValuesInterface $sourceProduct,
        EntityWithValuesInterface $destinationProduct,
        AttributeMappingCollection $mappingCollection
    ): void {
        $this->productAttributeFilterMock->method('filter')->willReturn([
            'values' => [
                'attributeCode' => 'example value',
            ],
        ]);
        $this->productModelAttributeFilterMock->method('filter')->willReturn([
            'values' => [
                'attributeCode' => 'example value',
            ],
        ]);

        $this->propertyCopierMock->expects($this->exactly(1))->method('copyData');
        $processor = $this->getRuleProcessorCopierInstance();
        $result = $processor->copy($sourceProduct, $destinationProduct, $mappingCollection);
        $this->assertTrue($result);
    }

    /**
     * @dataProvider dataCopy
     */
    public function testCopyFilteredOut(
        EntityWithValuesInterface $sourceProduct,
        EntityWithValuesInterface $destinationProduct,
        AttributeMappingCollection $mappingCollection
    ): void {
        $this->productAttributeFilterMock->method('filter')->willReturn([
            'values' => [],
        ]);
        $this->productModelAttributeFilterMock->method('filter')->willReturn([
            'values' => [],
        ]);

        $this->propertyCopierMock->expects($this->exactly(0))->method('copyData');
        $processor = $this->getRuleProcessorCopierInstance();
        $result = $processor->copy($sourceProduct, $destinationProduct, $mappingCollection);
        $this->assertFalse($result);
    }

    public function getRuleProcessorCopierInstance(): RuleProcessorCopier
    {
        return new RuleProcessorCopier(
            $this->propertyCopierMock,
            $this->channelRepositoryMock,
            $this->localeRepositoryMock,
            $this->productAttributeFilterMock,
            $this->productModelAttributeFilterMock,
            $this->normalizerMock,
            $this->eventDispatcherMock,
            $this->loggerMock
        );
    }
}