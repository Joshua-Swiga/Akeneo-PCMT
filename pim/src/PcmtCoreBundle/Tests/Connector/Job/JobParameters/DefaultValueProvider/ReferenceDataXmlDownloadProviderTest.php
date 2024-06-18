<?php

declare(strict_types=1);

/**
 * Copyright (c) 2020, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

namespace PcmtCoreBundle\Tests\Connector\Job\JobParameters\DefaultValueProvider;

use PcmtCoreBundle\Connector\Job\JobParameters\DefaultValueProvider\ReferenceDataXmlDownloadProvider;
use PHPUnit\Framework\TestCase;

class ReferenceDataXmlDownloadProviderTest extends TestCase
{
    /**
     * @dataProvider dataGetDefaultValues
     */
    public function testGetDefaultValues(string $dirName): void
    {
        $o = new ReferenceDataXmlDownloadProvider([], $dirName);
        $values = $o->getDefaultValues();
        $this->assertArrayHasKey('dirPath', $values);
        $this->assertIsString($values['dirPath']);
        $this->assertStringContainsString($dirName, $values['dirPath']);
        $this->assertNotNull($values['filePath']);
    }

    public function dataGetDefaultValues(): array
    {
        return [
            ['xxxxxx'],
            ['ref_data'],
        ];
    }
}