<?php
/**
 * Copyright (c) 2020, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */
declare(strict_types=1);

namespace PcmtCoreBundle\Tests\Connector\Job;

use Akeneo\Pim\Enrichment\Bundle\Doctrine\Common\Saver\ProductSaver;
use Akeneo\Pim\Enrichment\Bundle\Elasticsearch\ProductQueryBuilderFactory;
use Akeneo\Pim\Enrichment\Component\Product\Builder\ProductBuilder;
use Akeneo\Pim\Enrichment\Component\Product\Builder\ProductBuilderInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Query\ProductQueryBuilderInterface;
use Akeneo\Pim\Enrichment\Component\Product\Repository\ProductRepositoryInterface;
use Akeneo\Pim\Enrichment\Component\Product\Value\ScalarValue;
use Akeneo\Pim\Structure\Component\Repository\FamilyRepositoryInterface;
use Akeneo\Tool\Component\Batch\Job\JobParameters;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use Akeneo\Tool\Component\Classification\Repository\CategoryRepositoryInterface;
use Akeneo\Tool\Component\Connector\Step\TaskletInterface;
use Akeneo\Tool\Component\StorageUtils\Cursor\CursorInterface;
use Akeneo\Tool\Component\StorageUtils\Saver\SaverInterface;
use PcmtCoreBundle\Connector\Job\E2OpenFromXmlTasklet;
use PcmtCoreBundle\Service\E2Open\E2OpenAttributesService;
use PcmtCoreBundle\Service\E2Open\PackagingHierarchyProcessor;
use PcmtCoreBundle\Service\E2Open\TradeItemDynamicMapping;
use PcmtCoreBundle\Service\E2Open\TradeItemProductUpdater;
use PcmtCoreBundle\Service\E2Open\TradeItemXmlProcessor;
use PcmtCoreBundle\Tests\TestDataBuilder\CategoryBuilder;
use PcmtCoreBundle\Tests\TestDataBuilder\FamilyBuilder;
use PHPUnit\Framework\MockObject\MockObject as Mock;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class E2OpenFromXmlTaskletTest extends KernelTestCase
{
    /** @var string */
    private $testFilePath = 'src/PcmtCoreBundle/Tests/TestResources/TestImportE2OpenFile.xml';

    /** @var SaverInterface|Mock */
    private $productSaverMock;

    /** @var ProductBuilderInterface|Mock */
    private $productBuilderMock;

    /** @var TradeItemXmlProcessor|Mock */
    private $tradeItemProcessorMock;

    /** @var PackagingHierarchyProcessor|Mock */
    private $packagingHierarchyProcessorMock;

    /** @var StepExecution */
    private $stepExecutionMock;

    /** @var JobParameters|Mock */
    private $jobParametersMock;

    /** @var ProductQueryBuilderFactory|Mock */
    private $productQueryBuilderFactoryMock;

    /** @var CursorInterface|Mock */
    private $productsCursorMock;

    /** @var ProductQueryBuilderInterface|Mock */
    private $productQueryBuilderMock;

    /** @var LoggerInterface|Mock */
    private $loggerMock;

    /** @var CategoryRepositoryInterface|Mock */
    private $categoryRepositoryMock;

    /** @var FamilyRepositoryInterface|Mock */
    private $familyRepositoryMock;

    /** @var ProductRepositoryInterface|Mock */
    private $productRepositoryMock;

    /** @var TradeItemProductUpdater|Mock */
    private $tradeItemProductUpdaterMock;

    /** @var TradeItemDynamicMapping|Mock */
    private $tradeItemDynamicMappingMock;

    protected function setUp(): void
    {
        $this->loggerMock = $this->createMock(LoggerInterface::class);
        $this->productSaverMock = $this->createMock(ProductSaver::class);
        $this->productBuilderMock = $this->createMock(ProductBuilder::class);
        $this->tradeItemProcessorMock = $this->createMock(TradeItemXmlProcessor::class);
        $this->packagingHierarchyProcessorMock = $this->createMock(PackagingHierarchyProcessor::class);
        $this->productQueryBuilderFactoryMock = $this->createMock(ProductQueryBuilderFactory::class);
        $this->productQueryBuilderMock = $this->createMock(ProductQueryBuilderInterface::class);
        $this->productsCursorMock = $this->createMock(CursorInterface::class);
        $this->categoryRepositoryMock = $this->createMock(CategoryRepositoryInterface::class);
        $this->productRepositoryMock = $this->createMock(ProductRepositoryInterface::class);
        $this->familyRepositoryMock = $this->createMock(FamilyRepositoryInterface::class);
        $this->tradeItemProductUpdaterMock = $this->createMock(TradeItemProductUpdater::class);
        $this->tradeItemDynamicMappingMock = $this->createMock(TradeItemDynamicMapping::class);

        $value = ScalarValue::value('GTIN', 'xxx');
        $product = (new \PcmtCoreBundle\Tests\TestDataBuilder\ProductBuilder())->addValue($value)->build();
        $this->productRepositoryMock->method('findBy')->willReturn([$product]);
        $this->categoryRepositoryMock->method('findOneByIdentifier')->willReturn(
            (new CategoryBuilder())->build()
        );
        $this->productQueryBuilderFactoryMock->method('create')->willReturn(
            $this->productQueryBuilderMock
        );
        $this->productQueryBuilderMock->method('execute')->willReturn($this->productsCursorMock);

        $this->stepExecutionMock = $this->createMock(StepExecution::class);
        $this->jobParametersMock = $this->createMock(JobParameters::class);

        $this->stepExecutionMock->expects($this->once())
            ->method('getJobParameters')
            ->willReturn($this->jobParametersMock);

        $this->jobParametersMock->expects($this->once())
            ->method('get')
            ->with('xmlFilePath')
            ->willReturn($this->testFilePath);

        parent::setUp();
    }

    public function dataProcess(): array
    {
        $family = (new FamilyBuilder())->withCode(E2OpenAttributesService::FAMILY_CODE)->build();
        $product = (new \PcmtCoreBundle\Tests\TestDataBuilder\ProductBuilder())->withFamily($family)->build();

        return [
            [$product],
        ];
    }

    /** @dataProvider dataProcess */
    public function testProcessForANewProduct(ProductInterface $product): void
    {
        $importTasklet = $this->getE2OpenFromXmlTaskletInstance();

        $this->productsCursorMock->expects($this->atLeastOnce())
            ->method('current')
            ->willReturn(null);

        $this->productBuilderMock->expects($this->atLeastOnce())
            ->method('createProduct')
            ->willReturn($product);

        $this->tradeItemProductUpdaterMock->expects($this->exactly(3))
            ->method('update');

        $this->productSaverMock->expects($this->atLeastOnce())
            ->method('save');

        $importTasklet->execute();
    }

    /** @dataProvider dataProcess */
    public function testProcessForAnExistingProduct(ProductInterface $product): void
    {
        $importTasklet = $this->getE2OpenFromXmlTaskletInstance();

        $this->productsCursorMock->expects($this->atLeastOnce())
            ->method('current')
            ->willReturn($product);

        $this->productBuilderMock->expects($this->never())
            ->method('createProduct');

        $this->tradeItemProcessorMock->expects($this->atLeastOnce())->method('processNode');

        $this->tradeItemProductUpdaterMock->expects($this->exactly(3))
            ->method('update');

        $this->productSaverMock->expects($this->atLeastOnce())
            ->method('save');

        $importTasklet->execute();
    }

    public function getE2OpenFromXmlTaskletInstance(): TaskletInterface
    {
        $tasklet = new E2OpenFromXmlTasklet(
            $this->productSaverMock,
            $this->productBuilderMock,
            $this->tradeItemProcessorMock,
            $this->productQueryBuilderFactoryMock,
            $this->loggerMock,
            $this->categoryRepositoryMock,
            $this->productRepositoryMock,
            $this->familyRepositoryMock,
            $this->packagingHierarchyProcessorMock,
            $this->tradeItemProductUpdaterMock,
            $this->tradeItemDynamicMappingMock
        );
        $tasklet->setStepExecution($this->stepExecutionMock);

        return $tasklet;
    }
}
