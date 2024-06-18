<?php

declare(strict_types=1);

/*
 * Copyright (c) 2020, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

namespace PcmtRulesBundle\Tests\Service;

use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Pim\Structure\Component\Model\Family;
use Akeneo\Pim\Structure\Component\Model\FamilyInterface;
use Akeneo\Pim\Structure\Component\Repository\AttributeRepositoryInterface;
use PcmtRulesBundle\Service\RuleAttributeProvider;
use PcmtRulesBundle\Tests\TestDataBuilder\AttributeBuilder;
use PcmtRulesBundle\Tests\TestDataBuilder\FamilyBuilder;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class RuleAttributeProviderTest extends TestCase
{
    /** @var AttributeRepositoryInterface|MockObject */
    private $attributeRepositoryMock;

    protected function setUp(): void
    {
        $this->attributeRepositoryMock = $this->createMock(AttributeRepositoryInterface::class);
    }

    public function dataGetAllForFamilies(): array
    {
        $attribute1 = (new AttributeBuilder())->withType(AttributeTypes::IDENTIFIER)->withCode('A1')->build();
        $attribute2 = (new AttributeBuilder())->withType(AttributeTypes::TEXT)->withCode('A2')->build();
        $attribute3 = (new AttributeBuilder())->withType(AttributeTypes::BOOLEAN)->withCode('A3')->build();
        $attribute4 = (new AttributeBuilder())->withType(AttributeTypes::OPTION_SIMPLE_SELECT)->withCode('A4')->build();
        $attributeUnique = (new AttributeBuilder())->withType(AttributeTypes::TEXT)->asUnique()->withCode('A5')->build();
        $sourceFamily = (new FamilyBuilder())->withCode('XXX')->build();
        $destinationFamily = (new FamilyBuilder())->withCode('YYY')->build();

        $map1 = [
            [$sourceFamily, [$attribute1]],
            [$destinationFamily, [$attribute1, $attribute2]],
        ];
        $expectedAttributes1 = [];

        $map2 = [
            [$sourceFamily, [$attribute1, $attribute3, $attribute4, $attributeUnique]],
            [$destinationFamily, [$attribute1, $attribute2, $attribute3, $attribute4, $attributeUnique]],
        ];
        $expectedAttributes2 = [$attribute3, $attribute4];

        return [
            [$sourceFamily, $destinationFamily, $map1, $expectedAttributes1],
            [$sourceFamily, $destinationFamily, $map2, $expectedAttributes2],
        ];
    }

    public function dataGetPossibleForKeyAttribute(): array
    {
        $attribute1 = (new AttributeBuilder())->withType(AttributeTypes::IDENTIFIER)->withCode('A1')->build();
        $attribute2 = (new AttributeBuilder())->withType(AttributeTypes::TEXT)->withCode('A2')->build();
        $attribute3 = (new AttributeBuilder())->withType(AttributeTypes::BOOLEAN)->withCode('A3')->build();
        $attribute4 = (new AttributeBuilder())->withType(AttributeTypes::OPTION_SIMPLE_SELECT)->withCode('A4')->build();
        $attribute5 = (new AttributeBuilder())->withType(AttributeTypes::TEXT)->asScopable()->withCode('A5')->build();
        $attributeUnique = (new AttributeBuilder())->withType(AttributeTypes::TEXT)->asUnique()->withCode('A6')->build();
        $sourceFamily = (new FamilyBuilder())->withCode('XXX')->build();
        $destinationFamily = (new FamilyBuilder())->withCode('YYY')->build();

        $map1 = [
            [$sourceFamily, [$attribute1, $attributeUnique]],
            [$destinationFamily, [$attribute1, $attribute2, $attributeUnique]],
        ];
        $expectedAttributes1 = [
            'sourceKeyAttributes'      => [],
            'destinationKeyAttributes' => [$attribute2],
        ];

        $map2 = [
            [$sourceFamily, [$attribute1, $attribute3, $attribute4, $attributeUnique]],
            [$destinationFamily, [$attribute1, $attribute2, $attribute3, $attribute4, $attributeUnique]],
        ];
        $expectedAttributes2 = [
            'sourceKeyAttributes'      => [$attribute4],
            'destinationKeyAttributes' => [$attribute2, $attribute4],
        ];

        $map3 = [
            [$sourceFamily, [$attribute1, $attribute5]],
            [$destinationFamily, [$attribute1, $attribute2, $attribute5]],
        ];
        $expectedAttributes3 = [
            'sourceKeyAttributes'      => [],
            'destinationKeyAttributes' => [$attribute2],
        ];

        return [
            [$sourceFamily, $destinationFamily, $map1, $expectedAttributes1],
            [$sourceFamily, $destinationFamily, $map2, $expectedAttributes2],
            [$sourceFamily, $destinationFamily, $map3, $expectedAttributes3],
        ];
    }

    /**
     * @dataProvider dataGetAllForFamilies
     */
    public function testGetAllForFamilies(Family $sourceFamily, Family $destinationFamily, array $valueMap, array $expectedAttributes): void
    {
        $this->attributeRepositoryMock->method('findAttributesByFamily')->will($this->returnValueMap($valueMap));
        $provider = $this->getRuleAttributeProviderInstance();
        $attributes = $provider->getAllForFamilies($sourceFamily, $destinationFamily);
        $this->assertEquals($expectedAttributes, $attributes);
    }

    /**
     * @dataProvider dataGetPossibleForKeyAttribute
     */
    public function testGetPossibleForKeyAttribute(Family $sourceFamily, Family $destinationFamily, array $valueMap, array $expectedAttributes): void
    {
        $this->attributeRepositoryMock->method('findAttributesByFamily')->will($this->returnValueMap($valueMap));
        $provider = $this->getRuleAttributeProviderInstance();
        $attributes = $provider->getPossibleForKeyAttribute($sourceFamily, $destinationFamily);
        $this->assertEquals($expectedAttributes, $attributes);
    }

    public function dataGetForOptions(): array
    {
        $type1 = 'example_type';
        $type2 = 'example_type_2';
        $validationRule = 'url';
        $attributes = [
            (new AttributeBuilder())->withType($type1)->build(),
            (new AttributeBuilder())->withType($type1)->withValidationRule($validationRule)->build(),
            (new AttributeBuilder())->withType($type2)->build(),
        ];

        return [
            [[$type1], $validationRule, $attributes, 1],
            [[$type2], $validationRule, $attributes, 0],
            [[$type1], null, $attributes, 2],
            [[$type2], null, $attributes, 1],
            [[], null, $attributes, 3],
            [[$type1, $type2], null, $attributes, 3],
        ];
    }

    /** @dataProvider dataGetForOptions */
    public function testGetForOptions(array $types, ?string $validationRule, array $attributes, int $expectedCount): void
    {
        $family = (new FamilyBuilder())->build();
        $this->attributeRepositoryMock
            ->expects($this->once())
            ->method('findAttributesByFamily')
            ->willReturn($attributes);

        $resultAttributes = $this->getRuleAttributeProviderInstance()->getForOptions($family, $types, $validationRule);

        $this->assertCount($expectedCount, $resultAttributes);
    }

    public function testGetAttributeByCode(): void
    {
        $code = 'CODE';
        $attribute = (new AttributeBuilder())->withCode($code)->build();

        $this->attributeRepositoryMock
            ->expects($this->once())
            ->method('findOneBy')
            ->with(['code' => $code])
            ->willReturn($attribute);

        $provider = $this->getRuleAttributeProviderInstance();

        $result = $provider->getAttributeByCode($code);

        $this->assertEquals($attribute->getCode(), $result->getCode());
    }

    public function dataGetForF2FAttributeMapping(): array
    {
        $attribute1 = (new AttributeBuilder())->withCode('a1')->build();
        $attribute2 = (new AttributeBuilder())->withCode('a2')->build();
        $attribute3 = (new AttributeBuilder())->withCode('a3')->build();
        $attribute4 = (new AttributeBuilder())->withCode('a4')->build();
        $sourceFamily = (new FamilyBuilder())->build();
        $sourceFamily->addAttribute($attribute1);
        $sourceFamily->addAttribute($attribute2);
        $destinationFamily = (new FamilyBuilder())->build();
        $destinationFamily->addAttribute($attribute2);
        $destinationFamily->addAttribute($attribute3);
        $destinationFamily->addAttribute($attribute4);
        $result = [[$attribute1], [$attribute3, $attribute4]];

        return [
            [$sourceFamily, $destinationFamily, $result],
        ];
    }

    /** @dataProvider dataGetForF2FAttributeMapping */
    public function testGetForF2FAttributeMapping(
        ?FamilyInterface $sourceFamily,
        ?FamilyInterface $destinationFamily,
        array $expectedResult
    ): void {
        $provider = $this->getRuleAttributeProviderInstance();

        $result = $provider->getForF2FAttributeMapping($sourceFamily, $destinationFamily);

        $this->assertEquals($expectedResult, $result);
    }

    private function getRuleAttributeProviderInstance(): RuleAttributeProvider
    {
        return new RuleAttributeProvider($this->attributeRepositoryMock);
    }
}
