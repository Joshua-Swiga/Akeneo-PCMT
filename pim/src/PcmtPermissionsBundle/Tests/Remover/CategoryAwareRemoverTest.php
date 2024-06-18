<?php
/**
 * Copyright (c) 2020, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

declare(strict_types=1);

namespace PcmtPermissionsBundle\Tests\Remover;

use Akeneo\Tool\Component\Classification\CategoryAwareInterface;
use Doctrine\Common\Persistence\ObjectManager;
use PcmtPermissionsBundle\Exception\NoCategoryAccessException;
use PcmtPermissionsBundle\Remover\CategoryAwareRemover;
use PcmtPermissionsBundle\Tests\TestDataBuilder\ProductBuilder;
use PcmtSharedBundle\Service\Checker\CategoryPermissionsCheckerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class CategoryAwareRemoverTest extends TestCase
{
    /** @var CategoryAwareRemover */
    private $categoryAwareRemover;

    /** @var ObjectManager|MockObject */
    private $objectManagerMock;

    /** @var EventDispatcherInterface|MockObject */
    private $eventDispatcherMock;

    /** @var string */
    private $removedClass;

    /** @var CategoryPermissionsCheckerInterface|MockObject */
    private $categoryPermissionsCheckerMock;

    protected function setUp(): void
    {
        $this->objectManagerMock = $this->createMock(ObjectManager::class);
        $this->eventDispatcherMock = $this->createMock(EventDispatcherInterface::class);
        $this->removedClass = CategoryAwareInterface::class;
        $this->categoryPermissionsCheckerMock = $this->createMock(
            CategoryPermissionsCheckerInterface::class
        );

        $this->categoryAwareRemover = new CategoryAwareRemover(
            $this->objectManagerMock,
            $this->eventDispatcherMock,
            $this->removedClass
        );

        $this->categoryAwareRemover
            ->setPermissionsChecker($this->categoryPermissionsCheckerMock);
    }

    public function testRemoveWhenUserHasAccessToTheProduct(): void
    {
        $product = (new ProductBuilder())->build();

        $this->categoryPermissionsCheckerMock
            ->expects($this->once())
            ->method('hasAccessToProduct')
            ->willReturn(true);

        $this->categoryAwareRemover->remove($product);
    }

    public function testRemoveWhenUserHasNoAccessToTheProduct(): void
    {
        $product = (new ProductBuilder())->build();

        $this->categoryPermissionsCheckerMock
            ->expects($this->once())
            ->method('hasAccessToProduct')
            ->willReturn(false);

        $this->expectException(NoCategoryAccessException::class);

        $this->categoryAwareRemover->remove($product);
    }
}
