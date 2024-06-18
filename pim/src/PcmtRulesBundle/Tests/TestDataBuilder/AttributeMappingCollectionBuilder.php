<?php

declare(strict_types=1);

/*
 * Copyright (c) 2021, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

namespace PcmtRulesBundle\Tests\TestDataBuilder;

use PcmtRulesBundle\Value\AttributeMapping;
use PcmtRulesBundle\Value\AttributeMappingCollection;

class AttributeMappingCollectionBuilder
{
    /** @var AttributeMappingCollection */
    private $collection;

    public function __construct()
    {
        $this->collection = new AttributeMappingCollection();
    }

    public function withAttributeMapping(AttributeMapping $mapping): self
    {
        $this->collection->add($mapping);

        return $this;
    }

    public function build(): AttributeMappingCollection
    {
        return $this->collection;
    }
}