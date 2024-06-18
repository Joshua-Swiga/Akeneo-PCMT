<?php
/**
 * Copyright (c) 2020, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

declare(strict_types=1);

namespace PcmtRulesBundle\Tests\TestDataBuilder;

use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Pim\Structure\Component\Model\VariantAttributeSet;

class VariantAttributeSetBuilder
{
    /** @var VariantAttributeSet */
    private $set;

    public function __construct()
    {
        $this->set = new VariantAttributeSet();
        $this->set->setLevel(0);
    }

    public function withLevel(int $level): self
    {
        $this->set->setLevel($level);

        return $this;
    }

    public function withAxes(array $arrayOfAttributes): self
    {
        $this->set->setAxes($arrayOfAttributes);

        return $this;
    }

    public function addAttribute(AttributeInterface $attribute): self
    {
        $this->set->addAttribute($attribute);

        return $this;
    }

    public function build(): VariantAttributeSet
    {
        return $this->set;
    }
}