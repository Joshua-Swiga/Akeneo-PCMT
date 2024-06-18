<?php

declare(strict_types=1);

/**
 * Copyright (c) 2020, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

namespace PcmtRulesBundle\Constraints;

use Symfony\Component\Validator\Constraint;

class DifferentFamilyConstraint extends Constraint
{
    /** {@inheritdoc} */
    public function getTargets()
    {
        return self::PROPERTY_CONSTRAINT;
    }

    /** {@inheritdoc} */
    public function validatedBy()
    {
        return 'pcmt_attribute_different_family_constraint_validator';
    }

    /** @var string */
    public $message = 'The source and destination family should be different.';
}
