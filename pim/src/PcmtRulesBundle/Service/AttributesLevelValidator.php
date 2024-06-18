<?php

declare(strict_types=1);

/*
 * Copyright (c) 2021, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

namespace PcmtRulesBundle\Service;

use Akeneo\Pim\Enrichment\Component\Product\Model\EntityWithFamilyInterface;

/**
 * checks if all the attributes are on the same level
 */
class AttributesLevelValidator
{
    public function validate(EntityWithFamilyInterface $entity, array $attributesToCheckCodes): bool
    {
        if (!$entity->getFamilyVariant()) {
            return true;
        }

        $variationLevel = $entity->getVariationLevel();
        $attributes = 0 === $variationLevel ?
            $entity->getFamilyVariant()->getCommonAttributes() :
            $entity->getFamilyVariant()->getVariantAttributeSet($variationLevel)->getAttributes();

        $codes = [];
        foreach ($attributes as $attribute) {
            $codes[] = $attribute->getCode();
        }

        foreach ($attributesToCheckCodes as $attributeCode) {
            if (!in_array($attributeCode, $codes)) {
                return false;
            }
        }

        return true;
    }
}
