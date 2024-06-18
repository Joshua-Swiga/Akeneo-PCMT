<?php
/**
 * Copyright (c) 2020, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

declare(strict_types=1);

namespace PcmtCoreBundle\Tests\Connector\Job\Step;

use Akeneo\Tool\Component\Batch\Item\InvalidItemException;
use Akeneo\Tool\Component\Batch\Item\InvalidItemInterface;
use Akeneo\Tool\Component\Batch\Item\ItemProcessorInterface;
use Akeneo\Tool\Component\Batch\Job\JobRepositoryInterface;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use PcmtCoreBundle\Connector\Job\Reader\CrossJoinExportReaderInterface;
use PcmtCoreBundle\Connector\Job\Step\MstSupplierExportStep;
use PcmtCoreBundle\Connector\Job\Writer\CrossJoinExportWriterInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class MstSupplierExportStepTest extends TestCase
{
    /** @var MstSupplierExportStep */
    private $mstSupplierExportStep;

    /** @var EventDispatcherInterface|MockObject */
    private $eventDispatcherMock;

    /** @var JobRepositoryInterface|MockObject */
    private $jobRepositoryMock;

    /** @var CrossJoinExportReaderInterface|MockObject */
    private $readerMock;

    /** @var ItemProcessorInterface|MockObject */
    private $processorMock;

    /** @var CrossJoinExportWriterInterface|MockObject */
    private $writerMock;

    /** @var CrossJoinExportWriterInterface|MockObject */
    private $stepExecutionMock;

    protected function setUp(): void
    {
        $this->eventDispatcherMock = $this->createMock(EventDispatcherInterface::class);
        $this->jobRepositoryMock = $this->createMock(JobRepositoryInterface::class);
        $this->readerMock = $this->createMock(CrossJoinExportReaderInterface::class);
        $this->processorMock = $this->createMock(ItemProcessorInterface::class);
        $this->writerMock = $this->createMock(CrossJoinExportWriterInterface::class);
        $this->stepExecutionMock = $this->createMock(StepExecution::class);

        $this->mstSupplierExportStep = new MstSupplierExportStep(
            'step_name',
            $this->eventDispatcherMock,
            $this->jobRepositoryMock,
            $this->readerMock,
            $this->processorMock,
            $this->writerMock,
            1
        );
    }

    public function testDoExecuteSetFamilyToCrossReadBeforeInvokeReadCross(): void
    {
        $this->readerMock
            ->expects($this->at(0))
            ->method('setFamilyToCrossRead')
            ->with(MstSupplierExportStep::FAMILY_TO_CROSS_READ);
        $this->readerMock
            ->expects($this->at(1))
            ->method('readCross')
            ->willReturn(null);
        $this->processorMock
            ->expects($this->never())
            ->method('process');

        $this->mstSupplierExportStep->doExecute($this->stepExecutionMock);
    }

    public function testDoExecuteCatchErrorWhenCrossItemIsInvalid(): void
    {
        $itemMock = $this->createMock(InvalidItemInterface::class);
        $itemMock
            ->method('getInvalidData')
            ->willReturn([]);
        $invalidItemException = new InvalidItemException(
            'message',
            $itemMock
        );
        $this->readerMock
            ->expects($this->at(1))
            ->method('readCross')
            ->willThrowException($invalidItemException);
        $this->jobRepositoryMock
            ->expects($this->once())
            ->method('addWarning');
        $this->eventDispatcherMock
            ->expects($this->once())
            ->method('dispatch');

        $this->mstSupplierExportStep->doExecute($this->stepExecutionMock);
    }
}