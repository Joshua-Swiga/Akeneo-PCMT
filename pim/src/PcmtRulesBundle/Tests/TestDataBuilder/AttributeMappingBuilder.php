<?php

declare(strict_types=1);

/*
 * Copyright (c) 2021, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

namespace PcmtRulesBundle\Tests\TestDataBuilder;

use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use PcmtRulesBundle\Value\AttributeMapping;

class AttributeMappingBuilder
{
    /** @var AttributeInterface */
    private $sourceAttribute;

    /** @var AttributeInterface */
    private $destinationAttribute;

    public function __construct()
    {
        $this->sourceAttribute = (new AttributeBuilder())->build();
        $this->destinationAttribute = (new AttributeBuilder())->build();
    }

    public function withSourceAttribute(AttributeInterface $attribute): self
    {
        $this->sourceAttribute = $attribute;

        return $this;
    }

    public function withDestinationAttribute(AttributeInterface $attribute): self
    {
        $this->destinationAttribute = $attribute;

        return $this;
    }

    public function build(): AttributeMapping
    {
        return new AttributeMapping(
            $this->sourceAttribute,
            $this->destinationAttribute
        );
    }
}