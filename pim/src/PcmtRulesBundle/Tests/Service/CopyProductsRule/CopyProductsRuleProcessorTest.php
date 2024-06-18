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

use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Structure\Component\Model\FamilyInterface;
use Akeneo\Tool\Component\Batch\Job\JobParameters;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use PcmtRulesBundle\Service\AttributeMappingGenerator;
use PcmtRulesBundle\Service\CopyProductsRule\CopyProductsRuleProcessor;
use PcmtRulesBundle\Service\CopyProductsRule\CopyProductToProductModel;
use PcmtRulesBundle\Tests\TestDataBuilder\FamilyBuilder;
use PcmtRulesBundle\Tests\TestDataBuilder\FamilyVariantBuilder;
use PcmtRulesBundle\Tests\TestDataBuilder\ProductBuilder;
use PcmtRulesBundle\Tests\TestDataBuilder\ProductModelBuilder;
use PcmtSharedBundle\Tests\TestDataBuilder\AssociationCollectionBuilder;
use PcmtSharedBundle\Tests\TestDataBuilder\AssociationTypeBuilder;
use PcmtSharedBundle\Tests\TestDataBuilder\ProductModelAssociationBuilder;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class CopyProductsRuleProcessorTest extends TestCase
{
    /** @var StepExecution|MockObject */
    private $stepExecutionMock;

    /** @var CopyProductToProductModel|MockObject */
    private $copyProductToProductModelMock;

    /** @var AttributeMappingGenerator|MockObject */
    private $attributeMappingGeneratorMock;

    protected function setUp(): void
    {
        $this->copyProductToProductModelMock = $this->createMock(CopyProductToProductModel::class);
        $this->attributeMappingGeneratorMock = $this->createMock(AttributeMappingGenerator::class);
        $this->stepExecutionMock = $this->createMock(StepExecution::class);
        $jobParametersMock = $this->createMock(JobParameters::class);
        $jobParametersMock->method('get')->willReturn([]);
        $this->stepExecutionMock->method('getJobParameters')->willReturn($jobParametersMock);
    }

    public function dataProcess(): array
    {
        $sourceFamily = (new FamilyBuilder())->withCode('SOURCE')->build();

        $destinationFamily1 = (new FamilyBuilder())->withCode('DESTINATION1')->build();
        $destinationFamily2 = (new FamilyBuilder())->withCode('DESTINATION2')->build();

        $familyVariant1 = (new FamilyVariantBuilder())
            ->withFamily($destinationFamily1)
            ->build();
        $familyVariant2 = (new FamilyVariantBuilder())
            ->withFamily($destinationFamily2)
            ->build();

        $productModel1 = (new ProductModelBuilder())
            ->withFamilyVariant($familyVariant1)
            ->build();
        $productModel2 = (new ProductModelBuilder())
            ->withFamilyVariant($familyVariant1)
            ->build();
        $productModel3 = (new ProductModelBuilder())
            ->withFamilyVariant($familyVariant2)
            ->build();

        $associations1 = (new AssociationCollectionBuilder())
            ->withAssociation(
                (new ProductModelAssociationBuilder())
                    ->withType((new AssociationTypeBuilder())->withId(1)->build())
                    ->withProductModel($productModel1)
                    ->build()
            )->build();
        $associations2 = (new AssociationCollectionBuilder())
            ->withAssociation(
                (new ProductModelAssociationBuilder())
                    ->withType((new AssociationTypeBuilder())->withId(1)->build())
                    ->withProductModel($productModel1)
                    ->withProductModel($productModel2)
                    ->build()
            )->build();
        $associations3 = (new AssociationCollectionBuilder())
            ->withAssociation(
                (new ProductModelAssociationBuilder())
                    ->withType((new AssociationTypeBuilder())->withId(1)->build())
                    ->withProductModel($productModel1)
                    ->withProductModel($productModel3)
                    ->build()
            )->build();

        $sourceProduct1 = (new ProductBuilder())
            ->withFamily($sourceFamily)
            ->withAssociations($associations1)
            ->build();
        $sourceProduct2 = (new ProductBuilder())
            ->withFamily($sourceFamily)
            ->withAssociations($associations2)
            ->build();
        $sourceProduct3 = (new ProductBuilder())
            ->withFamily($sourceFamily)
            ->withAssociations($associations3)
            ->build();

        return [
            [$destinationFamily1, $sourceProduct1, 1],
            [$destinationFamily1, $sourceProduct2, 2],
            [$destinationFamily1, $sourceProduct3, 1],
        ];
    }

    /**
     * @dataProvider dataProcess
     */
    public function testProcess(FamilyInterface $destinationFamily, ProductInterface $sourceProduct, int $expectedCalls): void
    {
        $this->copyProductToProductModelMock->expects($this->exactly($expectedCalls))->method('process');
        $processor = $this->getProcessorInstance();
        $processor->process($this->stepExecutionMock, $destinationFamily, $sourceProduct);
    }

    private function getProcessorInstance(): CopyProductsRuleProcessor
    {
        return new CopyProductsRuleProcessor(
            $this->copyProductToProductModelMock,
            $this->attributeMappingGeneratorMock
        );
    }
}