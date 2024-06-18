<?php

declare(strict_types=1);

/*
 * Copyright (c) 2020, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

namespace PcmtRulesBundle\Tests\Service;

use Akeneo\Pim\Enrichment\Bundle\Elasticsearch\ProductQueryBuilderFactory;
use Akeneo\Pim\Enrichment\Component\Product\Builder\ProductBuilderInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Query\ProductQueryBuilderFactoryInterface;
use Akeneo\Pim\Enrichment\Component\Product\Query\ProductQueryBuilderInterface;
use Akeneo\Pim\Enrichment\Component\Product\Value\ScalarValue;
use Akeneo\Pim\Structure\Component\Model\FamilyInterface;
use Akeneo\Tool\Component\Batch\Job\JobParameters;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use Akeneo\Tool\Component\StorageUtils\Saver\SaverInterface;
use PcmtRulesBundle\Service\AttributeMappingGenerator;
use PcmtRulesBundle\Service\RuleProcessor;
use PcmtRulesBundle\Service\RuleProcessorCopier;
use PcmtRulesBundle\Tests\TestDataBuilder\AttributeBuilder;
use PcmtRulesBundle\Tests\TestDataBuilder\AttributeMappingBuilder;
use PcmtRulesBundle\Tests\TestDataBuilder\AttributeMappingCollectionBuilder;
use PcmtRulesBundle\Tests\TestDataBuilder\FamilyBuilder;
use PcmtRulesBundle\Tests\TestDataBuilder\ProductBuilder;
use PcmtRulesBundle\Tests\TestDataBuilder\ProductModelBuilder;
use PcmtRulesBundle\Value\AttributeMappingCollection;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @property ProductQueryBuilderInterface|MockObject productQueryBuilderMock
 */
class RuleProcessorTest extends TestCase
{
    /** @var ProductQueryBuilderFactoryInterface|MockObject */
    private $productQueryBuilderFactoryMock;

    /** @var SaverInterface */
    private $productSaverMock;

    /** @var SaverInterface */
    private $productModelSaverMock;

    /** @var ProductQueryBuilderInterface|MockObject */
    private $productQueryBuilderMock;

    /** @var StepExecution|MockObject */
    private $stepExecutionMock;

    /** @var ProductBuilderInterface|MockObject */
    private $productBuilderMock;

    /** @var RuleProcessorCopier|MockObject */
    private $ruleProcessorCopierMock;

    /** @var JobParameters|MockObject */
    private $jobParametersMock;

    /** @var AttributeMappingGenerator|MockObject */
    private $attributeMappingGeneratorMock;

    protected function setUp(): void
    {
        $this->productQueryBuilderFactoryMock = $this->createMock(ProductQueryBuilderFactory::class);
        $this->productSaverMock = $this->createMock(SaverInterface::class);
        $this->productModelSaverMock = $this->createMock(SaverInterface::class);
        $this->productQueryBuilderMock = $this->createMock(ProductQueryBuilderInterface::class);
        $this->productQueryBuilderFactoryMock->method('create')->willReturn($this->productQueryBuilderMock);
        $this->stepExecutionMock = $this->createMock(StepExecution::class);
        $this->productBuilderMock = $this->createMock(ProductBuilderInterface::class);
        $this->ruleProcessorCopierMock = $this->createMock(RuleProcessorCopier::class);
        $this->jobParametersMock = $this->createMock(JobParameters::class);
        $this->stepExecutionMock
            ->method('getJobParameters')
            ->willReturn($this->jobParametersMock);
        $this->attributeMappingGeneratorMock = $this->createMock(AttributeMappingGenerator::class);
    }

