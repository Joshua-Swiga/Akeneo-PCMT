<?php
/**
 * Copyright (c) 2020, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

declare(strict_types=1);

namespace PcmtCoreBundle\Tests\Connector\Job\InvalidItems;

use PcmtCoreBundle\Connector\Job\InvalidItems\XmlInvalidItem;
use PHPUnit\Framework\TestCase;

class XmlInvalidItemTest extends TestCase
{
    public function testXmlInvalidItem(): void
    {
        $filePath = 'some/file/path.xml';
        $invalidItem = new XmlInvalidItem($filePath);
        $this->assertArrayHasKey('file', $invalidItem->getInvalidData());
        $this->assertSame($filePath, $invalidItem->getInvalidData()['file']);
    }
}