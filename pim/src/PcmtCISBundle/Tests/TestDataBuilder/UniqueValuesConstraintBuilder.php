<?php

declare(strict_types=1);

/*
 * Copyright (c) 2020, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

namespace PcmtCISBundle\Tests\TestDataBuilder;

use PcmtCISBundle\Constraint\UniqueValuesConstraint;

class UniqueValuesConstraintBuilder
{
    /** @var UniqueValuesConstraint */
    private $constraint;

    public function __construct()
    {
        $this->constraint = new UniqueValuesConstraint();
    }

    public function build(): UniqueValuesConstraint
    {
        return $this->constraint;
    }
}