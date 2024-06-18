<?php

/**
 * Copyright (c) 2020, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

declare(strict_types=1);

namespace PcmtDraftBundle\Tests\Connector\Job\Provider;

use PcmtDraftBundle\Connector\Job\Provider\DraftsProvider;
use PcmtDraftBundle\Entity\AbstractDraft;
use PcmtDraftBundle\Repository\DraftRepositoryInterface;
use PcmtDraftBundle\Tests\TestDataBuilder\ExistingProductDraftBuilder;
use PcmtDraftBundle\Tests\TestDataBuilder\NewProductDraftBuilder;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class DraftsProviderTest extends TestCase
{
    /** @var DraftsProvider */
    private $provider;

    /** @var DraftRepositoryInterface|MockObject */
    private $draftsRepositoryMock;

    protected function setUp(): void
    {
        $this->draftsRepositoryMock = $this->createMock(DraftRepositoryInterface::class);

        $this->provider = new DraftsProvider($this->draftsRepositoryMock);
    }

    public function testPrepareWhenAllSelected(): void
    {
        $draftOfANewProduct = (new NewProductDraftBuilder())->withId(1)->build();
        $draftOfAnExistingProduct = (new ExistingProductDraftBuilder())->withId(2)->build();

        $this->draftsRepositoryMock
            ->method('findWithPermissionAndStatus')
            ->with(AbstractDraft::STATUS_NEW)
            ->willReturn([
                $draftOfANewProduct,
                $draftOfAnExistingProduct,
            ]);

        $result = $this->provider->prepare(true, [], []);

        $this->assertEquals([
            $draftOfANewProduct,
            $draftOfAnExistingProduct,
        ], $result);
    }

    public function testPrepareWhenAllSelectedWithOneExcluded(): void
    {
        $draftOfANewProduct = (new NewProductDraftBuilder())->withId(1)->build();
        $draftOfAnExistingProduct = (new ExistingProductDraftBuilder())->withId(2)->build();

        $this->draftsRepositoryMock
            ->method('findWithPermissionAndStatus')
            ->with(AbstractDraft::STATUS_NEW)
            ->willReturn([
                $draftOfANewProduct,
                $draftOfAnExistingProduct,
            ]);

        $result = $this->provider->prepare(true, [2], []);

        $this->assertEquals([
            $draftOfANewProduct,
        ], $result);
    }

    public function testPrepareWhenOnlyOneSelected(): void
    {
        $draftOfANewProduct = (new NewProductDraftBuilder())->withId(1)->build();

        $this->draftsRepositoryMock
            ->method('findBy')
            ->with([
                'status' => AbstractDraft::STATUS_NEW,
                'id'     => [1],
            ])
            ->willReturn([
                $draftOfANewProduct,
            ]);

        $result = $this->provider->prepare(false, [], [1]);

        $this->assertEquals([
            $draftOfANewProduct,
        ], $result);
    }
}