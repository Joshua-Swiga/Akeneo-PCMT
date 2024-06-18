<?php
/**
 * Copyright (c) 2019, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */
declare(strict_types=1);

namespace PcmtCoreBundle\Tests\Service\E2Open;

use Akeneo\Pim\Structure\Bundle\Doctrine\ORM\Repository\FamilyRepository;
use Akeneo\Pim\Structure\Component\Model\Family;
use Akeneo\Pim\Structure\Component\Repository\FamilyRepositoryInterface;
use Doctrine\ORM\EntityManagerInterface;
use PcmtCoreBundle\Entity\Attribute;
use PcmtCoreBundle\Exception\Mapping\AttributeNotInFamilyException;
use PcmtCoreBundle\Repository\AttributeMappingRepository;
use PcmtCoreBundle\Service\E2Open\E2OpenMappingHandler;
use PcmtCoreBundle\Service\Handler\AttributeMappingHandlerInterface;
use PHPUnit\Framework\MockObject\MockObject as Mock;
use PHPUnit\Framework\TestCase;

class E2OpenMappingHandlerTest extends TestCase
{
    /** @var EntityManagerInterface|Mock */
    private $entityManagerMock;

    /** @var FamilyRepositoryInterface|Mock */
    private $familyRepositoryMock;

    /** @var AttributeMappingRepository|Mock */
    private $attributeMappingRepositoryMock;

    protected function setUp(): void
    {
        $this->entityManagerMock = $this->createMock(EntityManagerInterface::class);
        $this->familyRepositoryMock = $this->createMock(FamilyRepository::class);
        $this->attributeMappingRepositoryMock = $this->createMock(AttributeMappingRepository::class);
    }

    public function testFailsIfAttributeDoesNotBelongToExpectedDFamily(): void
    {
        $mappingAttribute = $this->createMock(Attribute::class);
        $mappedAttribute = $this->createMock(Attribute::class);
        $familyMock = $this->createMock(Family::class);
        $e2OpenMappingHandler = $this->getE2OpenMappingHandlerInstance();
        $this->familyRepositoryMock->expects($this->once())
            ->method('findOneBy')
            ->with(['code' => E2OpenMappingHandler::FAMILY_CODE])
            ->willReturn($familyMock);

        $familyMock->expects($this->once())
            ->method('hasAttribute')
            ->willReturn(false);

        $this->expectException(AttributeNotInFamilyException::class);

        $e2OpenMappingHandler->createMapping($mappingAttribute, $mappedAttribute);
    }

    public function testCreatesMapping(): void
    {
        $mappingAttribute = $this->createMock(Attribute::class);
        $mappedAttribute = $this->createMock(Attribute::class);
        $familyMock = $this->createMock(Family::class);
        $e2OpenMappingHandler = $this->getE2OpenMappingHandlerInstance();
        $this->familyRepositoryMock->expects($this->once())
            ->method('findOneBy')
            ->with(['code' => E2OpenMappingHandler::FAMILY_CODE])
            ->willReturn($familyMock);

        $familyMock->expects($this->once())
            ->method('hasAttribute')
            ->willReturn(true);

        $this->attributeMappingRepositoryMock->expects($this->once())
            ->method('findOneBy')
            ->willReturn(null);

        $this->entityManagerMock->expects($this->once())
            ->method('persist');

        $e2OpenMappingHandler->createMapping($mappingAttribute, $mappedAttribute);
    }

    public function getE2OpenMappingHandlerInstance(): AttributeMappingHandlerInterface
    {
        return new E2OpenMappingHandler(
            $this->entityManagerMock,
            $this->familyRepositoryMock,
            $this->attributeMappingRepositoryMock
        );
    }
}
