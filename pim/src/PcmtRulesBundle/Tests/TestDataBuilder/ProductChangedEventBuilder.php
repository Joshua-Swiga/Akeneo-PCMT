<?php

declare(strict_types=1);

/*
 * Copyright (c) 2021, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

namespace PcmtRulesBundle\Tests\TestDataBuilder;

use PcmtRulesBundle\Event\ProductChangedEvent;

class ProductChangedEventBuilder
{
    /** @var ProductChangedEvent */
    private $event;

    public function __construct()
    {
        $this->event = new ProductChangedEvent(
            (new ProductBuilder())->build(),
            (new AttributeBuilder())->build(),
            'en',
            'channel',
            (new ValueBuilder())->build(),
            (new ValueBuilder())->build()
        );
    }

    public function build(): ProductChangedEvent
    {
        return $this->event;
    }
}