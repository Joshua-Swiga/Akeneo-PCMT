<?php
/**
 * Copyright (c) 2020, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

declare(strict_types=1);

namespace PcmtCoreBundle\Tests\Connector\Job\Step;

use Akeneo\Tool\Bundle\BatchBundle\Job\DoctrineJobRepository;
use Akeneo\Tool\Component\Batch\Job\JobRepositoryInterface;
use Akeneo\Tool\Component\Batch\Model\JobExecution;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use Akeneo\Tool\Component\Batch\Step\ItemStep;
use PcmtCoreBundle\Connector\Job\Processor\PcmtReferenceDataProcessor;
use PcmtCoreBundle\Connector\Job\Reader\File\ReferenceDataXmlReader;
use PcmtCoreBundle\Connector\Job\Writer\ReferenceDataWriter;
use PHPUnit\Framework\MockObject\MockObject as Mock;
use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\EventDispatcher;

class ReferenceDataXmlDownloadStepTest extends TestCase
{
    /** @var ReferenceDataXmlReader|Mock */
    protected $readerMock;

    /** @var PcmtReferenceDataProcessor|Mock */
    protected $processorMock;

    /** @var ReferenceDataWriter|Mock */
    protected $writerMock;

    /** @var JobRepositoryInterface|Mock */
    protected $jobRepositoryMock;

    /** @var EventDispatcher|Mock */
    protected $eventDispatcherMock;

    protected function setUp(): void
    {
        $this->readerMock = $this->createMock(ReferenceDataXmlReader::class);
        $this->processorMock = $this->createMock(PcmtReferenceDataProcessor::class);
        $this->writerMock = $this->createMock(ReferenceDataWriter::class);
        $this->eventDispatcherMock = $this->createMock(EventDispatcher::class);
        $this->jobRepositoryMock = $this->createMock(DoctrineJobRepository::class);
        parent::setUp();
    }

    public function testWillExecuteTaskConsecutively(): void
    {
        $step = $this->getItemStepInstance();
        $stepName = 'reference_data_xml_download';
        $jobExecution = $this->createMock(JobExecution::class);

        $this->readerMock->expects($this->at(0))
            ->method('read');

        $this->processorMock->expects($this->at(0))
            ->method('process');

        $this->writerMock->expects($this->at(0))
            ->method('write');

        $step->doExecute(new StepExecution($stepName, $jobExecution));
    }

    protected function getItemStepInstance(): ItemStep
    {
        return new ItemStep(
            'process',
            $this->eventDispatcherMock,
            $this->jobRepositoryMock,
            $this->readerMock,
            $this->processorMock,
            $this->writerMock
        );
    }
}