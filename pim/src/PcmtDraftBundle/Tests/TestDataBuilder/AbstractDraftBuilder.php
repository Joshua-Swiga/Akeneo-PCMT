<?php
/**
 * Copyright (c) 2020, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

declare(strict_types=1);

namespace PcmtDraftBundle\Tests\TestDataBuilder;

use PcmtDraftBundle\Entity\DraftInterface;

abstract class AbstractDraftBuilder
{
    protected function setDraftId(DraftInterface $draft, int $value): void
    {
        $reflection = new \ReflectionClass(get_class($draft));
        $property = $reflection->getProperty('id');
        $property->setAccessible(true);
        $property->setValue($draft, $value);
    }
}