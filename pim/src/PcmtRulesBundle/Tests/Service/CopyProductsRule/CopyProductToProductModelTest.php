<?php
/*
 * Copyright (c) 2021, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

declare(strict_types=1);

/*
 * Copyright (c) 2021, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

namespace PcmtRulesBundle\Tests\Service\CopyProductsRule;

use Akeneo\Pim\Enrichment\Bundle\Elasticsearch\ProductQueryBuilderFactory;
use Akeneo\Pim\Enrichment\Component\Product\Builder\ProductBuilderInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;
use Akeneo\Pim\Enrichment\Component\Product\Query\ProductQueryBuilderFactoryInterface;
use Akeneo\Pim\Enrichment\Component\Product\Query\ProductQueryBuilderInterface;
use Akeneo\Pim\Enrichment\Component\Product\Value\ScalarValue;
use Akeneo\Tool\Component\Batch\Job\JobParameters;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use Akeneo\Tool\Component\StorageUtils\Saver\SaverInterface;
use PcmtRulesBundle\Service\CopyProductsRule\CopyProductToProductModel;
use PcmtRulesBundle\Service\RuleProcessorCopier;
use PcmtRulesBundle\Tests\TestDataBuilder\AttributeBuilder;
use PcmtRulesBundle\Tests\TestDataBuilder\AttributeMappingBuilder;
use PcmtRulesBundle\Tests\TestDataBuilder\AttributeMappingCollectionBuilder;
use PcmtRulesBundle\Tests\TestDataBuilder\FamilyBuilder;
use PcmtRulesBundle\Tests\TestDataBuilder\FamilyVariantBuilder;
use PcmtRulesBundle\Tests\TestDataBuilder\ProductBuilder;
use PcmtRulesBundle\Tests\TestDataBuilder\ProductModelBuilder;
use PcmtRulesBundle\Tests\TestDataBuilder\VariantAttributeSetBuilder;
use PcmtRulesBundle\Value\AttributeMappingCollection;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class CopyProductToProductModelTest extends TestCase
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

    /** @var \PcmtRulesBundle\Service\CopyProductsRule\SubEntityFinder|MockObject */
    private $subEntityFinderMock;

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
        $this->subEntityFinderMock = $this->createMock(\PcmtRulesBundle\Service\CopyProductsRule\SubEntityFinder::class);

        $this->stepExecutionMock
            ->method('getJobParameters')
            ->willReturn($this->jobParametersMock);
    }

    public function dataProcess(): array
    {
        $sourceFamily = (new FamilyBuilder())->withCode('SOURCE')->build();

        $destinationFamily = (new FamilyBuilder())->withCode('DESTINATION')->build();

        $attribute1 = (new AttributeBuilder())->withCode('A1')->build();
        $attribute2 = (new AttributeBuilder())->withCode('A2')->build();
        $attribute3 = (new AttributeBuilder())->withCode('A3')->build();

        $mapping1 = (new AttributeMappingBuilder())
            ->withSourceAttribute($attribute1)
            ->withDestinationAttribute($attribute1)
            ->build();
        $mapping2 = (new AttributeMappingBuilder())
            ->withSourceAttribute($attribute2)
            ->withDestinationAttribute($attribute2)
            ->build();
        $mapping3 = (new AttributeMappingBuilder())
            ->withSourceAttribute($attribute3)
            ->withDestinationAttribute($attribute3)
            ->build();
        $attributeMappingCollection = (new AttributeMappingCollectionBuilder())
            ->withAttributeMapping($mapping1)
            ->withAttributeMapping($mapping2)
            ->withAttributeMapping($mapping3)
            ->build();

        $variantAttributeSet1 = (new VariantAttributeSetBuilder())
            ->withLevel(1)
            ->addAttribute($attribute1)
            ->addAttribute($attribute2)
            ->withAxes([$attribute2])
            ->build();
        $variantAttributeSet2 = (new VariantAttributeSetBuilder())
            ->withLevel(2)
            ->addAttribute($attribute3)
            ->build();

        $familyVariant1 = (new FamilyVariantBuilder())
            ->withVariantAttributeSet($variantAttributeSet1)
            ->withFamily($destinationFamily)
            ->build();
        $familyVariant2 = (new FamilyVariantBuilder())
            ->withVariantAttributeSet($variantAttributeSet1)
            ->withVariantAttributeSet($variantAttributeSet2)
            ->withFamily($destinationFamily)
            ->build();

        $productModel1 = (new ProductModelBuilder())
            ->withFamilyVariant($familyVariant1)
            ->build();

        $productModel2 = (new ProductModelBuilder())
            ->withFamilyVariant($familyVariant2)
            ->build();

        $value1 = ScalarValue::value($attribute1->getCode(), 'xxx');
        $value2 = ScalarValue::value($attribute2->getCode(), 'xxx');
        $sourceProduct1 = (new ProductBuilder())
            ->addValue($value1)
            ->withFamily($sourceFamily)
            ->build();
        $sourceProduct2 = (new ProductBuilder())
            ->addValue($value2)
            ->withFamily($sourceFamily)
            ->build();

        return [
            [$sourceProduct1, $productModel1, $attributeMappingCollection, 0, 0], // value of axis attribute does not exists
            [$sourceProduct2, $productModel1, $attributeMappingCollection, 1, 0], // one level
            [$sourceProduct2, $productModel2, $attributeMappingCollection, 1, 1], // two levels - should create pm and p.
        ];
    }

    /**
     * @dataProvider dataProcess
     */
    public function testProcess(
        ProductInterface $sourceProduct,
        ProductModelInterface $destinationProductModel,
        AttributeMappingCollection $attributeMappingCollection,
        int $saveProductCalls,
        int $saveProductModelCalls
    ): void {
        $this->productBuilderMock
            ->method('createProduct')
            ->willReturn(
                (new ProductBuilder())->withFamily($destinationProductModel->getFamily())->build()
            );
        $this->productSaverMock->expects($this->exactly($saveProductCalls))->method('save');
        $this->productModelSaverMock->expects($this->exactly($saveProductModelCalls))->method('save');
        $this->ruleProcessorCopierMock->method('copy')->willReturn(true);
        $processor = $this->getServiceInstance();
        $processor->process(
            $this->stepExecutionMock,
            $sourceProduct,
            $destinationProductModel,
            $attributeMappingCollection
        );
    }

    /**
     * @dataProvider dataProcess
     */
    public function testProcessSubProductFound(
        ProductInterface $sourceProduct,
        ProductModelInterface $destinationProductModel,
        AttributeMappingCollection $attributeMappingCollection,
        int $saveProductCalls,
        int $saveProductModelCalls
    ): void {
        $this->subEntityFinderMock->method('findByAxisAttributes')->willReturn(
            (new ProductBuilder())->build()
        );
        $this->productBuilderMock->expects($this->never())->method('createProduct');
        $this->productSaverMock->expects($this->exactly($saveProductCalls))->method('save');
        $this->ruleProcessorCopierMock->method('copy')->willReturn(true);
        $processor = $this->getServiceInstance();
        $processor->process($this->stepExecutionMock, $sourceProduct, $destinationProductModel, $attributeMappingCollection);
    }

    private function getServiceInstance(): CopyProductToProductModel
    {
        return new CopyProductToProductModel(
            $this->productSaverMock,
            $this->productModelSaverMock,
            $this->productBuilderMock,
            $this->ruleProcessorCopierMock,
            $this->subEntityFinderMock
        );
    }
}