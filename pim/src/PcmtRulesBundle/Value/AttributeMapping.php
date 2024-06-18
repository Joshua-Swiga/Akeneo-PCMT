<?php

declare(strict_types=1);

/*
 * Copyright (c) 2021, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

namespace PcmtRulesBundle\Value;

use Akeneo\Pim\Structure\Component\Model\AttributeInterface;

class AttributeMapping
{
    /** @var AttributeInterface */
    private $sourceAttribute;

    /** @var AttributeInterface */
    private $destinationAttribute;

    public function __construct(
        AttributeInterface $sourceAttribute,
        AttributeInterface $destinationAttribute
    ) {
        $this->sourceAttribute = $sourceAttribute;
        $this->destinationAttribute = $destinationAttribute;
    }

    public function getSourceAttribute(): AttributeInterface
    {
        return $this->sourceAttribute;
    }

    public function getDestinationAttribute(): AttributeInterface
    {
        return $this->destinationAttribute;
    }
}