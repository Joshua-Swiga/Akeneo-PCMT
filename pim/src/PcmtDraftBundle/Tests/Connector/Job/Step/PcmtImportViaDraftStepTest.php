<?php

/**
 * Copyright (c) 2020, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

declare(strict_types=1);

namespace PcmtDraftBundle\Tests\Connector\Job\Step;

use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Tool\Component\Batch\Item\ItemProcessorInterface;
use Akeneo\Tool\Component\Batch\Item\ItemReaderInterface;
use Akeneo\Tool\Component\Batch\Job\JobRepositoryInterface;
use Akeneo\Tool\Component\Batch\Model\JobExecution;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use Akeneo\UserManagement\Component\Model\UserInterface;
use Akeneo\UserManagement\Component\Repository\UserRepositoryInterface;
use Exception;
use PcmtDraftBundle\Connector\Job\Step\PcmtImportViaDraftStep;
use PcmtDraftBundle\Connector\Job\Writer\Database\PcmtDraftWriterInterface;
use PcmtDraftBundle\Exception\UserNotFoundException;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class PcmtImportViaDraftStepTest extends TestCase
{
    private const TEST_STEP_NAME = 'test_step_name';
    private const TEST_BATCH_SIZE = 100;
    private const TEST_USER_IDENTIFIER = 'test_user_identifier';

    /** @var PcmtImportViaDraftStep */
    private $pcmtImportViaDraftStep;

    /** @var EventDispatcherInterface|MockObject */
    private $eventDispatcherMock;

    /** @var JobRepositoryInterface|MockObject */
    private $jobRepositoryMock;

    /** @var ItemReaderInterface|MockObject */
    private $itemReaderMock;

    /** @var ItemProcessorInterface|MockObject */
    private $itemProcessorMock;

    /** @var PcmtDraftWriterInterface|MockObject */
    private $itemWriterMock;

    /** @var UserRepositoryInterface|MockObject */
    private $userRepositoryMock;

    /** @var StepExecution|MockObject */
    private $stepExecutionMock;

    /** @var JobExecution|MockObject */
    private $jobExecutionMock;

    /** @var UserInterface|MockObject */
    private $userMock;

    protected function setUp(): void
    {
        $this->eventDispatcherMock = $this->createMock(EventDispatcherInterface::class);
        $this->jobRepositoryMock = $this->createMock(JobRepositoryInterface::class);
        $this->itemReaderMock = $this->createMock(ItemReaderInterface::class);
        $this->itemProcessorMock = $this->createMock(ItemProcessorInterface::class);
        $this->itemWriterMock = $this->createMock(PcmtDraftWriterInterface::class);
        $this->userRepositoryMock = $this->createMock(UserRepositoryInterface::class);
        $this->stepExecutionMock = $this->createMock(StepExecution::class);
        $this->jobExecutionMock = $this->createMock(JobExecution::class);
        $this->userMock = $this->createMock(UserInterface::class);

        $this->pcmtImportViaDraftStep = $this->getPcmtImportViaDraftStepInstance();

        $this->stepExecutionMock
            ->method('getJobExecution')
            ->willReturn($this->jobExecutionMock);

        $this->jobExecutionMock
            ->method('getUser')
            ->willReturn(self::TEST_USER_IDENTIFIER);

        $this->userRepositoryMock
            ->method('findOneByIdentifier')
            ->with(self::TEST_USER_IDENTIFIER)
            ->willReturn($this->userMock);

        $this->userMock
            ->method('getId')
            ->willReturn(1);
    }

    private function getPcmtImportViaDraftStepInstance(): PcmtImportViaDraftStep
    {
        $pcmtImportViaDraftStep = new PcmtImportViaDraftStep(
            self::TEST_STEP_NAME,
            $this->eventDispatcherMock,
            $this->jobRepositoryMock,
            $this->itemReaderMock,
            $this->itemProcessorMock,
            $this->itemWriterMock,
            self::TEST_BATCH_SIZE
        );

        $pcmtImportViaDraftStep->setUserRepository($this->userRepositoryMock);

        return $pcmtImportViaDraftStep;
    }

    public function dataDoExecute(): array
    {
        return [
            'with_one_dummy_item' => [
                'items' => [
                    [
                        'identifier' => 'DUMMY_IDENTIFIER',
                    ],
                ],
            ],
            'with_three_dummy_item' => [
                'items' => [
                    [
                        'identifier' => 'IDENTIFIER_1',
                    ],
                    [
                        'identifier' => 'IDENTIFIER_2',
                    ],
                    [
                        'identifier' => 'IDENTIFIER_3',
                    ],
                ],
            ],
        ];
    }

    /**
     * @dataProvider dataDoExecute
     *
     * @throws Exception
     */
    public function testDoExecute(array $items): void
    {
        $this->itemReaderMock
            ->method('read')
            ->willReturnOnConsecutiveCalls($items, null);

        $this->itemProcessorMock
            ->method('process')
            ->with($items)
            ->willReturn(
                $this->createMock(ProductInterface::class)
            );

        $this->userRepositoryMock
            ->method('find')
            ->willReturn($this->userMock);

        $this->itemWriterMock->expects($this->once())
            ->method('setUser')
            ->with($this->userMock);

        $this->pcmtImportViaDraftStep->doExecute($this->stepExecutionMock);
    }

    /**
     * @dataProvider dataDoExecute
     *
     * @throws Exception
     */
    public function testDoExecuteWhenNoUserFound(array $items): void
    {
        $this->itemReaderMock
            ->method('read')
            ->willReturnOnConsecutiveCalls($items, null);

        $this->itemProcessorMock
            ->method('process')
            ->with($items)
            ->willReturn(
                $this->createMock(ProductInterface::class)
            );

        $this->userRepositoryMock
            ->method('find')
            ->willReturn(null);

        $this->expectException(UserNotFoundException::class);

        $this->pcmtImportViaDraftStep->doExecute($this->stepExecutionMock);
    }
}