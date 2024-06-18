<?php

declare(strict_types=1);

/*
 * Copyright (c) 2021, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

namespace PcmtRulesBundle\Tests\Connector\Job\Step;

use Akeneo\Pim\Enrichment\Component\Product\Query\ProductQueryBuilderFactoryInterface;
use Akeneo\Pim\Enrichment\Component\Product\Query\ProductQueryBuilderInterface;
use Akeneo\Tool\Component\Batch\Job\JobParameters;
use Akeneo\Tool\Component\Batch\Job\JobRepositoryInterface;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use Akeneo\Tool\Component\FileStorage\Model\FileInfoInterface;
use PcmtRulesBundle\Connector\Job\Step\PullImagesRuleStep;
use PcmtRulesBundle\Service\AttributesLevelValidator;
use PcmtRulesBundle\Service\PullImageService;
use PcmtRulesBundle\Service\UpdateImageService;
use PcmtRulesBundle\Tests\TestDataBuilder\ProductBuilder;
use PcmtRulesBundle\Tests\TestDataBuilder\ProductModelBuilder;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class PullImagesRuleStepTest extends TestCase
{
    /** @var MockObject|EventDispatcherInterface */
    private $eventDispatcherMock;

    /** @var JobRepositoryInterface|MockObject */
    private $jobRepositoryMock;

    /** @var StepExecution|MockObject */
    private $stepExecutionMock;

    /** @var JobParameters|MockObject */
    private $jobParametersMock;

    /** @var AttributesLevelValidator|MockObject */
    private $attributesLevelValidatorMock;

    /** @var PullImageService|MockObject */
    private $pullImageServiceMock;

    /** @var UpdateImageService|MockObject */
    private $objectUpdateImageServiceMock;

    /** @var ProductQueryBuilderFactoryInterface|MockObject */
    private $pqbFactoryMock;

    /** @var ProductQueryBuilderInterface|MockObject */
    private $productQueryBuilderMock;

    protected function setUp(): void
    {
        $this->eventDispatcherMock = $this->createMock(EventDispatcherInterface::class);
        $this->jobRepositoryMock = $this->createMock(JobRepositoryInterface::class);
        $this->stepExecutionMock = $this->createMock(StepExecution::class);
        $this->jobParametersMock = $this->createMock(JobParameters::class);
        $this->jobParametersMock->method('all')->willReturn(['sss' => 'sdsfsd']);
        $this->jobParametersMock->method('get')->willReturn('sth');
        $this->stepExecutionMock->method('getJobParameters')->willReturn($this->jobParametersMock);

        $this->attributesLevelValidatorMock = $this->createMock(AttributesLevelValidator::class);
        $this->pullImageServiceMock = $this->createMock(PullImageService::class);

        $this->objectUpdateImageServiceMock = $this->createMock(UpdateImageService::class);
        $this->pqbFactoryMock = $this->createMock(ProductQueryBuilderFactoryInterface::class);
        $this->productQueryBuilderMock = $this->createMock(ProductQueryBuilderInterface::class);

        $this->pqbFactoryMock->method('create')->willReturn($this->productQueryBuilderMock);
    }

    public function dataDoExecute(): array
    {
        $product1 = (new ProductBuilder())->build();
        $product2 = (new ProductBuilder())->build();
        $productModel1 = (new ProductModelBuilder())->build();
        $productModel2 = (new ProductModelBuilder())->addSubProductModel($productModel1)->addProductVariant($product2)->build();

        return [
            [[], true, 0, 0],
            [[$product1, $productModel2], true, 4, 4],
            [[$product1, $productModel2], false, 4, 0],
        ];
    }

    /**
     * @dataProvider dataDoExecute
     */
    public function testDoExecute(array $products, bool $attributeValidationResult, int $entitiesToValidateCount, int $entitiesToPullImageCount): void
    {
        $this->productQueryBuilderMock->method('execute')->willReturnOnConsecutiveCalls($products);

        $class = new \ReflectionClass(PullImagesRuleStep::class);
        $method = $class->getMethod('doExecute');
        $method->setAccessible(true);

        $step = $this->getRuleStepInstance();

        $this->attributesLevelValidatorMock->expects($this->exactly($entitiesToValidateCount))->method('validate')->willReturn($attributeValidationResult);

        $fileMock = $this->createMock(FileInfoInterface::class);
        $this->pullImageServiceMock->expects($this->exactly($entitiesToPullImageCount))->method('processEntity')->willReturn($fileMock);
        $this->objectUpdateImageServiceMock->expects($this->exactly($entitiesToPullImageCount))->method('processEntity');

        $method->invokeArgs($step, [$this->stepExecutionMock]);
    }

    /**
     * @dataProvider dataDoExecute
     */
    public function testDoExecutePullImageThrowsException(array $products, bool $attributeValidationResult, int $entitiesToValidateCount, int $entitiesToPullImageCount): void
    {
        $this->productQueryBuilderMock->method('execute')->willReturnOnConsecutiveCalls($products);

        $class = new \ReflectionClass(PullImagesRuleStep::class);
        $method = $class->getMethod('doExecute');
        $method->setAccessible(true);

        $step = $this->getRuleStepInstance();

        $this->attributesLevelValidatorMock->expects($this->exactly($entitiesToValidateCount))->method('validate')->willReturn($attributeValidationResult);

        $this->pullImageServiceMock->expects($this->exactly($entitiesToPullImageCount))->method('processEntity')->willThrowException(new \Exception());
        $this->objectUpdateImageServiceMock->expects($this->exactly(0))->method('processEntity');

        $this->stepExecutionMock->expects($this->exactly($entitiesToPullImageCount))->method('addWarning');

        $method->invokeArgs($step, [$this->stepExecutionMock]);
    }

    /**
     * @dataProvider dataDoExecute
     */
    public function testDoExecuteUpdateObjectThrowsException(array $products, bool $attributeValidationResult, int $entitiesToValidateCount, int $entitiesToPullImageCount): void
    {
        $this->productQueryBuilderMock->method('execute')->willReturnOnConsecutiveCalls($products);

        $class = new \ReflectionClass(PullImagesRuleStep::class);
        $method = $class->getMethod('doExecute');
        $method->setAccessible(true);

        $step = $this->getRuleStepInstance();

        $this->attributesLevelValidatorMock->expects($this->exactly($entitiesToValidateCount))->method('validate')->willReturn($attributeValidationResult);

        $fileMock = $this->createMock(FileInfoInterface::class);
        $this->pullImageServiceMock->expects($this->exactly($entitiesToPullImageCount))->method('processEntity')->willReturn($fileMock);
        $this->objectUpdateImageServiceMock->expects($this->exactly($entitiesToPullImageCount))->method('processEntity')->willThrowException(new \Exception());

        $this->stepExecutionMock->expects($this->exactly($entitiesToPullImageCount))->method('addWarning');

        $method->invokeArgs($step, [$this->stepExecutionMock]);
    }

    private function getRuleStepInstance(): PullImagesRuleStep
    {
        $step = new PullImagesRuleStep(
            'name',
            $this->eventDispatcherMock,
            $this->jobRepositoryMock
        );
        $step->setAttributesLevelValidator($this->attributesLevelValidatorMock);
        $step->setPqbFactory($this->pqbFactoryMock);
        $step->setPullImageService($this->pullImageServiceMock);
        $step->setProductUpdateImageService($this->objectUpdateImageServiceMock);
        $step->setProductModelUpdateImageService($this->objectUpdateImageServiceMock);

        return $step;
    }
}