<?php
/**
 * Copyright (c) 2020, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

declare(strict_types=1);

namespace PcmtPermissionsBundle\Tests\Connector\Job\InvalidItem;

use PcmtPermissionsBundle\Connector\Job\InvalidItem\CategoryAwareInvalidItem;
use PcmtPermissionsBundle\Tests\TestDataBuilder\ProductBuilder;
use PHPUnit\Framework\TestCase;

class CategoryAwareInvalidItemTest extends TestCase
{
    public function testGetInvalidData(): void
    {
        $categoryAwareObject = (new ProductBuilder())->withIdentifier('test')->build();
        $invalidItem = new CategoryAwareInvalidItem($categoryAwareObject);

        $this->assertEquals(
            [
                'identifier' => 'test',
                'label'      => 'test',
                'type'       => 'product',
            ],
            $invalidItem->getInvalidData()
        );
    }
}