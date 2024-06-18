<?php

declare(strict_types=1);

/**
 * Copyright (c) 2020, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

namespace PcmtCISBundle\Constraint;

use Symfony\Component\Validator\Constraint;

class RequiredFieldConstraint extends Constraint
{
    /** {@inheritdoc} */
    public function getTargets()
    {
        return self::CLASS_CONSTRAINT;
    }

    /** {@inheritdoc} */
    public function validatedBy()
    {
        return 'pcmt_cis_required_field_constraint_validator';
    }

    /** @var string */
    public $message = 'pcmt.cis.create.error.required_field';
}
