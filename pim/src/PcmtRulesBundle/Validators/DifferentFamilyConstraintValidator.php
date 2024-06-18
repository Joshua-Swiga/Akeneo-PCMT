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
use PcmtRulesBundle\Constraints\DifferentFamilyConstraint;
use Symfony\Component\Form\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class DifferentFamilyConstraintValidator extends ConstraintValidator
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
    public function validate($entity, Constraint $constraint): void
    {
        if (!$constraint instanceof DifferentFamilyConstraint) {
            throw new UnexpectedTypeException($constraint, DifferentFamilyConstraint::class);
        }

        $sourceFamilyCode = $this->getValueFromRoot('sourceFamily');
        $destinationFamilyCode = $this->getValueFromRoot('destinationFamily');

        $sourceFamily = $this->getFamilyByCode($sourceFamilyCode);
        $destinationFamily = $this->getFamilyByCode($destinationFamilyCode);

        if (!$sourceFamily || !$destinationFamily) {
            return;
        }

        if ($sourceFamily->getId() === $destinationFamily->getId()) {
            $this->context->buildViolation($constraint->message)
                ->addViolation();
        }
    }

    private function getValueFromRoot(string $code): string
    {
        $root = $this->context->getRoot();

        return $root[$code] ?? '';
    }

    private function getFamilyByCode(string $code): ?FamilyInterface
    {
        return $this->familyRepository->findOneBy(['code' => $code]);
    }
}
