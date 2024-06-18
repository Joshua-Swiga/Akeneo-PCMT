<?php
/**
 * Copyright (c) 2019, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */
declare(strict_types=1);

namespace PcmtCoreBundle\Tests\Service\E2Open;

use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Pim\Structure\Component\Model\FamilyInterface;
use Akeneo\Pim\Structure\Component\Repository\AttributeRepositoryInterface;
use Akeneo\Pim\Structure\Component\Repository\FamilyRepositoryInterface;
use PcmtCoreBundle\Service\E2Open\E2OpenAttributesService;
use PHPUnit\Framework\MockObject\MockObject as Mock;
use PHPUnit\Framework\TestCase;

class E2OpenAttributeServiceTest extends TestCase
{
    /** @var AttributeRepositoryInterface|Mock */
    private $attributeRepositoryMock;

    /** @var FamilyRepositoryInterface|Mock */
    private $familyRepositoryMock;

    /** @var mixed[] */
    private $measureConfig = [];

    private const MEASURE_UNIT_NAME = 'DEGREE CELSIUS';
    private const MEASURE_UNIT_SYMBOL = 'CEL';

    protected function setUp(): void
    {
        $this->attributeRepositoryMock = $this->createMock(AttributeRepositoryInterface::class);
        $this->familyRepositoryMock = $this->createMock(FamilyRepositoryInterface::class);
        $this->measureConfig = [
            'measures_config' => [
                E2OpenAttributesService::MEASURE_UNIT => [
                    'units' => [
                        self::MEASURE_UNIT_NAME => [
                            'symbol' => self::MEASURE_UNIT_SYMBOL,
                        ],
                    ],
                ],
            ],
        ];
    }

    public function testGetMeasureUnitForSymbol(): void
    {
        $attributeService = $this->getE2OpenAttributeServiceInstance();
        $this->assertSame(self::MEASURE_UNIT_NAME, $attributeService->getMeasureUnitForSymbol(self::MEASURE_UNIT_SYMBOL));
        $this->assertNull($attributeService->getMeasureUnitForSymbol(self::MEASURE_UNIT_SYMBOL . 'X'));
    }

    public function testGetForCode(): void
    {
        $code = 'xxx';

        $attribute = $this->createMock(AttributeInterface::class);
        $attribute->method('getCode')->willReturn($code);

        $family = $this->createMock(FamilyInterface::class);
        $expectedArgument = ['code' => E2OpenAttributesService::FAMILY_CODE];
        $this->familyRepositoryMock->expects($this->once())
            ->method('findOneBy')
            ->with($this->identicalTo($expectedArgument))
            ->willReturn($family);

        $this->attributeRepositoryMock->expects($this->once())
            ->method('findAttributesByFamily')
            ->with($this->identicalTo($family))
            ->willReturn([$attribute]);

        $attributeService = $this->getE2OpenAttributeServiceInstance();

        $this->assertSame($attribute, $attributeService->getForCode($code));
        $this->assertNull($attributeService->getForCode($code.'xx'));
    }

    public function getE2OpenAttributeServiceInstance(): E2OpenAttributesService
    {
        return new E2OpenAttributesService(
            $this->attributeRepositoryMock,
            $this->familyRepositoryMock,
            $this->measureConfig
        );
    }
}
