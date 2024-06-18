<?php

declare(strict_types=1);

/*
 * Copyright (c) 2021, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

namespace PcmtRulesBundle\Tests\Service;

use Akeneo\Tool\Component\Batch\Job\JobParameters;
use PcmtRulesBundle\Service\JobParametersTextCreator;
use PHPUnit\Framework\TestCase;

class JobParametersTextCreatorTest extends TestCase
{
    protected function setUp(): void
    {
    }

    public function dataCreate(): array
    {
        return [
            [
                [
                    'a' => 'b',
                    'c' => 'd',
                ],
                'a : b, c : d',
            ],
            [
                [
                    'a' => 'b',
                    'c' => ['d', 'e', 'f'],
                ],
                'a : b, c :  { 0 : d, 1 : e, 2 : f } ',
            ],
        ];
    }

    /**
     * @dataProvider dataCreate
     */
    public function testCreate(array $parameters, string $expectedResult): void
    {
        $jobParameters = $this->createMock(JobParameters::class);
        $jobParameters->method('all')->willReturn($parameters);

        $creator = $this->getJobParametersTextCreatorInstance();
        $this->assertEquals($expectedResult, $creator->create($jobParameters));
    }

    private function getJobParametersTextCreatorInstance(): JobParametersTextCreator
    {
        return new JobParametersTextCreator();
    }
}