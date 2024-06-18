<?php

declare(strict_types=1);

/**
 * Copyright (c) 2020, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

namespace PcmtDraftBundle\Tests\Service\Draft;

use PcmtDraftBundle\Entity\ExistingObjectDraftInterface;
use PcmtDraftBundle\Entity\ExistingProductDraft;
use PcmtDraftBundle\Entity\ExistingProductModelDraft;
use PcmtDraftBundle\Repository\DraftRepository;
use PcmtDraftBundle\Service\Draft\DraftExistenceChecker;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class DraftExistenceCheckerTest extends TestCase
{
    /** @var DraftRepository|MockObject */
    private $draftRepositoryMock;

    protected function setUp(): void
    {
        $this->draftRepositoryMock = $this->createMock(DraftRepository::class);
    }

    /**
     * @dataProvider dataCheckIfDraftForObjectAlreadyExists
     */
    public function testCheckIfDraftForObjectAlreadyExists(ExistingObjectDraftInterface $draft, int $count, bool $expectedResult): void
    {
        $this->draftRepositoryMock->method('count')->willReturn($count);
        $checker = new DraftExistenceChecker($this->draftRepositoryMock);
        $this->assertSame($expectedResult, $checker->checkIfDraftForObjectAlreadyExists($draft));
    }

    public function dataCheckIfDraftForObjectAlreadyExists(): array
    {
        $draft1 = $this->createMock(ExistingProductDraft::class);
        $draft2 = $this->createMock(ExistingProductModelDraft::class);

        return [
            'check - not exists' => [$draft1, 0, false],
            'check - exists'     => [$draft2, 1, true],
        ];
    }

    public function testCheckIfDraftForObjectAlreadyExistsThrowsException(): void
    {
        $draft = $this->createMock(ExistingObjectDraftInterface::class);
        $this->expectException(\Throwable::class);
        $checker = new DraftExistenceChecker($this->draftRepositoryMock);
        $checker->checkIfDraftForObjectAlreadyExists($draft);
    }
}