<?php

declare(strict_types=1);

/*
 * Copyright (c) 2020, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

namespace PcmtRulesBundle\Service;

use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Pim\Structure\Component\Model\FamilyInterface;
use Akeneo\Pim\Structure\Component\Repository\AttributeRepositoryInterface;

class RuleAttributeProvider
{
    /** @var AttributeRepositoryInterface */
    private $attributeRepository;

    public function __construct(AttributeRepositoryInterface $attributeRepository)
    {
        $this->attributeRepository = $attributeRepository;
    }

    private function getSupportedTypes(): array
    {
        return [
            AttributeTypes::TEXT,
            AttributeTypes::OPTION_SIMPLE_SELECT,
            AttributeTypes::REFERENCE_DATA_SIMPLE_SELECT,
        ];
    }

    private function filterForKeyAttribute(array $attributes): array
    {
        return array_values(array_filter($attributes, function (AttributeInterface $attribute) {
            if (!in_array($attribute->getType(), $this->getSupportedTypes())) {
                return false;
            }

            if ($attribute->isLocalizable() || $attribute->isScopable()) {
                return false;
            }

            if ($attribute->isUnique()) {
                return false;
            }

            return true;
        }));
    }

    private function filterAttributes(array $attributes): array
    {
        return array_values(array_filter($attributes, function (AttributeInterface $attribute) {
            if (AttributeTypes::IDENTIFIER === $attribute->getType()) {
                return false;
            }

            if ($attribute->isUnique()) {
                return false;
            }

            return true;
        }));
    }

    public function getAllForFamilies(FamilyInterface $sourceFamily, FamilyInterface $destinationFamily): array
    {
        $attributes1 = $this->attributeRepository->findAttributesByFamily($sourceFamily);
        $attributes1 = $this->filterAttributes($attributes1);
        $attributes2 = $this->attributeRepository->findAttributesByFamily($destinationFamily);
        $attributes2 = $this->filterAttributes($attributes2);

        return array_intersect($attributes1, $attributes2);
    }

    public function getPossibleForKeyAttribute(FamilyInterface $sourceFamily, FamilyInterface $destinationFamily): array
    {
        $attributes1 = $this->attributeRepository->findAttributesByFamily($sourceFamily);
        $attributes1 = $this->filterForKeyAttribute($attributes1);
        $attributes2 = $this->attributeRepository->findAttributesByFamily($destinationFamily);
        $attributes2 = $this->filterForKeyAttribute($attributes2);

        return [
            'sourceKeyAttributes'      => $attributes1,
            'destinationKeyAttributes' => $attributes2,
        ];
    }

    public function getForOptions(FamilyInterface $family, array $types = [], ?string $validationRule = null): array
    {
        $attributes = $this->attributeRepository->findAttributesByFamily($family);
        if ($types) {
            $attributes = array_filter($attributes, function (AttributeInterface $attribute) use ($types) {
                return in_array($attribute->getType(), $types);
            });
        }
        if ($validationRule) {
            $attributes = array_filter($attributes, function (AttributeInterface $attribute) use ($validationRule) {
                return $attribute->getValidationRule() === $validationRule;
            });
        }

        return $attributes;
    }

    public function getAttributeByCode(string $code): ?AttributeInterface
    {
        return $this->attributeRepository->findOneBy(['code' => $code]);
    }

    public function getForF2FAttributeMapping(?FamilyInterface $sourceFamily, ?FamilyInterface $destinationFamily): array
    {
        $sourceAttributeList = $sourceFamily ? $sourceFamily->getAttributes()->getValues() : [];
        $destinationAttributeList = $destinationFamily ? $destinationFamily->getAttributes()->getValues() : [];

        // removing those attributes that are in both families
        foreach ($sourceAttributeList as $sKey => $sourceAttribute) {
            foreach ($destinationAttributeList as $dKey => $destinationAttribute) {
                if ($sourceAttribute->getCode() === $destinationAttribute->getCode()) {
                    unset($sourceAttributeList[$sKey], $destinationAttributeList[$dKey]);
                }
            }
        }

        return [array_values($sourceAttributeList), array_values($destinationAttributeList)];
    }
}