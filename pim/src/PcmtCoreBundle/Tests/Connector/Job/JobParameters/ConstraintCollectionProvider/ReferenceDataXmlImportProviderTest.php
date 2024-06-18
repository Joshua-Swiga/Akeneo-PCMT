<?php

declare(strict_types=1);

/**
 * Copyright (c) 2020, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

namespace PcmtCoreBundle\Tests\Connector\Job\JobParameters\ConstraintCollectionProvider;

use PcmtCoreBundle\Connector\Job\JobParameters\ConstraintCollectionProvider\ReferenceDataXmlImportProvider;
use PHPUnit\Framework\TestCase;

class ReferenceDataXmlImportProviderTest extends TestCase
{
    public function testGetConstraintCollection(): void
    {
        $o = new ReferenceDataXmlImportProvider([]);
        $collection = $o->getConstraintCollection();
        $this->assertArrayHasKey('filePath', $collection->fields);
    }
}