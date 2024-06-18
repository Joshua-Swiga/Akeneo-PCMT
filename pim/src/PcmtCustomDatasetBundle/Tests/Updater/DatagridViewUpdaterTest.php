<?php
/**
 * Copyright (c) 2020, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

declare(strict_types=1);

namespace PcmtCustomDatasetBundle\Tests\Updater;

use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Akeneo\Tool\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use Oro\Bundle\PimDataGridBundle\Entity\DatagridView;
use PcmtCustomDatasetBundle\Exception\UserMissingException;
use PcmtCustomDatasetBundle\Tests\TestDataBuilder\DatagridViewBuilder;
use PcmtCustomDatasetBundle\Tests\TestDataBuilder\UserBuilder;
use PcmtCustomDatasetBundle\Updater\DatagridViewUpdater;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class DatagridViewUpdaterTest extends TestCase
{
    /** @var IdentifiableObjectRepositoryInterface|MockObject */
    private $userRepositoryMock;

    /** @var ObjectUpdaterInterface|MockObject */
    private $baseDatagridViewUpdaterMock;

    /** @var DatagridViewUpdater */
    private $datagridViewUpdater;

    protected function setUp(): void
    {
        $this->userRepositoryMock = $this->createMock(IdentifiableObjectRepositoryInterface::class);
        $this->baseDatagridViewUpdaterMock = $this->createMock(ObjectUpdaterInterface::class);
        $this->datagridViewUpdater = new DatagridViewUpdater(
            $this->userRepositoryMock,
            $this->baseDatagridViewUpdaterMock
        );
    }

    /**
     * @dataProvider dataUpdate
     */
    public function testUpdateWithoutUserInDatabase(DatagridView $datagridView, array $data): void
    {
        $this->expectException(UserMissingException::class);
        $this->datagridViewUpdater->update($datagridView, $data);
    }

    /**
     * @dataProvider dataUpdate
     */
    public function testUpdate(DatagridView $datagridView, array $data): void
    {
        $user = (new UserBuilder())->build();
        $this->userRepositoryMock
            ->method('findOneByIdentifier')
            ->willReturn($user);
        $this->baseDatagridViewUpdaterMock
            ->expects($this->once())
            ->method('update')
            ->with($datagridView, $data, [])
            ->willReturn($this->baseDatagridViewUpdaterMock);
        $this->datagridViewUpdater->update($datagridView, $data);
    }

    public function dataUpdate(): array
    {
        return [
            [
                'datagridView' => (new DatagridViewBuilder())->build(),
                'data'         => [
                    'owner' => UserBuilder::EXAMPLE_USERNAME,
                ],
            ],
        ];
    }
}