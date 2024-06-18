<?php
/**
 * Copyright (c) 2020, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

declare(strict_types=1);

namespace PcmtCoreBundle\Tests\Connector\Job\InvalidItems;

use PcmtCoreBundle\Connector\Job\InvalidItems\UrlInvalidItem;
use PHPUnit\Framework\TestCase;

class UrlInvalidItemTest extends TestCase
{
    public function testUrlInvalidItem(): void
    {
        $url = 'http//:some/url';
        $invalidItem = new UrlInvalidItem($url);
        $this->assertArrayHasKey('url', $invalidItem->getInvalidData());
        $this->assertSame($url, $invalidItem->getInvalidData()['url']);
    }
}