<?php

declare(strict_types=1);

/**
 * Copyright (c) 2020, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

namespace PcmtCISBundle\Constraint;

use Symfony\Component\Validator\Constraint;

class UniqueValuesConstraint extends Constraint
{
    /** {@inheritdoc} */
    public function getTargets()
    {
        return self::CLASS_CONSTRAINT;
    }

    /** {@inheritdoc} */
    public function validatedBy()
    {
        return 'pcmt_cis_unique_values_constraint_validator';
    }

    /** @var string */
    public $message = 'pcmt.cis.create.error.unique_values';
}
