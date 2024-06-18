<?php

declare(strict_types=1);

/**
 * Copyright (c) 2020, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

namespace PcmtRulesBundle\Constraints;

use Symfony\Component\Validator\Constraint;

class CorrectAttributeMappingConstraint extends Constraint
{
    /** {@inheritdoc} */
    public function getTargets()
    {
        return self::PROPERTY_CONSTRAINT;
    }

    /** {@inheritdoc} */
    public function validatedBy()
    {
        return 'pcmt_correct_attribute_mapping_constraint_validator';
    }

    /** @var string */
    public $message = 'The attribute mapping is incorrect';
}
