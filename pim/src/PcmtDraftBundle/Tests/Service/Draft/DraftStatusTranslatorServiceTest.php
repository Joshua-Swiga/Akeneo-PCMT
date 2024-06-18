<?php
/**
 * Copyright (c) 2019, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

declare(strict_types=1);

namespace PcmtDraftBundle\Tests\Service\Draft;

use PcmtDraftBundle\Entity\AbstractDraft;
use PcmtDraftBundle\Service\Draft\DraftStatusTranslatorService;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Translation\TranslatorInterface;

class DraftStatusTranslatorServiceTest extends TestCase
{
    /**
     * @var TranslatorInterface|MockObject
     */
    private $translatorServiceMock;

    protected function setUp(): void
    {
        $this->translatorServiceMock = $this->createMock(TranslatorInterface::class);
    }

    public function testGetNameThrowsError(): void
    {
        $draftStatusTranslatorService = new DraftStatusTranslatorService($this->translatorServiceMock);
        $this->expectException(\Throwable::class);
        $draftStatusTranslatorService->getName(-1);
    }

    /**
     * @dataProvider dataGetName
     */
    public function testGetName(int $statusId): void
    {
        $this->translatorServiceMock->expects($this->never())->method('trans');

        $draftStatusTranslatorService = new DraftStatusTranslatorService($this->translatorServiceMock);

        $name = $draftStatusTranslatorService->getName($statusId);

        $this->assertNotEmpty($name);
        $this->assertIsString($name);
    }

    public function dataGetName(): array
    {
        return [
            [AbstractDraft::STATUS_NEW],
            [AbstractDraft::STATUS_APPROVED],
            [AbstractDraft::STATUS_REJECTED],
        ];
    }

    /**
     * @dataProvider dataGetNameTranslated
     */
    public function testGetNameTranslated(int $statusId, string $nameTranslated): void
    {
        $this->translatorServiceMock->expects($this->once())->method('trans')->willReturn($nameTranslated);

        $draftStatusTranslatorService = new DraftStatusTranslatorService($this->translatorServiceMock);

        $name = $draftStatusTranslatorService->getNameTranslated($statusId);
        $this->assertSame($nameTranslated, $name);
    }

    public function dataGetNameTranslated(): array
    {
        return [
            [AbstractDraft::STATUS_NEW, 'only new!'],
            [AbstractDraft::STATUS_REJECTED, 'only rejestected #'],
        ];
    }
}