<?php

declare(strict_types=1);

/*
 * Copyright (c) 2020, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

namespace PcmtCISBundle\Tests\Service;

use Carbon\Carbon;
use PcmtCISBundle\Service\FileUniqueIdentifierGenerator;
use PHPUnit\Framework\TestCase;

class FileUniqueIdentifierGeneratorTest extends TestCase
{
    /** @var FileUniqueIdentifierGenerator */
    private $generator;

    protected function setUp(): void
    {
        $this->generator = new FileUniqueIdentifierGenerator();
    }

    public function testGenerate(): void
    {
        $date = Carbon::create(2020, 10, 20, 10, 15, 51);

        Carbon::setTestNow($date);

        $this->assertEquals('20201020T101551Z', $this->generator->generate());
    }
}