<?php

declare(strict_types=1);

/**
 * Copyright (c) 2020, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

namespace PcmtDraftBundle\Tests\Connector\Job\JobParameters\ConstraintCollectionProvider;

use Akeneo\Tool\Component\Batch\Job\JobInterface;
use PcmtDraftBundle\Connector\Job\JobParameters\ConstraintCollectionProvider\DraftsBulkActions;
use PcmtDraftBundle\MassActions\DraftsBulkActionOperation;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class DraftsBulkActionsTest extends TestCase
{
    public function testGetConstraintCollection(): void
    {
        $draftsBulkApprove = new DraftsBulkActions(['test_job_name']);

        $fields = $draftsBulkApprove->getConstraintCollection()->fields;

        $this->assertIsArray($fields);

        $this->assertArrayHasKey(DraftsBulkActionOperation::KEY_EXCLUDED, $fields);
        $this->assertArrayHasKey(DraftsBulkActionOperation::KEY_SELECTED, $fields);
        $this->assertArrayHasKey(DraftsBulkActionOperation::KEY_ALL_SELECTED, $fields);
        $this->assertArrayHasKey(DraftsBulkActionOperation::KEY_USER_TO_NOTIFY, $fields);
        $this->assertArrayHasKey(DraftsBulkActionOperation::KEY_IS_USER_AUTHENTICATED, $fields);
    }

    public function dataSupports(): array
    {
        return [
            'when_job_name_is_supported'     => [
                'supported_names' => ['test_job_name'],
                'job_name'        => 'test_job_name',
                'expected_result' => true,
            ],
            'when_job_name_is_not_supported' => [
                'supported_names' => ['test_job_name'],
                'job_name'        => 'another_test_job_name',
                'expected_result' => false,
            ],
        ];
    }

    /**
     * @dataProvider dataSupports
     */
    public function testSupports(array $supportedNames, string $jobName, bool $expectedResult): void
    {
        $draftsBulkApprove = new DraftsBulkActions($supportedNames);

        /** @var JobInterface|MockObject $job */
        $job = $this->createMock(JobInterface::class);
        $job->method('getName')->willReturn($jobName);

        $this->assertSame($expectedResult, $draftsBulkApprove->supports($job));
    }
}