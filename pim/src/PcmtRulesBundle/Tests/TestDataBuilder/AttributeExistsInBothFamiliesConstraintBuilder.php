<?php

declare(strict_types=1);

/*
 * Copyright (c) 2020, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

namespace PcmtRulesBundle\Tests\TestDataBuilder;

use PcmtRulesBundle\Constraints\AttributeExistsInBothFamiliesConstraint;

class AttributeExistsInBothFamiliesConstraintBuilder
{
    /** @var AttributeExistsInBothFamiliesConstraint */
    private $constraint;

    public function __construct()
    {
        $this->constraint = new AttributeExistsInBothFamiliesConstraint();
    }

    public function build(): AttributeExistsInBothFamiliesConstraint
    {
        return $this->constraint;
    }
}
