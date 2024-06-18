<?php

declare(strict_types=1);

/**
 * Copyright (c) 2020, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

namespace PcmtPermissionsBundle\Tests\Service;

use Akeneo\UserManagement\Bundle\Doctrine\ORM\Repository\GroupRepository;
use PcmtPermissionsBundle\Entity\CategoryWithAccess;
use PcmtPermissionsBundle\Service\CategoryPermissionsDefaultProvider;
use PcmtPermissionsBundle\Tests\TestDataBuilder\CategoryWithAccessBuilder;
use PcmtPermissionsBundle\Tests\TestDataBuilder\UserGroupBuilder;
use PcmtSharedBundle\Service\Checker\CategoryPermissionsCheckerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class CategoryPermissionsDefaultProviderTest extends TestCase
{
    /** @var GroupRepository|MockObject */
    private $userGroupRepositoryMock;

    protected function setUp(): void
    {
        $this->userGroupRepositoryMock = $this->createMock(GroupRepository::class);
        $this->userGroupRepositoryMock->method('getDefaultUserGroup')->willReturn(
            (new UserGroupBuilder())->build()
        );
    }

    private function getCategoryPermissionsDefaultProviderInstance(): CategoryPermissionsDefaultProvider
    {
        return new CategoryPermissionsDefaultProvider($this->userGroupRepositoryMock);
    }

    public function dataRemove(): array
    {
        $userGroupAll = (new UserGroupBuilder())->build();
        $userGroupDifferent = (new UserGroupBuilder())->withName('Other name')->build();

        return [
            [
                (new CategoryWithAccessBuilder())
                    ->withAccesses([])
                    ->build(),
                0,
            ],
            [
                (new CategoryWithAccessBuilder())
                    ->withAccessesForGroup([CategoryPermissionsCheckerInterface::VIEW_LEVEL], $userGroupAll)
                    ->build(),
                0,
            ],
            [
                (new CategoryWithAccessBuilder())
                    ->withAccessesForGroup([CategoryPermissionsCheckerInterface::VIEW_LEVEL], $userGroupDifferent)
                    ->build(),
                1,
            ],
        ];
    }

    /**
     * @dataProvider dataRemove
     */
    public function testRemove(CategoryWithAccess $category, int $expectedCount): void
    {
        $provider = $this->getCategoryPermissionsDefaultProviderInstance();
        $provider->remove($category);

        $this->assertEquals($expectedCount, count($category->getAccesses()));
    }

    public function dataFill(): array
    {
        $userGroupDifferent1 = (new UserGroupBuilder())->withName('Other name')->build();
        $userGroupDifferent2 = (new UserGroupBuilder())->withName('Other name 2')->build();

        return [
            'default accesses' => [
                (new CategoryWithAccessBuilder())->build(),
                3,
            ],
            'empty accesses' => [
                (new CategoryWithAccessBuilder())->withAccesses([])->build(),
                3,
            ],
            'existing accesses' => [
                (new CategoryWithAccessBuilder())
                    ->clearAccesses()
                    ->withAccessesForGroups([CategoryPermissionsCheckerInterface::VIEW_LEVEL], [$userGroupDifferent1, $userGroupDifferent2])
                    ->build(),
                4,
            ],
        ];
    }

    /**
     * @dataProvider dataFill
     */
    public function testFill(CategoryWithAccess $category, int $expectedCount): void
    {
        $provider = $this->getCategoryPermissionsDefaultProviderInstance();
        $provider->fill($category);

        $this->assertEquals($expectedCount, count($category->getAccesses()));
    }
}