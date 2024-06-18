<?php
/**
 * Copyright (c) 2020, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

declare(strict_types=1);

namespace PcmtDraftBundle\Tests\Connector\Job\InvalidItems;

use PcmtDraftBundle\Connector\Job\InvalidItems\DraftInvalidItem;
use PHPUnit\Framework\TestCase;

class DraftInvalidItemTest extends TestCase
{
    public function testDraftInvalidItem(): void
    {
        $item = new DraftInvalidItem(56, []);

        $this->assertEquals(56, $item->getDraftId());
        $this->assertEquals([], $item->getInvalidData());
    }
}