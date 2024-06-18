<?php

declare(strict_types=1);

/*
 * Copyright (c) 2021, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

namespace PcmtRulesBundle\Tests\Service;

use PcmtRulesBundle\Service\AttributeMappingGenerator;
use PcmtRulesBundle\Service\RuleAttributeProvider;
use PcmtRulesBundle\Tests\TestDataBuilder\AttributeBuilder;
use PcmtRulesBundle\Tests\TestDataBuilder\FamilyBuilder;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class AttributeMappingGeneratorTest extends TestCase
{
    /** @var RuleAttributeProvider|MockObject */
    private $ruleAttributeProviderMock;

    protected function setUp(): void
    {
        $this->ruleAttributeProviderMock = $this->createMock(RuleAttributeProvider::class);
    }

    public function dataGet(): array
    {
        $attributes = [
            (new AttributeBuilder())->build(),
            (new AttributeBuilder())->build(),
        ];

        $mappings = [
            [
                'sourceValue'      => 'dsfsdfsd',
                'destinationValue' => 'asdasdsad',
            ],
            [
                'sourceValue'      => 'dsfsdfsd22',
                'destinationValue' => 'asdasdsadd',
            ],
            [
                'sourceValue'      => 'dsfsdfsd15',
                'destinationValue' => 'asdasdsad333',
            ],
        ];

        return [
            [
                [], [], 0,
            ],
            [
                $attributes, [], 2,
            ],
            [
                [], $mappings, 3,
            ],
            [
                $attributes, $mappings, 5,
            ],
        ];
    }

    /**
     * @dataProvider dataGet
     */
    public function testGet(array $commonAttributes, array $definedMappings, int $expectedCount): void
    {
        $sourceFamily = (new FamilyBuilder())->build();
        $destinationFamily = (new FamilyBuilder())->build();
        $this->ruleAttributeProviderMock
            ->expects($this->once())
            ->method('getAllForFamilies')
            ->with($sourceFamily, $destinationFamily)
            ->willReturn($commonAttributes);

        $this->ruleAttributeProviderMock->method('getAttributeByCode')->willReturn(
            (new AttributeBuilder())->build()
        );
        $generator = $this->getAttributeMappingGeneratorInstance();
        $collection = $generator->get($sourceFamily, $destinationFamily, $definedMappings);
        $this->assertEquals($expectedCount, $collection->count());
    }

    public function testGetKeyAttributesMapping(): void
    {
        $sourceKeyAttributeCode = 'test1';
        $destinationKeyAttributeCode = 'test2';

        $this->ruleAttributeProviderMock->method('getAttributeByCode')->willReturnOnConsecutiveCalls(
            (new AttributeBuilder())->withCode($sourceKeyAttributeCode)->build(),
            (new AttributeBuilder())->withCode($destinationKeyAttributeCode)->build()
        );

        $keyAttributeMapping = $this->getAttributeMappingGeneratorInstance()
            ->getKeyAttributesMapping(
                $sourceKeyAttributeCode,
                $destinationKeyAttributeCode
            );

        $this->assertEquals($sourceKeyAttributeCode, $keyAttributeMapping->getSourceAttribute()->getCode());
        $this->assertEquals($destinationKeyAttributeCode, $keyAttributeMapping->getDestinationAttribute()->getCode());
    }

    private function getAttributeMappingGeneratorInstance(): AttributeMappingGenerator
    {
        return new AttributeMappingGenerator(
            $this->ruleAttributeProviderMock
        );
    }
}