    public function dataProcess(): array
    {
        $product1 = (new ProductBuilder())->withId(2222)->build();
        $destinationProducts = [
            $product1,
            (new ProductBuilder())->withId(2223)->build(),
        ];

        $value = ScalarValue::value('test', 'xxx');
        $product = (new ProductBuilder())->addValue($value)->build();

        $productModel1 = (new ProductModelBuilder())->addProductVariant($product1)->build();
        $productModel2 = (new ProductModelBuilder())->addSubProductModel($productModel1)->build();

        $destinationProductModels = [
            $productModel2,
        ];

        $sourceFamily = (new FamilyBuilder())->withCode('SOURCE')->build();
        $destinationFamily = (new FamilyBuilder())->withCode('DESTINATION')->build();

        $sourceAttribute = (new AttributeBuilder())->withCode('SOURCE_ATTRIBUTE')->build();
        $destinationAttribute = (new AttributeBuilder())->withCode('DESTINATION_ATTRIBUTE')->build();
        $attributeMapping = (new AttributeMappingBuilder())
            ->withSourceAttribute($sourceAttribute)
            ->withDestinationAttribute($destinationAttribute)
            ->build();
        $attributeMappingCollection0 = (new AttributeMappingCollectionBuilder())->build();
        $attributeMappingCollection1 = (new AttributeMappingCollectionBuilder())->withAttributeMapping($attributeMapping)->build();

        return [
            [$sourceFamily, $destinationFamily, $product, $destinationProducts, $attributeMappingCollection0, 2],
            [$sourceFamily, $destinationFamily, $product, $destinationProductModels, $attributeMappingCollection0, 3],
            [$sourceFamily, $destinationFamily, $product, $destinationProductModels, $attributeMappingCollection1, 3],
        ];
    }

    /**
     * @dataProvider dataProcess
     */
    public function testProcess(
        FamilyInterface $sourceFamily,
        FamilyInterface $destinationFamily,
        ProductInterface $sourceProduct,
        array $destinationProducts,
        AttributeMappingCollection $attributeMappingCollection,
        int $expectedCalls
    ): void {
        $this->productBuilderMock->method('createProduct')->willReturn(
            (new ProductBuilder())->withId(2332)->build()
        );
        $this->jobParametersMock
            ->expects($this->at(0))
            ->method('get')
            ->with('attributeMapping')
            ->willReturn([]);
        $this->jobParametersMock
            ->expects($this->at(1))
            ->method('get')
            ->with('keyAttribute')
            ->willReturn([
                'sourceKeyAttribute'      => 'test',
                'destinationKeyAttribute' => 'test',
            ]);
        $this->attributeMappingGeneratorMock->method('get')->willReturn($attributeMappingCollection);
        $this->attributeMappingGeneratorMock
            ->method('getKeyAttributesMapping')
            ->willReturn(
                (new AttributeMappingBuilder())
                    ->withSourceAttribute(
                        (new AttributeBuilder())
                            ->withCode('test')
                            ->build()
                    )
                    ->withDestinationAttribute(
                        (new AttributeBuilder())
                            ->withCode('test')
                            ->build()
                    )
                    ->build()
            );

        $this->productQueryBuilderMock->method('execute')->willReturn($destinationProducts);
        $this->ruleProcessorCopierMock->expects($this->exactly($expectedCalls))->method('copy')->willReturn(true);
        $processor = $this->getRuleProductProcessorInstance();
        $processor->process($this->stepExecutionMock, $sourceFamily, $destinationFamily, $sourceProduct);
    }

    /**
     * @dataProvider dataProcess
     */
    public function testProcessThrowsException(
        FamilyInterface $sourceFamily,
        FamilyInterface $destinationFamily,
        ProductInterface $sourceProduct,
        array $destinationProducts,
        AttributeMappingCollection $attributeMappingCollection,
        int $expectedCalls
    ): void {
        $this->jobParametersMock
            ->expects($this->at(0))
            ->method('get')
            ->with('attributeMapping')
            ->willReturn([]);
        $this->jobParametersMock
            ->expects($this->at(1))
            ->method('get')
            ->with('keyAttribute')
            ->willReturn([
                'sourceKeyAttribute'      => 'test',
                'destinationKeyAttribute' => 'test',
            ]);
        $this->attributeMappingGeneratorMock->method('get')->willReturn($attributeMappingCollection);
        $this->attributeMappingGeneratorMock
            ->method('getKeyAttributesMapping')
            ->willReturn(
                (new AttributeMappingBuilder())
                    ->withSourceAttribute(
                        (new AttributeBuilder())
                            ->withCode('test')
                            ->build()
                    )
                    ->withDestinationAttribute(
                        (new AttributeBuilder())
                            ->withCode('test')
                            ->build()
                    )
                    ->build()
            );

        $this->expectException(\Throwable::class);
        $this->productQueryBuilderMock->method('addFilter')->willThrowException(new \Exception());

        $processor = $this->getRuleProductProcessorInstance();
        $processor->process($this->stepExecutionMock, $sourceFamily, $destinationFamily, $sourceProduct);
    }

