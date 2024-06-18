<?php

declare(strict_types=1);

/*
 * Copyright (c) 2020, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

namespace PcmtRulesBundle\Tests\Connector\Job\Step;

use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Pim\Structure\Component\Repository\AttributeRepositoryInterface;
use Akeneo\Tool\Component\Batch\Job\JobParameters;
use Akeneo\Tool\Component\Batch\Job\JobRepositoryInterface;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use PcmtRulesBundle\Connector\Job\Step\SelectOptionsRuleStep;
use PcmtRulesBundle\Service\SelectOptionsCreator;
use PcmtRulesBundle\Service\SelectOptionsRemover;
use PcmtRulesBundle\Tests\TestDataBuilder\AttributeBuilder;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class SelectOptionsRuleStepTest extends TestCase
{
    /** @var MockObject|EventDispatcherInterface */
    private $eventDispatcherMock;

    /** @var JobRepositoryInterface|MockObject */
    private $jobRepositoryMock;

    /** @var StepExecution|MockObject */
    private $stepExecutionMock;

    /** @var JobParameters|MockObject */
    private $jobParametersMock;
    /**
     * @var AttributeRepositoryInterface|MockObject
     */
    private $attributeRepositoryMock;

    /** @var SelectOptionsCreator|MockObject */
    private $selectOptionsCreatorMock;

    /** @var SelectOptionsRemover|MockObject */
    private $selectOptionsRemoverMock;

    protected function setUp(): void
    {
        $this->eventDispatcherMock = $this->createMock(EventDispatcherInterface::class);
        $this->jobRepositoryMock = $this->createMock(JobRepositoryInterface::class);
        $this->stepExecutionMock = $this->createMock(StepExecution::class);
        $this->jobParametersMock = $this->createMock(JobParameters::class);
        $this->jobParametersMock->method('all')->willReturn(['sss' => 'sdsfsd']);
        $this->stepExecutionMock->method('getJobParameters')->willReturn($this->jobParametersMock);
        $this->attributeRepositoryMock = $this->createMock(AttributeRepositoryInterface::class);
        $this->selectOptionsCreatorMock = $this->createMock(SelectOptionsCreator::class);
        $this->selectOptionsRemoverMock = $this->createMock(SelectOptionsRemover::class);
    }

    public function dataDoExecute(): array
    {
        $destinationAttributeWrongType = (new AttributeBuilder())->withCode('dest_attr')->withType('asdsad')->build();
        $destinationAttributeCorrectType = (new AttributeBuilder())->withCode('dest_attr')->withType('pim_catalog_simpleselect')->build();
        $attributeValue = (new AttributeBuilder())->withCode('attr_value')->build();
        $sourceFamilyCode = 'fsdfdsfdsf';

        return [
            [null, $attributeValue, $sourceFamilyCode, false],
            [$destinationAttributeWrongType, $attributeValue, $sourceFamilyCode, false],
            [$destinationAttributeCorrectType, null, $sourceFamilyCode, false],
            [$destinationAttributeCorrectType, $attributeValue, null, false],
            [$destinationAttributeCorrectType, $attributeValue, $sourceFamilyCode, true],
        ];
    }

    /**
     * @dataProvider dataDoExecute
     */
    public function testDoExecute(
        ?AttributeInterface $destinationAttribute,
        ?AttributeInterface $attributeValue,
        ?string $sourceFamilyCode,
        bool $ifSuccess
    ): void {
        $this->attributeRepositoryMock->method('findOneByIdentifier')->willReturnOnConsecutiveCalls(
            $destinationAttribute,
            $attributeValue
        );
        $this->jobParametersMock->method('get')->willReturn($sourceFamilyCode);
        $class = new \ReflectionClass(SelectOptionsRuleStep::class);
        $method = $class->getMethod('doExecute');
        $method->setAccessible(true);

        $step = $this->getSelectOptionsRuleStepInstance();

        if (!$ifSuccess) {
            $this->expectException(\Throwable::class);
        } else {
            $this->selectOptionsRemoverMock->expects($this->once())->method('remove');
            $this->selectOptionsCreatorMock->expects($this->once())->method('create');
        }

        $method->invokeArgs($step, [$this->stepExecutionMock]);
    }

    private function getSelectOptionsRuleStepInstance(): SelectOptionsRuleStep
    {
        $step = new SelectOptionsRuleStep(
            'name',
            $this->eventDispatcherMock,
            $this->jobRepositoryMock
        );
        $step->setAttributeRepository($this->attributeRepositoryMock);
        $step->setSelectOptionsCreator($this->selectOptionsCreatorMock);
        $step->setSelectOptionsRemover($this->selectOptionsRemoverMock);

        return $step;
    }
}