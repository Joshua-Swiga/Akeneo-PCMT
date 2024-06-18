<?php
/**
 * Copyright (c) 2019, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

declare(strict_types=1);

namespace PcmtDraftBundle\Tests\Normalizer;

use Akeneo\Pim\Enrichment\Component\Product\Model\AssociationInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\EntityWithAssociationsInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Structure\Component\Model\AssociationType;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Doctrine\Common\Collections\ArrayCollection;
use PcmtDraftBundle\Normalizer\AssociationsNormalizer;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class AssociationsNormalizerTest extends TestCase
{
    /** @var EntityWithAssociationsInterface|MockObject */
    private $normalizedEntity;

    public function testNormalize(): void
    {
        $type1 = 'c1';
        $type2 = 'c2';
        $associationType1 = $this->createMock(AssociationType::class);
        $associationType1->method('getCode')->willReturn($type1);
        $associationType2 = $this->createMock(AssociationType::class);
        $associationType2->method('getCode')->willReturn($type2);
        $product1 = $this->createMock(ProductInterface::class);
        $association1 = $this->createMock(AssociationInterface::class);
        $association1->method('getAssociationType')->willReturn($associationType1);
        $association1->method('getGroups')->willReturn([]);
        $association1->method('getProducts')->willReturn(new ArrayCollection());
        $association1->method('getProductModels')->willReturn(new ArrayCollection());
        $association2 = $this->createMock(AssociationInterface::class);
        $association2->method('getAssociationType')->willReturn($associationType2);
        $association2->method('getGroups')->willReturn([]);
        $products = new ArrayCollection();
        $products->add($product1);
        $association2->method('getProducts')->willReturn($products);
        $association2->method('getProductModels')->willReturn(new ArrayCollection());

        $this->normalizedEntity = $this->createMock(EntityWithAssociationsInterface::class);
        $this->normalizedEntity->method('getAssociations')->willReturn([$association1, $association2]);

        $associationNormalizer = new AssociationsNormalizer();
        $array = $associationNormalizer->normalize($this->normalizedEntity, 'standard');

        $this->assertIsArray($array);
        $this->assertIsArray($array[$type1]);
        $this->assertIsArray($array[$type1]['products']);
        $this->assertCount(0, $array[$type1]['products']);
        $this->assertIsArray($array[$type1]['product_models']);
        $this->assertIsArray($array[$type2]);
        $this->assertIsArray($array[$type2]['products']);
        $this->assertCount(1, $array[$type2]['products']);
    }

    /**
     * @dataProvider dataSupportsNormalization
     */
    public function testSupportsNormalization(object $object, string $format, bool $expectedResult): void
    {
        $normalizer = new AssociationsNormalizer();
        $result = $normalizer->supportsNormalization($object, $format);
        $this->assertSame($expectedResult, $result);
    }

    public function dataSupportsNormalization(): array
    {
        return [
            [$this->createMock(EntityWithAssociationsInterface::class), 'standard', true],
            [$this->createMock(EntityWithAssociationsInterface::class), 'notstandard', false],
            [$this->createMock(AttributeInterface::class), 'standard', false],
        ];
    }
}