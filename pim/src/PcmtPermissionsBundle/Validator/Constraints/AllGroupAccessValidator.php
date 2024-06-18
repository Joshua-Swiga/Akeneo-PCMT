<?php

declare(strict_types=1);

/**
 * Copyright (c) 2020, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

namespace PcmtPermissionsBundle\Validator\Constraints;

use Akeneo\UserManagement\Component\Model\Group;
use Akeneo\UserManagement\Component\Model\User;
use PcmtPermissionsBundle\Entity\CategoryWithAccess;
use Symfony\Component\Form\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class AllGroupAccessValidator extends ConstraintValidator
{
    /** {@inheritdoc} */
    public function validate($value, Constraint $constraint): void
    {
        if (!$constraint instanceof AllGroupAccess) {
            throw new UnexpectedTypeException($constraint, AllGroupAccess::class);
        }

        /** @var CategoryWithAccess $value */
        $this->checkAccesses($constraint, $value->getViewAccess(), 'viewAccess');
        $this->checkAccesses($constraint, $value->getEditAccess(), 'editAccess');
        $this->checkAccesses($constraint, $value->getOwnAccess(), 'ownAccess');
    }

    private function checkAccesses(AllGroupAccess $constraint, array $groups, string $path): void
    {
        if (0 === count($groups)) {
            $this->context->buildViolation($constraint->message)
                ->atPath($path)
                ->addViolation();
        }
        if (count($groups) > 1) {
            foreach ($groups as $group) {
                /** @var Group $group */
                if (User::GROUP_DEFAULT === $group->getName()) {
                    $this->context->buildViolation($constraint->messageAll)
                        ->atPath($path)
                        ->addViolation();
                }
            }
        }
    }
}