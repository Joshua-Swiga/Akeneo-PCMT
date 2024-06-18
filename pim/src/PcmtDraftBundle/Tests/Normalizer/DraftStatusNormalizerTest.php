<?php

declare(strict_types=1);

/**
 * Copyright (c) 2020, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

namespace PcmtDraftBundle\Tests\Normalizer;

use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use PcmtDraftBundle\Entity\AbstractDraft;
use PcmtDraftBundle\Entity\DraftStatus;
use PcmtDraftBundle\Normalizer\DraftStatusNormalizer;
use PcmtDraftBundle\Service\Draft\DraftStatusTranslatorService;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class DraftStatusNormalizerTest extends TestCase
{
    public function testNormalizerExpectsError(): void
    {
        $draftStatus = $this->createMock(DraftStatus::class);

        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects($this->once())->method('error');
        $service = $this->createMock(DraftStatusTranslatorService::class);
        $service->method('getNameTranslated')->willThrowException(new \Exception('e'));
        $normalizer = new DraftStatusNormalizer($logger, $service);
        $result = $normalizer->normalize($draftStatus);

        $this->assertIsArray($result);
        $this->assertCount(3, $result);
    }

    /**
     * @dataProvider dataNormalizer
     */
    public function testNormalizer(DraftStatus $draftStatus): void
    {
        $name = 'xxx';
        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects($this->never())->method('error');
        $service = $this->createMock(DraftStatusTranslatorService::class);
        $service->method('getNameTranslated')->willReturn($name);
        $normalizer = new DraftStatusNormalizer($logger, $service);
        $result = $normalizer->normalize($draftStatus);

        $this->assertIsArray($result);
        $this->assertCount(3, $result);
        $this->assertSame($name, $result['name']);
    }

    public function dataNormalizer(): array
    {
        $draftStatus1 = $this->createMock(DraftStatus::class);
        $draftStatus1->method('getId')->willReturn(AbstractDraft::STATUS_NEW);
        $draftStatus2 = $this->createMock(DraftStatus::class);
        $draftStatus2->method('getId')->willReturn(AbstractDraft::STATUS_APPROVED);
        $draftStatus3 = $this->createMock(DraftStatus::class);
        $draftStatus3->method('getId')->willReturn(AbstractDraft::STATUS_REJECTED);

        return [
            [$draftStatus1],
            [$draftStatus2],
            [$draftStatus3],
        ];
    }

    /**
     * @dataProvider dataSupportsNormalization
     */
    public function testSupportsNormalization(object $object, bool $expectedResult): void
    {
        $logger = $this->createMock(LoggerInterface::class);
        $service = $this->createMock(DraftStatusTranslatorService::class);
        $normalizer = new DraftStatusNormalizer($logger, $service);
        $result = $normalizer->supportsNormalization($object);
        $this->assertSame($expectedResult, $result);
    }

    public function dataSupportsNormalization(): array
    {
        return [
            [$this->createMock(DraftStatus::class), true],
            [$this->createMock(AttributeInterface::class), false],
        ];
    }
}