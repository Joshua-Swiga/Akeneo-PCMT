<?php

declare(strict_types=1);

/**
 * Copyright (c) 2020, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

namespace PcmtDraftBundle\Tests\Normalizer;

use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Normalizer\InternalApi\ProductNormalizer;
use Doctrine\Common\Persistence\ObjectRepository;
use Doctrine\ORM\EntityManager;
use PcmtDraftBundle\Entity\AbstractDraft;
use PcmtDraftBundle\Normalizer\PcmtProductNormalizer;
use PcmtDraftBundle\Service\Helper\UnexpectedAttributesFilter;
use PcmtDraftBundle\Tests\TestDataBuilder\ProductBuilder;
use PcmtSharedBundle\Service\Checker\CategoryPermissionsCheckerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class PcmtProductNormalizerTest extends TestCase
{
    /** @var NormalizerInterface */
    private $productNormalizerMock;

    /** @var EntityManager */
    private $entityManagerMock;

    /** @var ObjectRepository */
    private $repositoryMock;

    /** @var ProductInterface */
    private $productMock;

    /** @var UnexpectedAttributesFilter */
    private $unexpectedAttributesFilterMock;

    /** @var CategoryPermissionsCheckerInterface|MockObject */
    private $categoryPermissionsCheckerMock;

    protected function setUp(): void
    {
        $this->categoryPermissionsCheckerMock = $this->createMock(CategoryPermissionsCheckerInterface::class);
        $this->repositoryMock = $this->createMock(ObjectRepository::class);
        $this->productNormalizerMock = $this->createMock(ProductNormalizer::class);
        $this->entityManagerMock = $this->createMock(EntityManager::class);
        $this->entityManagerMock->method('getRepository')->willReturn($this->repositoryMock);
        $this->productMock = $this->createMock(ProductInterface::class);
        $this->unexpectedAttributesFilterMock = $this->createMock(UnexpectedAttributesFilter::class);
    }

    private function getProductNormalizer(): PcmtProductNormalizer
    {
        return new PcmtProductNormalizer(
            $this->productNormalizerMock,
            $this->entityManagerMock,
            $this->unexpectedAttributesFilterMock,
            $this->categoryPermissionsCheckerMock
        );
    }

    /**
     * @dataProvider dataNormalizeIncludeDraftContext
     */
    public function testNormalizeIncludeDraftContext(array $context, bool $expectedDraftIdSet): void
    {
        $this->productNormalizerMock
            ->expects($this->once())
            ->method('normalize')
            ->willReturn([]);

        $normalizer = $this->getProductNormalizer();

        $product = (new ProductBuilder())->build();
        $array = $normalizer->normalize($product, 'internal_api', $context);

        $this->assertSame($expectedDraftIdSet, isset($array['draftId']));
    }

    public function dataNormalizeIncludeDraftContext(): array
    {
        return [
            'empty context' => [
                [],
                false,
            ],
            'not empty context' => [
                ['xxx' => 1],
                false,
            ],
            'context with include draft' => [
                ['include_draft_id' => true],
                true,
            ],
            'context with include draft 2' => [
                [
                    'xxx'              => 'yyy',
                    'include_draft_id' => 1,
                ],
                true,
            ],
            'context with include draft set to false' => [
                ['include_draft_id' => false],
                false,
            ],
        ];
    }

    /**
     * @dataProvider dataNormalizeIncludePermissionsContext
     */
    public function testNormalizeIncludePermissionsContext(array $context, bool $isPermissionSet): void
    {
        $this->productNormalizerMock
            ->expects($this->once())
            ->method('normalize')
            ->willReturn([]);

        $this->categoryPermissionsCheckerMock
            ->expects($this->exactly($isPermissionSet ? 1 : 0))
            ->method('hasAccessToProduct');

        $normalizer = $this->getProductNormalizer();

        $product = (new ProductBuilder())->build();
        $array = $normalizer->normalize($product, 'internal_api', $context);

        $this->assertSame($isPermissionSet, isset($array[PcmtProductNormalizer::PERMISSION_TO_EDIT]));
    }

    public function dataNormalizeIncludePermissionsContext(): array
    {
        return [
            'empty context' => [
                [],
                false,
            ],
            'not empty context' => [
                ['xxx' => 1],
                false,
            ],
            'context with include permissions' => [
                [PcmtProductNormalizer::INCLUDE_CATEGORY_PERMISSIONS => true],
                true,
            ],
            'context with include permissions 2' => [
                [
                    'xxx'                                               => 'yyy',
                    PcmtProductNormalizer::INCLUDE_CATEGORY_PERMISSIONS => 1,
                ],
                true,
            ],
            'context with include permissions set to false' => [
                [PcmtProductNormalizer::INCLUDE_CATEGORY_PERMISSIONS => false],
                false,
            ],
        ];
    }

    /**
     * @dataProvider dataNormalizeForDifferentDrafts
     */
    public function testNormalizeForDifferentDrafts(?AbstractDraft $draft, int $expectedDraftId): void
    {
        $this->productNormalizerMock
            ->expects($this->once())
            ->method('normalize')
            ->willReturn([]);

        $this->repositoryMock->method('findOneBy')->willReturn($draft);

        $normalizer = $this->getProductNormalizer();

        $product = (new ProductBuilder())->build();
        $array = $normalizer->normalize($product, 'internal_api', ['include_draft_id' => true]);

        $this->assertSame($expectedDraftId, $array['draftId']);
    }

    public function dataNormalizeForDifferentDrafts(): array
    {
        $value = 2;
        $draft = $this->createMock(AbstractDraft::class);
        $draft->method('getId')->willReturn($value);

        return [
            [
                $draft,
                $value,
            ],
            [
                null,
                0,
            ],
        ];
    }

    /**
     * @dataProvider dataSupportsNormalization
     */
    public function testSupportsNormalization(bool $value): void
    {
        $this->productNormalizerMock
            ->expects($this->once())
            ->method('supportsNormalization')
            ->willReturn($value);

        $normalizer = $this->getProductNormalizer();

        $product = (new ProductBuilder())->build();
        $result = $normalizer->supportsNormalization($product, 'internal_api');
        $this->assertSame($result, $value);
    }

    public function dataSupportsNormalization(): array
    {
        return [
            [true],
            [false],
        ];
    }

    public function testNormalizeWhenImportViaDraftsIsSetInContext(): void
    {
        $this->productNormalizerMock
            ->expects($this->once())
            ->method('normalize')
            ->willReturn(
                [
                    'values' => [],
                ]
            );

        $this->unexpectedAttributesFilterMock
            ->expects($this->once())
            ->method('filter');

        $this->productMock
            ->method('isVariant')
            ->willReturn(true);

        $normalizer = $this->getProductNormalizer();

        $normalizer->normalize($this->productMock, 'standard', ['import_via_drafts' => true]);
    }

    public function testNormalizeWhenImportViaDraftsIsSetInContextAndProductIsNotVariant(): void
    {
        $this->productNormalizerMock
            ->expects($this->once())
            ->method('normalize')
            ->willReturn([]);

        $this->unexpectedAttributesFilterMock
            ->expects($this->never())
            ->method('filter');

        $this->productMock
            ->method('isVariant')
            ->willReturn(false);

        $normalizer = $this->getProductNormalizer();

        $normalizer->normalize($this->productMock, 'standard', ['import_via_drafts' => true]);
    }

    public function testNormalizeWhenImportViaDraftsIsSetInContextAndNormalizedDataHasNotValuesKey(): void
    {
        $this->productNormalizerMock
            ->expects($this->once())
            ->method('normalize')
            ->willReturn([]);

        $this->unexpectedAttributesFilterMock
            ->expects($this->never())
            ->method('filter');

        $this->productMock
            ->method('isVariant')
            ->willReturn(false);

        $normalizer = $this->getProductNormalizer();

        $normalizer->normalize($this->productMock, 'standard', ['import_via_drafts' => true]);
    }
}