    public function dataProcessNoDestinationProduct(): array
    {
        $attributes = [
            (new AttributeBuilder())->build(),
        ];

        $value = ScalarValue::value('test', 'xxx');
        $product = (new ProductBuilder())->addValue($value)->build();

        $sourceFamily = (new FamilyBuilder())->withCode('SOURCE')->build();
        $destinationFamily = (new FamilyBuilder())->withCode('DESTINATION')->build();

        return [
            [$sourceFamily, $destinationFamily, $product, $attributes],
        ];
    }

    /**
     * @dataProvider dataProcessNoDestinationProduct
     */
    public function testProcessNoDestinationProduct(FamilyInterface $sourceFamily, FamilyInterface $destinationFamily, ProductInterface $sourceProduct, array $attributes): void
    {
        $this->productBuilderMock->expects($this->once())->method('createProduct')->willReturn(
            (new ProductBuilder())->withId(2332)->build()
        );
        $this->jobParametersMock
            ->expects($this->at(0))
            ->method('get')
            ->with('attributeMapping')
            ->willReturn([]);
        $this->jobParametersMock
            ->expects($this->at(1))
            ->method('get')
            ->with('keyAttribute')
            ->willReturn([
                'sourceKeyAttribute'      => 'test',
                'destinationKeyAttribute' => 'test',
            ]);
        $this->attributeMappingGeneratorMock
            ->method('getKeyAttributesMapping')
            ->willReturn(
                (new AttributeMappingBuilder())
                    ->withSourceAttribute(
                        (new AttributeBuilder())
                            ->withCode('test')
                            ->build()
                    )
                    ->withDestinationAttribute(
                        (new AttributeBuilder())
                            ->withCode('test')
                            ->build()
                    )
                    ->build()
            );
        $this->productQueryBuilderMock->method('execute')->willReturn([]);
        $this->ruleProcessorCopierMock->expects($this->exactly(1))->method('copy')->willReturn(true);
        $processor = $this->getRuleProductProcessorInstance();
        $processor->process($this->stepExecutionMock, $sourceFamily, $destinationFamily, $sourceProduct);
    }

    /**
     * @dataProvider dataProcess
     */
    public function testProcessException(
        FamilyInterface $sourceFamily,
        FamilyInterface $destinationFamily,
        ProductInterface $sourceProduct,
        array $destinationProducts
    ): void {
        $this->jobParametersMock
            ->expects($this->at(0))
            ->method('get')
            ->with('attributeMapping')
            ->willReturn([]);
        $this->jobParametersMock
            ->expects($this->at(1))
            ->method('get')
            ->with('keyAttribute')
            ->willReturn([
                'sourceKeyAttribute'      => 'test',
                'destinationKeyAttribute' => 'test',
            ]);
        $this->attributeMappingGeneratorMock
            ->method('getKeyAttributesMapping')
            ->willReturn(
                (new AttributeMappingBuilder())
                    ->withSourceAttribute(
                        (new AttributeBuilder())
                            ->withCode('test')
                            ->build()
                    )
                    ->withDestinationAttribute(
                        (new AttributeBuilder())
                            ->withCode('test')
                            ->build()
                    )
                    ->build()
            );
        $this->productQueryBuilderMock->method('execute')->willReturn($destinationProducts);
        $this->ruleProcessorCopierMock->method('copy')->willThrowException(new \Exception());
        $this->stepExecutionMock->expects($this->atLeastOnce())->method('addWarning');
        $processor = $this->getRuleProductProcessorInstance();
        $processor->process($this->stepExecutionMock, $sourceFamily, $destinationFamily, $sourceProduct);
    }

