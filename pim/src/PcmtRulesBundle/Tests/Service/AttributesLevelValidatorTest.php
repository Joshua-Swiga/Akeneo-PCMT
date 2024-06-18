<?php

declare(strict_types=1);

/*
 * Copyright (c) 2021, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

namespace PcmtRulesBundle\Tests\Service;

use Akeneo\Pim\Enrichment\Component\Product\Model\EntityWithFamilyInterface;
use PcmtRulesBundle\Service\AttributesLevelValidator;
use PcmtRulesBundle\Tests\TestDataBuilder\AttributeBuilder;
use PcmtRulesBundle\Tests\TestDataBuilder\FamilyVariantBuilder;
use PcmtRulesBundle\Tests\TestDataBuilder\ProductBuilder;
use PcmtRulesBundle\Tests\TestDataBuilder\ProductModelBuilder;
use PcmtRulesBundle\Tests\TestDataBuilder\VariantAttributeSetBuilder;
use PHPUnit\Framework\TestCase;

class AttributesLevelValidatorTest extends TestCase
{
    public function dataValidate(): array
    {
        $attribute = (new AttributeBuilder())->build();
        $productSimple = (new ProductBuilder())->build();
        $variantAttributeSet = (new VariantAttributeSetBuilder())->withLevel(1)->addAttribute($attribute)->build();
        $familyVariant = (new FamilyVariantBuilder())->withVariantAttributeSet($variantAttributeSet)->build();
        $productModel = (new ProductModelBuilder())->withFamilyVariant($familyVariant)->build();
        $productVariant = (new ProductBuilder())->withParent($productModel)->withFamilyVariant($familyVariant)->build();

        return [
            [$productSimple, [], true],
            [$productSimple, ['code1', 'code2'], true],
            [$productVariant, [], true],
            [$productVariant, ['code1', 'code2'], false],
            [$productModel, ['code1', 'code2'], false],
        ];
    }

    /** @dataProvider dataValidate */
    public function testValidate(
        EntityWithFamilyInterface $entity,
        array $attributeCodes,
        bool $expectedResult
    ): void {
        $validator = $this->getValidatorInstance();
        $this->assertEquals($expectedResult, $validator->validate($entity, $attributeCodes));
    }

    private function getValidatorInstance(): AttributesLevelValidator
    {
        return new AttributesLevelValidator();
    }
}