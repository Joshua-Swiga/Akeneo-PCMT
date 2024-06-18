<?php

declare(strict_types=1);

/**
 * Copyright (c) 2020, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

namespace PcmtPermissionsBundle\Tests\Service;

use Akeneo\Tool\Component\Classification\Repository\CategoryRepositoryInterface;
use PcmtPermissionsBundle\Service\CategoryPermissionsChecker;
use PcmtPermissionsBundle\Service\CategoryWithPermissionsRepository;
use PcmtPermissionsBundle\Tests\TestDataBuilder\CategoryWithAccessBuilder;
use PcmtSharedBundle\Service\Checker\CategoryPermissionsCheckerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class CategoryWithPermissionRepositoryTest extends TestCase
{
    /** @var CategoryWithPermissionsRepository */
    private $categoryWithPermissionRepository;

    /** @var CategoryRepositoryInterface|MockObject */
    private $categoryRepositoryMock;

    /** @var CategoryPermissionsChecker|MockObject */
    private $categoryPermissionsCheckerMock;

    protected function setUp(): void
    {
        $this->accessesToTest = CategoryPermissionsCheckerInterface::ALL_LEVELS;

        $this->categoryRepositoryMock = $this->createMock(CategoryRepositoryInterface::class);
        $this->categoryPermissionsCheckerMock = $this->createMock(CategoryPermissionsChecker::class);

        $this->categoryWithPermissionRepository = new CategoryWithPermissionsRepository(
            $this->categoryRepositoryMock,
            $this->categoryPermissionsCheckerMock
        );
    }

    public function dataGetCategoryCodes(): array
    {
        return [
            [
                [
                    (new CategoryWithAccessBuilder())->build(),
                    (new CategoryWithAccessBuilder())->build(),
                ],
            ],
            [
                [
                    (new CategoryWithAccessBuilder())->build(),
                    (new CategoryWithAccessBuilder())->build(),
                ],
            ],
        ];
    }

    /** @dataProvider dataGetCategoryCodes */
    public function testGetCategoryCodesIsGrantedFalse(array $categories): void
    {
        $level = CategoryPermissionsCheckerInterface::VIEW_LEVEL;

        $this->categoryRepositoryMock->method('findAll')->willReturn($categories);

        $this->categoryPermissionsCheckerMock
            ->expects($this->exactly(count($categories)))
            ->method('isGranted')
            ->willReturn(false);

        $codes = $this->categoryWithPermissionRepository->getCategoryCodes($level);
        $this->assertCount(0, $codes);
    }

    /** @dataProvider dataGetCategoryCodes */
    public function testGetCategoryCodesIsGrantedTrue(array $categories): void
    {
        $level = CategoryPermissionsCheckerInterface::VIEW_LEVEL;

        $this->categoryRepositoryMock->method('findAll')->willReturn($categories);

        $this->categoryPermissionsCheckerMock
            ->expects($this->exactly(count($categories)))
            ->method('isGranted')
            ->willReturn(true);

        $codes = $this->categoryWithPermissionRepository->getCategoryCodes($level);
        $this->assertEquals(count($categories), count($codes));
    }
}