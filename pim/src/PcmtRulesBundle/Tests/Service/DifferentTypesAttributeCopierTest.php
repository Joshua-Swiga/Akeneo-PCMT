<?php

declare(strict_types=1);

/*
 * Copyright (c) 2021, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

namespace PcmtRulesBundle\Tests\Service;

use Akeneo\Pim\Enrichment\Component\Product\Builder\EntityWithValuesBuilderInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\EntityWithValuesInterface;
use Akeneo\Pim\Enrichment\Component\Product\Validator\AttributeValidatorHelper;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use PcmtRulesBundle\Service\DifferentTypesAttributeCopier;
use PcmtRulesBundle\Tests\TestDataBuilder\AttributeBuilder;
use PcmtRulesBundle\Tests\TestDataBuilder\AttributeOptionBuilder;
use PcmtRulesBundle\Tests\TestDataBuilder\ProductBuilder;
use PcmtRulesBundle\Tests\TestDataBuilder\ValueBuilder;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class DifferentTypesAttributeCopierTest extends TestCase
{
    /** @var EntityWithValuesBuilderInterface|MockObject */
    private $entityWithValuesBuilderMock;

    /** @var AttributeValidatorHelper|MockObject */
    private $attrValidatorHelperMock;

    /** @var NormalizerInterface|MockObject */
    private $normalizerMock;

    private const OPTION_CODE = 'examplecode';

    protected function setUp(): void
    {
        $this->entityWithValuesBuilderMock = $this->createMock(EntityWithValuesBuilderInterface::class);
        $this->attrValidatorHelperMock = $this->createMock(AttributeValidatorHelper::class);
        $this->normalizerMock = $this->createMock(NormalizerInterface::class);
    }

    public function dataSupportsAttributes(): array
    {
        $attributeText = (new AttributeBuilder())->withType('pim_catalog_text')->build();
        $attributeSimpleSelect = (new AttributeBuilder())->withType('pim_catalog_simpleselect')->build();
        $attributeOther = (new AttributeBuilder())->withType('pim_catalog_other')->build();
        $v1 = [
            ['pim_catalog_text'],
            ['pim_catalog_simpleselect'],
            $attributeText,
            $attributeSimpleSelect,
            true,
        ];
        $v2 = [
            ['pim_catalog_text'],
            ['pim_catalog_simpleselect'],
            $attributeOther,
            $attributeSimpleSelect,
            false,
        ];
        $v3 = [
            ['pim_catalog_text'],
            ['pim_catalog_simpleselect'],
            $attributeSimpleSelect,
            $attributeText,
            false,
        ];

        return [
            $v1,
            $v2,
            $v3,
        ];
    }

    public function dataCopyAttributeData(): array
    {
        $attributeText = (new AttributeBuilder())->withCode('xxx')->withType('pim_catalog_text')->build();
        $attributeSimpleSelectWithOption = (new AttributeBuilder())
            ->withType('pim_catalog_simpleselect')
            ->withOption((new AttributeOptionBuilder())->withCode(self::OPTION_CODE)->build())
            ->build();
        $attributeSimpleSelectWithoutOption = (new AttributeBuilder())
            ->withType('pim_catalog_simpleselect')
            ->build();
        $value = (new ValueBuilder())->withAttributeCode('xxx')->build();
        $entityFrom = (new ProductBuilder())->addValue($value)->build();
        $entityTo = (new ProductBuilder())->build();
        $v1 = [
            ['pim_catalog_text'],
            ['pim_catalog_simpleselect'],
            $attributeText,
            $attributeSimpleSelectWithOption,
            $entityFrom,
            $entityTo,
            false,
        ];
        $v2 = [
            ['pim_catalog_text'],
            ['pim_catalog_simpleselect'],
            $attributeText,
            $attributeSimpleSelectWithoutOption,
            $entityFrom,
            $entityTo,
            true,
        ];

        return [
            $v1,
            $v2,
        ];
    }

    /**
     * @dataProvider dataSupportsAttributes
     */
    public function testSupportsAttributes(array $supportedFromTypes, array $supportedToTypes, AttributeInterface $attributeFrom, AttributeInterface $attributeTo, bool $expectedResult): void
    {
        $copier = $this->getDifferentTypesAttributeCopierInstance($supportedFromTypes, $supportedToTypes);
        $this->assertEquals($expectedResult, $copier->supportsAttributes($attributeFrom, $attributeTo));
    }

    /**
     * @dataProvider dataCopyAttributeData
     */
    public function testCopyAttributeData(
        array $supportedFromTypes,
        array $supportedToTypes,
        AttributeInterface $attributeFrom,
        AttributeInterface $attributeTo,
        EntityWithValuesInterface $entityFrom,
        EntityWithValuesInterface $entityTo,
        bool $expectedException
    ): void {
        $this->normalizerMock->method('normalize')->willReturn([
            'data' => self::OPTION_CODE,
        ]);

        if ($expectedException) {
            $this->expectException(\Throwable::class);
        } else {
            $this->entityWithValuesBuilderMock->expects($this->once())->method('addOrReplaceValue');
        }
        $copier = $this->getDifferentTypesAttributeCopierInstance($supportedFromTypes, $supportedToTypes);
        $copier->copyAttributeData(
            $entityFrom,
            $entityTo,
            $attributeFrom,
            $attributeTo,
            []
        );
    }

    private function getDifferentTypesAttributeCopierInstance(array $supportedFromTypes, array $supportedToTypes): DifferentTypesAttributeCopier
    {
        return new DifferentTypesAttributeCopier(
            $this->entityWithValuesBuilderMock,
            $this->attrValidatorHelperMock,
            $this->normalizerMock,
            $supportedFromTypes,
            $supportedToTypes
        );
    }
}