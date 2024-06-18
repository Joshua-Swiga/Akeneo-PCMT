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

use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Pim\Structure\Component\Model\FamilyInterface;
use Akeneo\Pim\Structure\Component\Repository\AttributeRepositoryInterface;
use Akeneo\Pim\Structure\Component\Repository\FamilyRepositoryInterface;
use PcmtRulesBundle\Constraints\AttributeExistsInBothFamiliesConstraint;
use PcmtRulesBundle\Service\RuleAttributeProvider;
use Symfony\Component\Form\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class AttributeExistsInBothFamiliesConstraintValidator extends ConstraintValidator
{
    /** @var RuleAttributeProvider */
    private $ruleAttributeProvider;

    /** @var FamilyRepositoryInterface */
    private $familyRepository;

    /** @var AttributeRepositoryInterface */
    private $attributeRepository;

    public function __construct(
        RuleAttributeProvider $ruleAttributeProvider,
        FamilyRepositoryInterface $familyRepository,
        AttributeRepositoryInterface $attributeRepository
    ) {
        $this->ruleAttributeProvider = $ruleAttributeProvider;
        $this->familyRepository = $familyRepository;
        $this->attributeRepository = $attributeRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function validate($entity, Constraint $constraint): void
    {
        if (!$constraint instanceof AttributeExistsInBothFamiliesConstraint) {
            throw new UnexpectedTypeException($constraint, AttributeExistsInBothFamiliesConstraint::class);
        }

        $sourceFamilyCode = $this->getValueFromRoot('sourceFamily');
        $destinationFamilyCode = $this->getValueFromRoot('destinationFamily');
        $keyAttributeCode = $this->getValueFromRoot('keyAttribute');

        $sourceFamily = $this->getFamilyByCode($sourceFamilyCode);
        $destinationFamily = $this->getFamilyByCode($destinationFamilyCode);
        $keyAttribute = $this->getAttributeByCode($keyAttributeCode);

        if (!$sourceFamily || !$destinationFamily || !$keyAttribute) {
            return;
        }

        $attributes = $this->ruleAttributeProvider->getPossibleForKeyAttribute($sourceFamily, $destinationFamily);
        foreach ($attributes as $attribute) {
            /** @var AttributeInterface $attribute */
            if ($keyAttribute->getCode() === $attribute->getCode()) {
                return;
            }
        }

        $this->context->buildViolation($constraint->message)
            ->atPath('key_attribute')
            ->addViolation();
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

    private function getAttributeByCode(string $code): ?AttributeInterface
    {
        return $this->attributeRepository->findOneBy(['code' => $code]);
    }
}
