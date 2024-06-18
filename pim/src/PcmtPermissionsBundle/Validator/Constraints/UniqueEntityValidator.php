<?php

declare(strict_types=1);

/**
 * Copyright (c) 2020, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

namespace PcmtPermissionsBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

class UniqueEntityValidator extends \Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntityValidator
{
    /**
     * {@inheritdoc}
     */
    public function validate($entity, Constraint $constraint): void
    {
        parent::validate($entity, $constraint);
    }
}
