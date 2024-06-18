<?php
/*
 * Copyright (c) 2020, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

declare(strict_types=1);

/**
 * Copyright (c) 2020, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

namespace PcmtCISBundle\Validator;

use PcmtCISBundle\Constraint\RequiredFieldConstraint;
use PcmtCISBundle\Entity\Subscription;
use Symfony\Component\Form\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class RequiredFieldConstraintValidator extends ConstraintValidator
{
    /**
     * {@inheritdoc}
     */
    public function validate($entity, Constraint $constraint): void
    {
        if (!$constraint instanceof RequiredFieldConstraint) {
            throw new UnexpectedTypeException($constraint, RequiredFieldConstraint::class);
        }

        /** @var Subscription $entity */
        if ($entity->getGTIN() || $entity->getGPCCategoryCode() || $entity->getTargetMarketCountryCode()) {
            return;
        }

        $this->context->buildViolation($constraint->message)
            ->addViolation();
    }
}
