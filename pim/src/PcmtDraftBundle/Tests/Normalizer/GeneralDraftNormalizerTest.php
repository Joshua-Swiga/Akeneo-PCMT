<?php

declare(strict_types=1);

/**
 * Copyright (c) 2020, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

namespace PcmtDraftBundle\Tests\Normalizer;

use Akeneo\Tool\Component\Localization\Presenter\PresenterInterface;
use Akeneo\UserManagement\Bundle\Context\UserContext;
use PcmtDraftBundle\Entity\DraftInterface;
use PcmtDraftBundle\Normalizer\DraftStatusNormalizer;
use PcmtDraftBundle\Normalizer\GeneralDraftNormalizer;
use PcmtDraftBundle\Tests\TestDataBuilder\ExistingProductDraftBuilder;
use PcmtDraftBundle\Tests\TestDataBuilder\ExistingProductModelDraftBuilder;
use PcmtDraftBundle\Tests\TestDataBuilder\NewProductDraftBuilder;
use PcmtDraftBundle\Tests\TestDataBuilder\NewProductModelDraftBuilder;
use PcmtDraftBundle\Tests\TestDataBuilder\UserBuilder;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Translation\TranslatorInterface;

class GeneralDraftNormalizerTest extends TestCase
{
    /** @var DraftStatusNormalizer|MockObject */
    private $statusNormalizerMock;

    /** @var PresenterInterface|MockObject */
    private $datetimePresenterMock;

    /** @var UserContext|MockObject */
    private $userContextMock;

    /** @var TranslatorInterface|MockObject */
    private $translatorMock;

    protected function setUp(): void
    {
        $this->statusNormalizerMock = $this->createMock(DraftStatusNormalizer::class);
        $this->datetimePresenterMock = $this->createMock(PresenterInterface::class);
        $this->userContextMock = $this->createMock(UserContext::class);
        $this->translatorMock = $this->createMock(TranslatorInterface::class);
    }

    public function dataNormalize(): array
    {
        return [
            [(new ExistingProductDraftBuilder())->withUpdatedAt(new \DateTime())->build()],
            [(new NewProductDraftBuilder())->build()],
            [(new ExistingProductModelDraftBuilder())->withUpdatedAt(new \DateTime())->build()],
            [(new NewProductModelDraftBuilder())->build()],
        ];
    }

    /**
     * @dataProvider dataNormalize
     */
    public function testNormalize(DraftInterface $draft): void
    {
        $normalizer = $this->getGeneralDraftNormalizerInstance();
        $this->statusNormalizerMock->expects($this->once())->method('normalize');
        $result = $normalizer->normalize($draft);
        $this->assertArrayHasKey('typeName', $result);
        $this->assertIsString($result['typeName']);
        $this->assertArrayHasKey('author', $result);
        $this->assertEquals(
            UserBuilder::EXAMPLE_FIRST_NAME.' '.UserBuilder::EXAMPLE_LAST_NAME,
            $result['author']
        );
        $this->assertArrayHasKey('id', $result);
        $this->assertArrayHasKey('createdAt', $result);
        $this->assertArrayHasKey('updatedAt', $result);
        $this->assertArrayHasKey('meta', $result);
    }

    public function testNormalizeUnknownType(): void
    {
        $draftMock = $this->createMock(DraftInterface::class);
        $draftMock->method('getType')->willReturn('xxxxxxx');
        $draftMock->method('getUpdatedAt')->willReturn(new \DateTime());

        $normalizer = $this->getGeneralDraftNormalizerInstance();

        $this->expectException(\InvalidArgumentException::class);
        $normalizer->normalize($draftMock);
    }

    public function testNormalizeWhenUserContextThrowsException(): void
    {
        $this->userContextMock->method('getUserTimezone')->willThrowException(new \RuntimeException());

        $draft = (new ExistingProductDraftBuilder())->build();
        $normalizer = $this->getGeneralDraftNormalizerInstance();

        $result = $normalizer->normalize($draft);
        $this->assertArrayHasKey('createdAt', $result);
        $this->assertArrayHasKey('updatedAt', $result);
    }

    private function getGeneralDraftNormalizerInstance(): GeneralDraftNormalizer
    {
        return new GeneralDraftNormalizer(
            $this->statusNormalizerMock,
            $this->datetimePresenterMock,
            $this->userContextMock,
            $this->translatorMock
        );
    }
}