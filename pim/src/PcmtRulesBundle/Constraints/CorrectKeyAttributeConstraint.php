<?php
/**
 * Copyright (c) 2020, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

declare(strict_types=1);

namespace PcmtRulesBundle\Constraints;

use Symfony\Component\Validator\Constraint;

class CorrectKeyAttributeConstraint extends Constraint
{
    /** {@inheritdoc} */
    public function getTargets()
    {
        return self::PROPERTY_CONSTRAINT;
    }

    /** {@inheritdoc} */
    public function validatedBy()
    {
        return 'pcmt_correct_key_attribute_constraint_validator';
    }

    /** @var string */
    public $message = 'The key attribute mapping is incorrect';
}