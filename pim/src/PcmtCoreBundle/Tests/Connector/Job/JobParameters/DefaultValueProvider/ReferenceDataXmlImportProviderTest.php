<?php

declare(strict_types=1);

/**
 * Copyright (c) 2020, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

namespace PcmtCoreBundle\Tests\Connector\Job\JobParameters\DefaultValueProvider;

use PcmtCoreBundle\Connector\Job\JobParameters\DefaultValueProvider\ReferenceDataXmlImportProvider;
use PHPUnit\Framework\TestCase;

class ReferenceDataXmlImportProviderTest extends TestCase
{
    public function testGetDefaultValues(): void
    {
        $o = new ReferenceDataXmlImportProvider([]);
        $values = $o->getDefaultValues();
        $this->assertArrayHasKey('xmlMapping', $values);
        $this->assertArrayHasKey('uploadAllowed', $values);
        $this->assertArrayHasKey('decimalSeparator', $values);
        $this->assertArrayHasKey('dirPath', $values);
        $this->assertIsString($values['dirPath']);
        $this->assertSame('ALL', $values['filePath']);
    }
}