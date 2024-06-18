<?php

declare(strict_types=1);

/**
 * Copyright (c) 2020, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

namespace PcmtDraftBundle\Tests\Connector\Job\JobParameters\DefaultValueProvider;

use Akeneo\Tool\Component\Batch\Job\JobInterface;
use PcmtDraftBundle\Connector\Job\JobParameters\DefaultValueProvider\DraftsBulkActions;
use PcmtDraftBundle\MassActions\DraftsBulkActionOperation;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use ReflectionException;

class DraftsBulkActionsTest extends TestCase
{
    public function testGetDefaultValues(): void
    {
        $draftsBulkApprove = new DraftsBulkActions(['test_job_name']);

        $defaultValues = $draftsBulkApprove->getDefaultValues();

        $this->assertIsArray($defaultValues);
        $this->assertArrayHasKey(DraftsBulkActionOperation::KEY_SELECTED, $defaultValues);
        $this->assertArrayHasKey(DraftsBulkActionOperation::KEY_EXCLUDED, $defaultValues);
        $this->assertArrayHasKey(DraftsBulkActionOperation::KEY_ALL_SELECTED, $defaultValues);
        $this->assertArrayHasKey(DraftsBulkActionOperation::KEY_USER_TO_NOTIFY, $defaultValues);
        $this->assertArrayHasKey(DraftsBulkActionOperation::KEY_IS_USER_AUTHENTICATED, $defaultValues);
    }

    public function dataSupports(): array
    {
        return [
            'when_job_is_supported'     => [
                'supported_jobs'  => [
                    'test_job_name',
                ],
                'job_name'        => 'test_job_name',
                'expected_result' => true,
            ],
            'when_job_is_not_supported' => [
                'supported_jobs'  => [
                    'test_job_name',
                ],
                'job_name'        => 'another_test_job_name',
                'expected_result' => false,
            ],
        ];
    }

    /**
     * @dataProvider dataSupports
     *
     * @throws ReflectionException
     */
    public function testSupports(array $supportedJobs, string $jobName, bool $expectedResult): void
    {
        $draftsBulkApprove = new DraftsBulkActions($supportedJobs);

        /** @var JobInterface|MockObject $job */
        $job = $this->createMock(JobInterface::class);
        $job->method('getName')->willReturn($jobName);

        $this->assertSame($expectedResult, $draftsBulkApprove->supports($job));
    }
}