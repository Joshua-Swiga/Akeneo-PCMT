<?php

declare(strict_types=1);

/*
 * Copyright (c) 2021, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

namespace PcmtRulesBundle\Service;

use Akeneo\Pim\Structure\Component\Model\FamilyInterface;
use PcmtRulesBundle\Value\AttributeMapping;
use PcmtRulesBundle\Value\AttributeMappingCollection;

class AttributeMappingGenerator
{
    /** @var RuleAttributeProvider */
    private $ruleAttributeProvider;

    public function __construct(RuleAttributeProvider $ruleAttributeProvider)
    {
        $this->ruleAttributeProvider = $ruleAttributeProvider;
    }

    public function get(FamilyInterface $sourceFamily, FamilyInterface $destinationFamily, array $definedMappings): AttributeMappingCollection
    {
        $mappings = new AttributeMappingCollection();

        foreach ($this->ruleAttributeProvider->getAllForFamilies($sourceFamily, $destinationFamily) as $attribute) {
            $mappings->add(new AttributeMapping($attribute, $attribute));
        }

        foreach ($definedMappings as $mapping) {
            $sourceAttribute = $this->ruleAttributeProvider->getAttributeByCode($mapping['sourceValue']);
            $destinationAttribute = $this->ruleAttributeProvider->getAttributeByCode($mapping['destinationValue']);

            if (null !== $sourceAttribute && null !== $destinationAttribute) {
                $mappings->add(new AttributeMapping(
                    $sourceAttribute,
                    $destinationAttribute
                ));
            }
        }

        return $mappings;
    }

    public function getKeyAttributesMapping(string $sourceKeyAttributeCode, string $destinationKeyAttributeCode): AttributeMapping
    {
        return new AttributeMapping(
            $this->ruleAttributeProvider->getAttributeByCode($sourceKeyAttributeCode),
            $this->ruleAttributeProvider->getAttributeByCode($destinationKeyAttributeCode)
        );
    }
}