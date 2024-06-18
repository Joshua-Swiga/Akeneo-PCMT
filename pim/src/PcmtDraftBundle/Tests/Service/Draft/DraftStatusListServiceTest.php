<?php
/**
 * Copyright (c) 2019, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

declare(strict_types=1);

namespace PcmtDraftBundle\Tests\Service\Draft;

use PcmtDraftBundle\Entity\AbstractDraft;
use PcmtDraftBundle\Service\Draft\DraftStatusListService;
use PcmtDraftBundle\Service\Draft\DraftStatusTranslatorService;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class DraftStatusListServiceTest extends TestCase
{
    /** @var DraftStatusListService */
    private $draftStatusListService;

    /** @var DraftStatusTranslatorService|MockObject */
    private $draftStatusTranslatorServiceMock;

    protected function setUp(): void
    {
        $this->draftStatusTranslatorServiceMock = $this->createMock(
            DraftStatusTranslatorService::class
        );

        $this->draftStatusListService = new DraftStatusListService(
            $this->draftStatusTranslatorServiceMock
        );
    }

    public function testGetAll(): void
    {
        $list = $this->draftStatusListService->getAll();
        $this->assertIsArray($list);
        $this->assertGreaterThan(2, count($list));
        $this->assertIsInt(reset($list));
    }

    public function testGetTranslated(): void
    {
        $this->draftStatusTranslatorServiceMock
            ->method('getNameTranslated')
            ->withConsecutive(
                [AbstractDraft::STATUS_NEW],
                [AbstractDraft::STATUS_APPROVED],
                [AbstractDraft::STATUS_REJECTED]
            )
            ->willReturnOnConsecutiveCalls(
                'pcmt_core.draft.status_new',
                'pcmt_core.draft.status_approved',
                'pcmt_core.draft.status_rejected'
            );

        $list = $this->draftStatusListService->getTranslated();

        $this->assertContains(
            [
                'id'   => AbstractDraft::STATUS_NEW,
                'name' => 'pcmt_core.draft.status_new',
            ],
            $list
        );
    }
}