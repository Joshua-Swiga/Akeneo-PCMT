<?php

declare(strict_types=1);

/*
 * Copyright (c) 2020, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

namespace PcmtDraftBundle\Tests\Service\Draft;

use Doctrine\ORM\EntityManagerInterface;
use PcmtDraftBundle\Service\AttributeChange\AttributeChangeService;
use PcmtDraftBundle\Service\Draft\ChangesChecker;
use PcmtDraftBundle\Service\Draft\GeneralObjectFromDraftCreator;
use PcmtDraftBundle\Tests\TestDataBuilder\ExistingProductDraftBuilder;
use PcmtDraftBundle\Tests\TestDataBuilder\ProductBuilder;
use PcmtSharedBundle\Tests\TestDataBuilder\AssociationCollectionBuilder;
use PcmtSharedBundle\Tests\TestDataBuilder\AssociationTypeBuilder;
use PcmtSharedBundle\Tests\TestDataBuilder\ProductAssociationBuilder;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ChangesCheckerTest extends TestCase
{
    /** @var EntityManagerInterface|MockObject */
    private $entityManagerMock;

    /** @var GeneralObjectFromDraftCreator|MockObject */
    private $generalObjectFromDraftCreatorMock;

    /** @var AttributeChangeService|MockObject */
    private $attributeChangeServiceMock;

    protected function setUp(): void
    {
        $this->entityManagerMock = $this->createMock(EntityManagerInterface::class);
        $this->generalObjectFromDraftCreatorMock = $this->createMock(GeneralObjectFromDraftCreator::class);
        $this->attributeChangeServiceMock = $this->createMock(AttributeChangeService::class);
    }

    public function dataCheckIfChanges(): array
    {
        return [
            [['aaaa'], true],
            [[], false],
            [['ssss', 'fffff'], true],
        ];
    }

    /**
     * @dataProvider dataCheckIfChanges
     */
    public function testCheckIfChanges(array $changes, bool $expectedResult): void
    {
        $baseProduct = (new ProductBuilder())->withAssociations(
            (new AssociationCollectionBuilder())->withAssociation(
                (new ProductAssociationBuilder())->withType(
                    (new AssociationTypeBuilder())->withId(1)->build()
                )->build()
            )->build()
        )->build();
        $draft = (new ExistingProductDraftBuilder())->withProduct($baseProduct)->build();

        $this->attributeChangeServiceMock->method('get')->willReturn($changes);

        $checker = $this->getChangesChecker();
        $this->assertEquals($expectedResult, $checker->checkIfChanges($draft));
    }

    private function getChangesChecker(): ChangesChecker
    {
        return new ChangesChecker(
            $this->entityManagerMock,
            $this->generalObjectFromDraftCreatorMock,
            $this->attributeChangeServiceMock
        );
    }
}