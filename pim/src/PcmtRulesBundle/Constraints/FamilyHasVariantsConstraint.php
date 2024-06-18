<?php

declare(strict_types=1);

/**
 * Copyright (c) 2020, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

namespace PcmtRulesBundle\Constraints;

use Symfony\Component\Validator\Constraint;

class FamilyHasVariantsConstraint extends Constraint
{
    /** {@inheritdoc} */
    public function getTargets()
    {
        return self::PROPERTY_CONSTRAINT;
    }

    /** {@inheritdoc} */
    public function validatedBy()
    {
        return 'pcmt_family_has_variants_constraint_validator';
    }

    /** @var string */
    public $message = 'The family should have variants.';
}
