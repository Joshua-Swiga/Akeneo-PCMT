<?php

declare(strict_types=1);

/*
 * Copyright (c) 2020, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

namespace PcmtRulesBundle\Tests\Service;

use Akeneo\Channel\Bundle\Doctrine\Repository\LocaleRepository;
use Akeneo\Pim\Enrichment\Component\Product\Query\ProductQueryBuilderFactoryInterface;
use Akeneo\Pim\Enrichment\Component\Product\Query\ProductQueryBuilderInterface;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Pim\Structure\Component\Model\AttributeOption;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use Akeneo\Tool\Component\StorageUtils\Factory\SimpleFactoryInterface;
use Akeneo\Tool\Component\StorageUtils\Saver\SaverInterface;
use PcmtRulesBundle\Service\SelectOptionsCreator;
use PcmtRulesBundle\Tests\TestDataBuilder\AttributeBuilder;
use PcmtRulesBundle\Tests\TestDataBuilder\ProductBuilder;
use PcmtRulesBundle\Tests\TestDataBuilder\ProductModelBuilder;
use PcmtRulesBundle\Tests\TestDataBuilder\ValueBuilder;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class SelectOptionsCreatorTest extends TestCase
{
    /** @var ProductQueryBuilderFactoryInterface|MockObject */
    private $pqbFactoryMock;

    /** @var LocaleRepository|MockObject */
    private $localeRepositoryMock;

    /** @var SimpleFactoryInterface|MockObject */
    private $optionFactoryMock;

    /** @var SaverInterface|MockObject */
    private $optionSaverMock;

    /** @var StepExecution|MockObject */
    private $stepExecutionMock;

    /** @var ProductQueryBuilderInterface|MockObject */
    private $pqbMock;

    protected function setUp(): void
    {
        $this->pqbFactoryMock = $this->createMock(ProductQueryBuilderFactoryInterface::class);
        $this->pqbMock = $this->createMock(ProductQueryBuilderInterface::class);
        $this->pqbFactoryMock->method('create')->willReturn($this->pqbMock);
        $this->localeRepositoryMock = $this->createMock(LocaleRepository::class);
        $this->optionFactoryMock = $this->createMock(SimpleFactoryInterface::class);
        $this->optionSaverMock = $this->createMock(SaverInterface::class);
        $this->stepExecutionMock = $this->createMock(StepExecution::class);
        $this->localeRepositoryMock->method('getActivatedLocaleCodes')->willReturn(['en_US', 'pl_PL']);
    }

    public function dataCreate(): array
    {
        $destinationAttribute = (new AttributeBuilder())->build();
        $attributeForValue = (new AttributeBuilder())->build();

        return [
            [
                'source_family_code',
                'attribute_code_for_code',
                $destinationAttribute,
                $attributeForValue,
            ],
        ];
    }

    /** @dataProvider dataCreate */
    public function testCreate(
        string $sourceFamilyCode,
        string $attributeCodeForCode,
        AttributeInterface $destinationAttribute,
        AttributeInterface $attributeForValue
    ): void {
        $product1 = (new ProductBuilder())
            ->addValue((new ValueBuilder())->withAttributeCode($attributeCodeForCode)->withData('xxx')->build())
            ->addValue((new ValueBuilder())->withAttributeCode($attributeForValue->getCode())->withData('xxxv')->build())
            ->build();

        $product2 = (new ProductBuilder())
            ->addValue((new ValueBuilder())->withAttributeCode($attributeCodeForCode)->withData('xxx')->build())
            ->addValue((new ValueBuilder())->withAttributeCode($attributeForValue->getCode())->withData('xxxv')->build())
            ->build();

        $product3 = (new ProductBuilder())
            ->addValue((new ValueBuilder())->withAttributeCode($attributeCodeForCode . 'other')->withData('xxx')->build())
            ->addValue((new ValueBuilder())->withAttributeCode($attributeForValue->getCode() . 'other')->withData('xxxv')->build())
            ->build();

        $product4 = (new ProductBuilder())
            ->addValue((new ValueBuilder())->withAttributeCode($attributeCodeForCode)->withData(null)->build())
            ->addValue((new ValueBuilder())->withAttributeCode($attributeForValue->getCode())->withData('xxxv')->build())
            ->build();

        $productModel2 = (new ProductModelBuilder())
            ->addValue((new ValueBuilder())->withAttributeCode($attributeCodeForCode)->withData('xxxpm')->build())
            ->addValue((new ValueBuilder())->withAttributeCode($attributeForValue->getCode())->withData('xxxpmv')->build())
            ->build();

        $productModel1 = (new ProductModelBuilder())
            ->addValue((new ValueBuilder())->withAttributeCode($attributeCodeForCode)->withData('xxxpm1')->build())
            ->addValue((new ValueBuilder())->withAttributeCode($attributeForValue->getCode())->withData(null)->build())
            ->addProductVariant($product2)
            ->addSubProductModel($productModel2)
            ->build();

        $this->pqbMock->method('execute')->willReturn([
            $product1,
            $product3,
            $product4,
            $productModel1,
        ]);

        $this->optionFactoryMock->method('create')->willReturnCallback(
            function () {
                return new AttributeOption();
            }
        );

        $this->optionSaverMock->expects($this->exactly(2))->method('save');

        //$this->stepExecutionMock->expects($this->exactly(2))->method('incrementSummaryInfo');
        $creator = $this->getSelectOptionsCreatorInstance();
        $creator->create(
            $this->stepExecutionMock,
            $sourceFamilyCode,
            $attributeCodeForCode,
            $destinationAttribute,
            $attributeForValue
        );
    }

    public function getSelectOptionsCreatorInstance(): SelectOptionsCreator
    {
        return new SelectOptionsCreator(
            $this->pqbFactoryMock,
            $this->localeRepositoryMock,
            $this->optionFactoryMock,
            $this->optionSaverMock
        );
    }
}