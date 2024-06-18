<?php

declare(strict_types=1);

/**
 * Copyright (c) 2020, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

namespace PcmtPermissionsBundle\Tests\Updater;

use Akeneo\Tool\Component\StorageUtils\Saver\SaverInterface;
use PcmtPermissionsBundle\Entity\CategoryWithAccess;
use PcmtPermissionsBundle\Tests\TestDataBuilder\CategoryWithAccessBuilder;
use PcmtPermissionsBundle\Tests\TestDataBuilder\UserGroupBuilder;
use PcmtPermissionsBundle\Updater\CategoryChildrenPermissionsUpdater;
use PcmtSharedBundle\Service\Checker\CategoryPermissionsCheckerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class CategoryChildrenPermissionUpdaterTest extends TestCase
{
    /** @var SaverInterface|MockObject */
    private $categoryWithAccessSaverMock;

    /** @var CategoryChildrenPermissionsUpdater */
    private $updater;

    protected function setUp(): void
    {
        $this->categoryWithAccessSaverMock = $this->createMock(SaverInterface::class);

        $this->updater = new CategoryChildrenPermissionsUpdater($this->categoryWithAccessSaverMock);
    }

    public function dataUpdate(): array
    {
        $categoryWithAccess = (new CategoryWithAccessBuilder())->withTwoChildren()->build();

        return [
            'two children, same access' => [
                (new CategoryWithAccessBuilder())->withTwoChildren()->build(),
                0,
            ],
            'grand children, no access' => [
                (new CategoryWithAccessBuilder())->withChildren([$categoryWithAccess])->build(),
                0,
            ],
            'children, different access' => [
                (new CategoryWithAccessBuilder())
                    ->withTwoChildren()
                    ->withAccessesForGroup([CategoryPermissionsCheckerInterface::EDIT_LEVEL], (new UserGroupBuilder())->build())
                    ->build(),
                2,
            ],
            'one child, two grand, different access' => [
                (new CategoryWithAccessBuilder())
                    ->withChildren([$categoryWithAccess])
                    ->withAccessesForGroup([CategoryPermissionsCheckerInterface::EDIT_LEVEL], (new UserGroupBuilder())->build())
                    ->build(),
                3,
            ],
        ];
    }

    /**
     * @dataProvider dataUpdate
     */
    public function testUpdate(CategoryWithAccess $categoryWithAccess, int $expectedSaves): void
    {
        $this->categoryWithAccessSaverMock->expects($this->exactly($expectedSaves))->method('save');

        $this->updater->update($categoryWithAccess);
    }
}