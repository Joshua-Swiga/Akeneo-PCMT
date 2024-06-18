<?php
/**
 * Copyright (c) 2020, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

declare(strict_types=1);

namespace PcmtDraftBundle\Tests\TestDataBuilder;

use Akeneo\Pim\Structure\Component\Model\VariantAttributeSet;

class VariantAttributeSetBuilder
{
    /** @var VariantAttributeSet */
    private $set;

    public function __construct()
    {
        $this->set = new VariantAttributeSet();
    }

    public function withLevel(int $level): self
    {
        $this->set->setLevel($level);

        return $this;
    }

    public function build(): VariantAttributeSet
    {
        return $this->set;
    }
}