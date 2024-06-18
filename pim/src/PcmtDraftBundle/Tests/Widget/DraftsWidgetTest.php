<?php
/**
 * Copyright (c) 2020, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

declare(strict_types=1);

namespace PcmtDraftBundle\Tests\Widget;

use PcmtDraftBundle\Entity\AbstractDraft;
use PcmtDraftBundle\Repository\DraftRepository;
use PcmtDraftBundle\Tests\TestDataBuilder\ExistingProductDraftBuilder;
use PcmtDraftBundle\Tests\TestDataBuilder\NewProductDraftBuilder;
use PcmtDraftBundle\Widget\DraftsWidget;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class DraftsWidgetTest extends TestCase
{
    /** @var DraftRepository|MockObject */
    private $draftRepositoryMock;

    /** @var NormalizerInterface|MockObject */
    private $serializerMock;

    /** @var DraftsWidget */
    private $draftsWidget;

    protected function setUp(): void
    {
        $this->draftRepositoryMock = $this->createMock(DraftRepository::class);
        $this->serializerMock = $this->createMock(NormalizerInterface::class);
        $this->draftsWidget = new DraftsWidget(
            $this->draftRepositoryMock,
            $this->serializerMock
        );
    }

    /**
     * @dataProvider dataGetData
     */
    public function testGetData(array $drafts, array $expectedResult): void
    {
        $criteria = [
            'status' => AbstractDraft::STATUS_NEW,
        ];
        $this->draftRepositoryMock
            ->expects($this->once())
            ->method('findBy')
            ->with($criteria, null, 20, 0)
            ->willReturn($drafts);
        $this->serializerMock
            ->expects($this->once())
            ->method('normalize')
            ->with($drafts)
            ->willReturn($expectedResult);
        $result = $this->draftsWidget->getData();
        $this->assertSame($expectedResult, $result);
    }

    public function dataGetData(): array
    {
        return [
            'not empty drafts list' => [
                'drafts'         => [
                    (new NewProductDraftBuilder())->build(),
                    (new ExistingProductDraftBuilder())->build(),
                ],
                'expectedResult' => [
                    [
                        'some' => 'result',
                    ],
                    [
                        'some' => 'result',
                    ],
                ],
            ],
            'empty drafts list' => [
                'drafts'         => [],
                'expectedResult' => [],
            ],
        ];
    }
}