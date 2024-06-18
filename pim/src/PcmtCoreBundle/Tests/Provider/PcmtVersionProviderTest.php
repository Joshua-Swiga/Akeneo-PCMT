<?php

/**
 * Copyright (c) 2020, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

declare(strict_types=1);

namespace PcmtCoreBundle\Tests\Provider;

use PcmtCoreBundle\Provider\PcmtVersionProvider;
use PHPUnit\Framework\TestCase;

class PcmtVersionProviderTest extends TestCase
{
    public function testVersionWithSnapshot(): void
    {
        $provider = new PcmtVersionProvider('2.0.1-snapshot');

        $this->assertEquals('PCMT', $provider->getEdition());
        $this->assertEquals('2', $provider->getMajor());
        $this->assertEquals('0', $provider->getMinor());
        $this->assertEquals('1', $provider->getPatch());
        $this->assertEquals('snapshot', $provider->getStability());
        $this->assertEquals('', $provider->getSha());
        $this->assertEquals('PCMT 2.0.1 Snapshot', $provider->getFullVersion());
    }

    public function testVersionWithSnapshotAndCommitSha(): void
    {
        $provider = new PcmtVersionProvider('2.0.1-snapshot-sha8ddcc07b');

        $this->assertEquals('PCMT', $provider->getEdition());
        $this->assertEquals('2', $provider->getMajor());
        $this->assertEquals('0', $provider->getMinor());
        $this->assertEquals('1', $provider->getPatch());
        $this->assertEquals('snapshot', $provider->getStability());
        $this->assertEquals('sha8ddcc07b', $provider->getSha());
        $this->assertEquals('PCMT 2.0.1 Snapshot (sha8ddcc07b)', $provider->getFullVersion());
    }

    public function testVersionStable(): void
    {
        $provider = new PcmtVersionProvider('2.0.1');

        $this->assertEquals('PCMT', $provider->getEdition());
        $this->assertEquals('2', $provider->getMajor());
        $this->assertEquals('0', $provider->getMinor());
        $this->assertEquals('1', $provider->getPatch());
        $this->assertEquals('stable', $provider->getStability());
        $this->assertEquals('', $provider->getSha());
        $this->assertEquals('PCMT 2.0.1 Stable', $provider->getFullVersion());
    }
}