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
use Akeneo\Pim\Structure\Bundle\Doctrine\ORM\Repository\FamilyRepository;
use Akeneo\Pim\Structure\Component\Model\FamilyInterface;
use Akeneo\Tool\Component\Batch\Job\JobParameters;
use Akeneo\Tool\Component\Batch\Job\JobRepositoryInterface;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use PcmtRulesBundle\Connector\Job\Step\CopyProductsRuleStep;
use PcmtRulesBundle\Service\CopyProductsRule\CopyProductsRuleProcessor;
use PcmtRulesBundle\Service\JobParametersTextCreator;
use PcmtRulesBundle\Tests\TestDataBuilder\FamilyBuilder;
use PcmtRulesBundle\Tests\TestDataBuilder\ProductBuilder;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class CopyProductsRuleStepTest extends TestCase
{
    /** @var MockObject|EventDispatcherInterface */
    private $eventDispatcherMock;

    /** @var JobRepositoryInterface|MockObject */
    private $jobRepositoryMock;

    /** @var StepExecution|MockObject */
    private $stepExecutionMock;

    /** @var JobParameters|MockObject */
    private $jobParametersMock;

    /** @var FamilyRepository|MockObject */
    private $familyRepositoryMock;

    /** @var ProductQueryBuilderFactoryInterface|MockObject */
    private $pqbFactoryMock;

    /** @var ProductQueryBuilderInterface|MockObject */
    private $productQueryBuilderMock;

    /** @var CopyProductsRuleProcessor|MockObject */
    private $productProcessorMock;

    /** @var JobParametersTextCreator|MockObject */
    private $jobParametersTextCreatorMock;

    protected function setUp(): void
    {
        $this->eventDispatcherMock = $this->createMock(EventDispatcherInterface::class);
        $this->jobRepositoryMock = $this->createMock(JobRepositoryInterface::class);
        $this->stepExecutionMock = $this->createMock(StepExecution::class);
        $this->jobParametersMock = $this->createMock(JobParameters::class);
        $this->stepExecutionMock->method('getJobParameters')->willReturn($this->jobParametersMock);
        $this->familyRepositoryMock = $this->createMock(FamilyRepository::class);
        $this->pqbFactoryMock = $this->createMock(ProductQueryBuilderFactoryInterface::class);
        $this->productQueryBuilderMock = $this->createMock(ProductQueryBuilderInterface::class);
        $this->pqbFactoryMock->method('create')->willReturn($this->productQueryBuilderMock);
        $this->productProcessorMock = $this->createMock(CopyProductsRuleProcessor::class);
        $this->jobParametersTextCreatorMock = $this->createMock(JobParametersTextCreator::class);
    }

    public function dataDoExecute(): array
    {
        $family1 = (new FamilyBuilder())->build();
        $family2 = (new FamilyBuilder())->build();
        $product1 = (new ProductBuilder())->build();
        $product2 = (new ProductBuilder())->build();

        return [
            [null, null, [$product1], 0],
            [$family1, $family2, [], 0],
            [$family1, $family2, [$product1], 1],
            [$family1, $family2, [$product1, $product2], 2],
        ];
    }

    /** @dataProvider dataDoExecute */
    public function testDoExecute(?FamilyInterface $sourceFamily, ?FamilyInterface $destinationFamily, array $products, int $calls): void
    {
        $class = new \ReflectionClass(CopyProductsRuleStep::class);
        $method = $class->getMethod('doExecute');
        $method->setAccessible(true);

        $this->familyRepositoryMock->method('findOneBy')->willReturnOnConsecutiveCalls(
            $sourceFamily,
            $destinationFamily
        );

        $this->productQueryBuilderMock->method('execute')->willReturn($products);

        $step = $this->getRuleStepInstance();

        $this->productProcessorMock->expects($this->exactly($calls))->method('process');

        $method->invokeArgs($step, [$this->stepExecutionMock]);
    }

    private function getRuleStepInstance(): CopyProductsRuleStep
    {
        $step = new CopyProductsRuleStep(
            'name',
            $this->eventDispatcherMock,
            $this->jobRepositoryMock
        );
        $step->setFamilyRepository($this->familyRepositoryMock);
        $step->setPqbFactory($this->pqbFactoryMock);
        $step->setProductProcessor($this->productProcessorMock);
        $step->setJobParametersTextCreator($this->jobParametersTextCreatorMock);

        return $step;
    }
}