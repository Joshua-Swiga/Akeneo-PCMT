<?php
/**
 * Copyright (c) 2020, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

declare(strict_types=1);

namespace PcmtCustomDatasetBundle\Tests\Processor\Denormalizer;

use Akeneo\Pim\Enrichment\Component\Product\Comparator\Filter\FilterInterface;
use Akeneo\Pim\Enrichment\Component\Product\Connector\Processor\Denormalizer\FindProductToImport;
use Akeneo\Pim\Enrichment\Component\Product\Connector\Processor\Denormalizer\MediaStorer;
use Akeneo\Pim\Enrichment\Component\Product\EntityWithFamilyVariant\AddParent;
use Akeneo\Pim\Enrichment\Component\Product\ProductModel\Filter\AttributeFilterInterface;
use Akeneo\Tool\Component\Batch\Item\InvalidItemException;
use Akeneo\Tool\Component\Batch\Job\JobParameters;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use Akeneo\Tool\Component\StorageUtils\Detacher\ObjectDetacherInterface;
use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Akeneo\Tool\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use PcmtCustomDatasetBundle\Processor\Denormalizer\PcmtProductProcessor;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class PcmtProductProcessorTest extends TestCase
{
    /** @var PcmtProductProcessor */
    protected $processor;

    /** @var IdentifiableObjectRepositoryInterface|MockObject */
    private $repositoryMock;

    /** @var FindProductToImport|MockObject */
    private $findProductToImportMock;

    /** @var AddParent|MockObject */
    private $addParentMock;

    /** @var ObjectUpdaterInterface|MockObject */
    private $updaterMock;

    /** @var ValidatorInterface|MockObject */
    private $validatorMock;

    /** @var ObjectDetacherInterface|MockObject */
    private $detacherMock;

    /** @var FilterInterface|MockObject */
    private $productFilterMock;

    /** @var AttributeFilterInterface|MockObject */
    private $productAttributeFilterMock;

    /** @var MediaStorer|MockObject */
    private $mediaStorerMock;

    /** @var StepExecution|MockObject */
    private $stepExecutionMock;

    /** @var JobParameters|MockObject */
    private $jobParametersMock;

    protected function setUp(): void
    {
        $this->repositoryMock = $this->createMock(IdentifiableObjectRepositoryInterface::class);
        $this->findProductToImportMock = $this->createMock(FindProductToImport::class);
        $this->addParentMock = $this->createMock(AddParent::class);
        $this->updaterMock = $this->createMock(ObjectUpdaterInterface::class);
        $this->validatorMock = $this->createMock(ValidatorInterface::class);
        $this->detacherMock = $this->createMock(ObjectDetacherInterface::class);
        $this->productFilterMock = $this->createMock(FilterInterface::class);
        $this->productAttributeFilterMock = $this->createMock(AttributeFilterInterface::class);
        $this->mediaStorerMock = $this->createMock(MediaStorer::class);
        $this->stepExecutionMock = $this->createMock(StepExecution::class);
        $this->jobParametersMock = $this->createMock(JobParameters::class);

        $this->stepExecutionMock->method('getJobParameters')->willReturn($this->jobParametersMock);

        $this->processor = new PcmtProductProcessor(
            $this->repositoryMock,
            $this->findProductToImportMock,
            $this->addParentMock,
            $this->updaterMock,
            $this->validatorMock,
            $this->detacherMock,
            $this->productFilterMock,
            $this->productAttributeFilterMock,
            $this->mediaStorerMock
        );
        $this->processor->setStepExecution($this->stepExecutionMock);
    }

    /**
     * @dataProvider dataProcessGetEnabledFromJobParametersIfMissing
     */
    public function testProcessGetEnabledFromJobParametersIfMissing(array $item): void
    {
        $this->repositoryMock->method('getIdentifierProperties')
            ->willReturn(['identifier']);
        $firstCallWith = 'enabledComparison';
        if (!isset($item['enabled'])) {
            $firstCallWith = 'enabled';
        }
        $this->jobParametersMock->expects($this->at(0))->method('get')->with($firstCallWith);

        $this->processor->process($item);
    }

    public function dataProcessGetEnabledFromJobParametersIfMissing(): array
    {
        return [
            'item without enabled' => [
                'item' => [
                    'identifier' => 'example',
                    'values'     => [],
                    'parent'     => '',
                    'family'     => '',
                ],
            ],
            'item with enabled'    => [
                'item' => [
                    'identifier' => 'example',
                    'enabled'    => true,
                    'values'     => [],
                    'parent'     => '',
                    'family'     => '',
                ],
            ],
        ];
    }

    /**
     * @dataProvider dataProcessIdentifierException
     */
    public function testProcessIdentifierException(array $item): void
    {
        $this->expectException(InvalidItemException::class);
        $this->expectExceptionMessage('The identifier must be filled');
        $this->processor->process($item);
        $this->assertIsArray($item);
    }

    public function dataProcessIdentifierException(): array
    {
        return [
            'identifier is null'    => [
                [
                    'identifier' => null,
                ],
            ],
            'identifier is empty'   => [
                [
                    'identifier' => '',
                ],
            ],
            'identifier is missing' => [
                [],
            ],
        ];
    }
}