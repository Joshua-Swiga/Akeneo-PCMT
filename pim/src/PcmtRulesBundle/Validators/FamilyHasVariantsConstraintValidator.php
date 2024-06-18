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

namespace PcmtRulesBundle\Validators;

use Akeneo\Pim\Structure\Component\Model\FamilyInterface;
use Akeneo\Pim\Structure\Component\Repository\FamilyRepositoryInterface;
use PcmtRulesBundle\Constraints\FamilyHasVariantsConstraint;
use Symfony\Component\Form\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class FamilyHasVariantsConstraintValidator extends ConstraintValidator
{
    /** @var FamilyRepositoryInterface */
    private $familyRepository;

    public function __construct(
        FamilyRepositoryInterface $familyRepository
    ) {
        $this->familyRepository = $familyRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function validate($value, Constraint $constraint): void
    {
        if (!$constraint instanceof FamilyHasVariantsConstraint) {
            throw new UnexpectedTypeException($constraint, FamilyHasVariantsConstraint::class);
        }

        $family = $this->getFamilyByCode($value);
        if (!$family || 0 === $family->getFamilyVariants()->count()) {
            $this->context->buildViolation($constraint->message)
                ->addViolation();
        }
    }

    private function getFamilyByCode(string $code): ?FamilyInterface
    {
        return $this->familyRepository->findOneBy(['code' => $code]);
    }
}
