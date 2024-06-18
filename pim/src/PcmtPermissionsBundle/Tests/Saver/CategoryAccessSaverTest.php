<?php

declare(strict_types=1);

/**
 * Copyright (c) 2020, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

namespace PcmtPermissionsBundle\Tests\Saver;

use Doctrine\ORM\EntityManager;
use PcmtPermissionsBundle\Saver\CategoryAccessSaver;
use PcmtPermissionsBundle\Tests\TestDataBuilder\CategoryAccessBuilder;
use PcmtPermissionsBundle\Tests\TestDataBuilder\CategoryWithAccessBuilder;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class CategoryAccessSaverTest extends TestCase
{
    /** @var EntityManager|MockObject */
    private $entityManagerMock;

    /** @var CategoryAccessSaver */
    private $categoryAccessSaver;

    protected function setUp(): void
    {
        $this->entityManagerMock = $this->createMock(EntityManager::class);
        $this->categoryAccessSaver = new CategoryAccessSaver($this->entityManagerMock);
    }

    public function testSave(): void
    {
        $this->entityManagerMock->expects($this->once())->method('flush');
        $this->entityManagerMock->expects($this->once())->method('persist');

        $category = (new CategoryWithAccessBuilder())->build();
        $this->categoryAccessSaver->save((new CategoryAccessBuilder($category))->build());
    }

    public function testSaveAll(): void
    {
        $category = (new CategoryWithAccessBuilder())->build();
        $objects = [
            (new CategoryAccessBuilder($category))->build(),
            (new CategoryAccessBuilder($category))->build(),
        ];

        $this->entityManagerMock->expects($this->once())->method('flush');
        $this->entityManagerMock->expects($this->exactly(2))->method('persist');

        $this->categoryAccessSaver->saveAll($objects);
    }
}