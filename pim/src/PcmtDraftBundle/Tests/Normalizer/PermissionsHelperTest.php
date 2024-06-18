<?php

declare(strict_types=1);

/**
 * Copyright (c) 2020, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

namespace PcmtDraftBundle\Tests\Normalizer;

use Akeneo\Tool\Component\Classification\CategoryAwareInterface;
use PcmtDraftBundle\Normalizer\PermissionsHelper;
use PcmtDraftBundle\Tests\TestDataBuilder\ProductBuilder;
use PcmtDraftBundle\Tests\TestDataBuilder\ProductModelBuilder;
use PcmtSharedBundle\Service\Checker\CategoryPermissionsCheckerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class PermissionsHelperTest extends TestCase
{
    /** @var CategoryPermissionsCheckerInterface|MockObject */
    private $categoryPermissionsCheckerMock;

    protected function setUp(): void
    {
        $this->categoryPermissionsCheckerMock = $this->createMock(CategoryPermissionsCheckerInterface::class);
    }

    /**
     * @dataProvider dataNormalizeCategoryPermissions
     */
    public function testNormalizeCategoryPermissions(?CategoryAwareInterface $entity): void
    {
        $this->categoryPermissionsCheckerMock->method('hasAccessToProduct')->willReturnOnConsecutiveCalls(true, true, false);
        $permissionsHelper = $this->getPermissionsHelperInstance();

        $result = $permissionsHelper->normalizeCategoryPermissions($entity);

        $this->assertIsArray($result);
        $this->assertCount(3, $result);
        $this->assertTrue($result['view']);
        $this->assertTrue($result['edit']);
        $this->assertFalse($result['own']);
    }

    public function testNormalizeCategoryPermissionsNoEntity(): void
    {
        $this->categoryPermissionsCheckerMock->method('hasAccessToProduct')->willReturnOnConsecutiveCalls(true, true, false);
        $permissionsHelper = $this->getPermissionsHelperInstance();

        $result = $permissionsHelper->normalizeCategoryPermissions(null);

        $this->assertIsArray($result);
        $this->assertCount(3, $result);
        $this->assertTrue($result['view']);
        $this->assertTrue($result['edit']);
        $this->assertTrue($result['own']);
    }

    public function dataNormalizeCategoryPermissions(): array
    {
        return [
            [(new ProductBuilder())->build()],
            [(new ProductModelBuilder())->build()],
        ];
    }

    public function getPermissionsHelperInstance(): PermissionsHelper
    {
        return new PermissionsHelper(
            $this->categoryPermissionsCheckerMock
        );
    }
}