    /**
     * @dataProvider dataProcess
     */
    public function testProcessFilterException(
        FamilyInterface $sourceFamily,
        FamilyInterface $destinationFamily,
        ProductInterface $sourceProduct,
        array $destinationProducts
    ): void {
        $this->jobParametersMock
            ->expects($this->at(0))
            ->method('get')
            ->with('attributeMapping')
            ->willReturn([]);
        $this->jobParametersMock
            ->expects($this->at(1))
            ->method('get')
            ->with('keyAttribute')
            ->willReturn([
                'sourceKeyAttribute'      => 'test',
                'destinationKeyAttribute' => 'test',
            ]);
        $this->productQueryBuilderMock->method('addFilter')->willThrowException(new \Exception());
        $this->expectException(\Throwable::class);
        $processor = $this->getRuleProductProcessorInstance();
        $processor->process($this->stepExecutionMock, $sourceFamily, $destinationFamily, $sourceProduct);
    }

    /**
     * @dataProvider dataProcess
     */
    public function testProcessNoKeyValue(
        FamilyInterface $sourceFamily,
        FamilyInterface $destinationFamily,
        ProductInterface $sourceProduct,
        array $destinationProducts
    ): void {
        $sourceProduct = (new ProductBuilder())->build();
        $this->productQueryBuilderMock->expects($this->never())->method('execute');
        $this->jobParametersMock
            ->expects($this->at(0))
            ->method('get')
            ->with('attributeMapping')
            ->willReturn([]);
        $this->jobParametersMock
            ->expects($this->at(1))
            ->method('get')
            ->with('keyAttribute')
            ->willReturn([
                'sourceKeyAttribute'      => 'test',
                'destinationKeyAttribute' => 'test',
            ]);
        $this->attributeMappingGeneratorMock
            ->method('getKeyAttributesMapping')
            ->willReturn(
                (new AttributeMappingBuilder())
                    ->withSourceAttribute(
                        (new AttributeBuilder())
                            ->withCode('test')
                            ->build()
                    )
                    ->withDestinationAttribute(
                        (new AttributeBuilder())
                            ->withCode('test')
                            ->build()
                    )
                    ->build()
            );
        $processor = $this->getRuleProductProcessorInstance();
        $processor->process($this->stepExecutionMock, $sourceFamily, $destinationFamily, $sourceProduct);
    }

    /**
     * @dataProvider dataProcess
     */
    public function testProcessWarning(
        FamilyInterface $sourceFamily,
        FamilyInterface $destinationFamily,
        ProductInterface $sourceProduct,
        array $destinationProducts
    ): void {
        $sourceProduct = (new ProductBuilder())->build();
        $this->productQueryBuilderMock->expects($this->never())->method('execute');
        $this->jobParametersMock
            ->expects($this->at(0))
            ->method('get')
            ->with('attributeMapping')
            ->willReturn([]);
        $this->jobParametersMock
            ->expects($this->at(1))
            ->method('get')
            ->with('keyAttribute')
            ->willReturn([
                'sourceKeyAttribute'      => 'test',
                'destinationKeyAttribute' => 'test',
            ]);
        $this->attributeMappingGeneratorMock
            ->method('getKeyAttributesMapping')
            ->willReturn(
                (new AttributeMappingBuilder())
                    ->withSourceAttribute(
                        (new AttributeBuilder())
                            ->withCode('test')
                            ->build()
                    )
                    ->withDestinationAttribute(
                        (new AttributeBuilder())
                            ->withCode('test')
                            ->build()
                    )
                    ->build()
            );
        $processor = $this->getRuleProductProcessorInstance();
        $processor->process($this->stepExecutionMock, $sourceFamily, $destinationFamily, $sourceProduct);
    }

    private function getRuleProductProcessorInstance(): RuleProcessor
    {
        return new RuleProcessor(
            $this->productQueryBuilderFactoryMock,
            $this->productSaverMock,
            $this->productModelSaverMock,
            $this->productBuilderMock,
            $this->ruleProcessorCopierMock,
            $this->attributeMappingGeneratorMock
        );
    }
}
