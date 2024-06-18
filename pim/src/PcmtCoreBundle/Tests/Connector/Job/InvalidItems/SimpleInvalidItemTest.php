<?php
/**
 * Copyright (c) 2020, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

declare(strict_types=1);

namespace PcmtCoreBundle\Tests\Connector\Job\InvalidItems;

use PcmtSharedBundle\Connector\Job\InvalidItems\SimpleInvalidItem;
use PHPUnit\Framework\TestCase;

class SimpleInvalidItemTest extends TestCase
{
    public function testDefaultSimpleInvalidItem(): void
    {
        $invalidItem = new SimpleInvalidItem();
        $this->assertSame([], $invalidItem->getInvalidData());
    }

    public function testSimpleInvalidItemWithParameters(): void
    {
        $data = [
            'array'  => [
                'some',
                'array',
            ],
            'string' => 'some string',
            'int'    => 7,
            'null'   => null,
        ];
        $invalidItem = new SimpleInvalidItem($data);
        $this->assertSame($data, $invalidItem->getInvalidData());
    }
}