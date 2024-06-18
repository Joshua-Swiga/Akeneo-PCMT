<?php

declare(strict_types=1);

/*
 * Copyright (c) 2020, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

namespace PcmtCISBundle\Tests\TestDataBuilder;

use PcmtCISBundle\Constraint\RequiredFieldConstraint;

class RequiredFieldConstraintBuilder
{
    /** @var RequiredFieldConstraint */
    private $constraint;

    public function __construct()
    {
        $this->constraint = new RequiredFieldConstraint();
    }

    public function build(): RequiredFieldConstraint
    {
        return $this->constraint;
    }
}