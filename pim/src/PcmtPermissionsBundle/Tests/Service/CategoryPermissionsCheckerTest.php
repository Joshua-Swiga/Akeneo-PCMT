<?php
/**
 * Copyright (c) 2020, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

declare(strict_types=1);

namespace PcmtPermissionsBundle\Tests\Service;

use Akeneo\Tool\Component\Classification\CategoryAwareInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use PcmtPermissionsBundle\Service\CategoryPermissionsChecker;
use PcmtPermissionsBundle\Tests\TestDataBuilder\CategoryWithAccessBuilder;
use PcmtPermissionsBundle\Tests\TestDataBuilder\ProductBuilder;
use PcmtPermissionsBundle\Tests\TestDataBuilder\UserBuilder;
use PcmtPermissionsBundle\Tests\TestDataBuilder\UserGroupBuilder;
use PcmtSharedBundle\Service\Checker\CategoryPermissionsCheckerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\Exception\ParameterNotFoundException;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class CategoryPermissionsCheckerTest extends TestCase
{
    /** @var TokenStorageInterface|MockObject */
    private $tokenStorageMock;

    /** @var CategoryPermissionsCheckerInterface */
    private $categoryPermissionsChecker;

    /** @var string[] */
    private $accessesToTest = [];

    protected function setUp(): void
    {
        $this->accessesToTest = CategoryPermissionsCheckerInterface::ALL_LEVELS;

        $this->tokenStorageMock = $this->createMock(TokenStorageInterface::class);

        $this->categoryPermissionsChecker = new CategoryPermissionsChecker($this->tokenStorageMock);
    }

    public function testExceptionIsThrownWhenCheckingUnhandledAccessRights(): void
    {
        $user = (new UserBuilder())->build();
        $productCategoriesCollection = new ArrayCollection();
        $productCategoriesCollection->add((new CategoryWithAccessBuilder())->build());

        $entityMock = $this->createMock(CategoryAwareInterface::class);
        $entityMock->method('getCategories')->willReturn($productCategoriesCollection);

        $this->expectException(ParameterNotFoundException::class);
        $this->categoryPermissionsChecker->hasAccessToProduct('unhandle_sad_type', $entityMock, $user);
    }

    public function testUserIsTakenFromTokenStorageWhenCheckingAccessRights(): void
    {
        $user = (new UserBuilder())->build();

        $tokenMock = $this->createMock(TokenInterface::class);
        $tokenMock
            ->expects($this->once())
            ->method('getUser')
            ->willReturn($user);
        $this->tokenStorageMock
            ->expects($this->once())
            ->method('getToken')
            ->willReturn($tokenMock);

        $category = (new CategoryWithAccessBuilder())->withAccessesForGroup(
            CategoryPermissionsCheckerInterface::ALL_LEVELS,
            (new UserGroupBuilder())->build()
        )->build();
        $entity = (new ProductBuilder())->addCategory($category)->build();

        $this->categoryPermissionsChecker->hasAccessToProduct(CategoryPermissionsCheckerInterface::VIEW_LEVEL, $entity);
    }

    /**
     * @dataProvider dataHasAlwaysAccessToProduct
     */
    public function testHasAlwaysAccessToProduct(
        ?ArrayCollection $productCategories
    ): void {
        $entityMock = $this->createMock(CategoryAwareInterface::class);
        $entityMock->method('getCategories')->willReturn($productCategories);
        $user = (new UserBuilder())->build();

        foreach ($this->accessesToTest as $accessType) {
            $result = $this->categoryPermissionsChecker->hasAccessToProduct($accessType, $entityMock, $user);
            $this->assertTrue($result);
        }
    }

    public function dataHasAlwaysAccessToProduct(): array
    {
        $productCategoriesCollection = new ArrayCollection();
        $productCategoriesCollection->add((new CategoryWithAccessBuilder())->clearAccesses()->build());

        return [
            'product without categories' => [
                new ArrayCollection(),
            ],
            'category without set accesses' => [
                $productCategoriesCollection,
            ],
        ];
    }

    /**
     * @dataProvider dataHasAccessToProductForSpecificUser
     */
    public function testHasAccessToProductForSpecificUser(
        ?Collection $categoryCollection,
        array $accessTypesWithPermissions
    ): void {
        $entityMock = $this->createMock(CategoryAwareInterface::class);
        $entityMock->method('getCategories')->willReturn($categoryCollection);
        $user = (new UserBuilder())->build();

        foreach ($this->accessesToTest as $accessType) {
            $result = $this->categoryPermissionsChecker->hasAccessToProduct($accessType, $entityMock, $user);
            $expectedResult = in_array($accessType, $accessTypesWithPermissions);
            $this->assertSame($expectedResult, $result);
        }
    }

    public function dataHasAccessToProductForSpecificUser(): array
    {
        $productCategoriesCollection = new ArrayCollection();
        $productCategoriesCollection->add((new CategoryWithAccessBuilder())->clearAccesses()->build());

        $categoryWithViewAndOwnRights = (new CategoryWithAccessBuilder())
            ->clearAccesses()
            ->withAccessesForGroup(
                [
                    CategoryPermissionsCheckerInterface::EDIT_LEVEL,
                ],
                (new UserGroupBuilder())->buildWithAnotherId()
            )
            ->withAccessesForGroup(
                [
                    CategoryPermissionsCheckerInterface::VIEW_LEVEL,
                    CategoryPermissionsCheckerInterface::OWN_LEVEL,
                ],
                (new UserGroupBuilder())->build()
            )
            ->build();

        $categoryWithOwnRights = (new CategoryWithAccessBuilder())
            ->clearAccesses()
            ->withAccessesForGroup(
                [
                    CategoryPermissionsCheckerInterface::EDIT_LEVEL,
                ],
                (new UserGroupBuilder())->buildWithAnotherId()
            )
            ->withAccessesForGroup(
                [
                    CategoryPermissionsCheckerInterface::OWN_LEVEL,
                ],
                (new UserGroupBuilder())->build()
            )
            ->build();

        $categoryWithViewAndEditRights = (new CategoryWithAccessBuilder())
            ->clearAccesses()
            ->withAccessesForGroup(
                [
                    CategoryPermissionsCheckerInterface::EDIT_LEVEL,
                    CategoryPermissionsCheckerInterface::OWN_LEVEL,
                ],
                (new UserGroupBuilder())->buildWithAnotherId()
            )
            ->withAccessesForGroup(
                [
                    CategoryPermissionsCheckerInterface::VIEW_LEVEL,
                    CategoryPermissionsCheckerInterface::EDIT_LEVEL,
                ],
                (new UserGroupBuilder())->build()
            )
            ->build();

        $categoryWithEditRights = (new CategoryWithAccessBuilder())
            ->clearAccesses()
            ->withAccessesForGroup(
                [
                    CategoryPermissionsCheckerInterface::VIEW_LEVEL,
                    CategoryPermissionsCheckerInterface::EDIT_LEVEL,
                    CategoryPermissionsCheckerInterface::OWN_LEVEL,
                ],
                (new UserGroupBuilder())->buildWithAnotherId()
            )
            ->withAccessesForGroup(
                [
                    CategoryPermissionsCheckerInterface::EDIT_LEVEL,
                ],
                (new UserGroupBuilder())->build()
            )
            ->build();

        $categoryWithViewRights = (new CategoryWithAccessBuilder())
            ->clearAccesses()
            ->withAccessesForGroup(
                [
                    CategoryPermissionsCheckerInterface::VIEW_LEVEL,
                    CategoryPermissionsCheckerInterface::EDIT_LEVEL,
                    CategoryPermissionsCheckerInterface::OWN_LEVEL,
                ],
                (new UserGroupBuilder())->buildWithAnotherId()
            )
            ->withAccessesForGroup(
                [
                    CategoryPermissionsCheckerInterface::VIEW_LEVEL,
                ],
                (new UserGroupBuilder())->build()
            )
            ->build();

        $categoryWithAllRightsForOtherGroup = (new CategoryWithAccessBuilder())
            ->clearAccesses()
            ->withAccessesForGroup(
                [
                    CategoryPermissionsCheckerInterface::VIEW_LEVEL,
                    CategoryPermissionsCheckerInterface::EDIT_LEVEL,
                    CategoryPermissionsCheckerInterface::OWN_LEVEL,
                ],
                (new UserGroupBuilder())->buildWithAnotherId()
            )
            ->build();

        $categoryWithEditAndOwnRightsForOtherGroup = (new CategoryWithAccessBuilder())
            ->clearAccesses()
            ->withAccessesForGroup(
                [
                    CategoryPermissionsCheckerInterface::EDIT_LEVEL,
                    CategoryPermissionsCheckerInterface::OWN_LEVEL,
                ],
                (new UserGroupBuilder())->buildWithAnotherId()
            )
            ->build();

        $categoryWithViewRightsForOtherGroup = (new CategoryWithAccessBuilder())
            ->clearAccesses()
            ->withAccessesForGroup(
                [
                    CategoryPermissionsCheckerInterface::VIEW_LEVEL,
                ],
                (new UserGroupBuilder())->buildWithAnotherId()
            )
            ->build();

        return [
            'View and Own rights' => [
                new ArrayCollection([$categoryWithViewAndOwnRights]),
                [
                    CategoryPermissionsCheckerInterface::VIEW_LEVEL,
                    CategoryPermissionsCheckerInterface::EDIT_LEVEL,
                    CategoryPermissionsCheckerInterface::OWN_LEVEL,
                ],
            ],
            'Own rights' => [
                new ArrayCollection([$categoryWithOwnRights]),
                [
                    CategoryPermissionsCheckerInterface::VIEW_LEVEL,
                    CategoryPermissionsCheckerInterface::EDIT_LEVEL,
                    CategoryPermissionsCheckerInterface::OWN_LEVEL,
                ],
            ],
            'View and Edit rights' => [
                new ArrayCollection([$categoryWithViewAndEditRights]),
                [
                    CategoryPermissionsCheckerInterface::VIEW_LEVEL,
                    CategoryPermissionsCheckerInterface::EDIT_LEVEL,
                ],
            ],
            'Edit rights' => [
                new ArrayCollection([$categoryWithEditRights]),
                [
                    CategoryPermissionsCheckerInterface::VIEW_LEVEL,
                    CategoryPermissionsCheckerInterface::EDIT_LEVEL,
                ],
            ],
            'View rights' => [
                new ArrayCollection([$categoryWithViewRights]),
                [
                    CategoryPermissionsCheckerInterface::VIEW_LEVEL,
                ],
            ],
            'All rights for other group' => [
                new ArrayCollection([$categoryWithAllRightsForOtherGroup]),
                [],
            ],
            'Edit and own rights for other group' => [
                new ArrayCollection([$categoryWithEditAndOwnRightsForOtherGroup]),
                [
                    CategoryPermissionsCheckerInterface::VIEW_LEVEL,
                ],
            ],
            'View rights for other group' => [
                new ArrayCollection([$categoryWithViewRightsForOtherGroup]),
                [
                    CategoryPermissionsCheckerInterface::VIEW_LEVEL,
                    CategoryPermissionsCheckerInterface::EDIT_LEVEL,
                    CategoryPermissionsCheckerInterface::OWN_LEVEL,
                ],
            ],
        ];
    }
}