<?php
/**
 * Copyright (c) 2020, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

declare(strict_types=1);

namespace PcmtDraftBundle\Tests\Normalizer;

use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Pim\Structure\Component\Model\AttributeTranslationInterface;
use PcmtCoreBundle\Entity\AttributeTranslation;
use PcmtCoreBundle\Normalizer\Standard\DescriptionNormalizer;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class DescriptionNormalizerTest extends TestCase
{
    /**
     * @dataProvider provideDataForNormalize
     */
    public function testNormalize(AttributeInterface $attribute, int $expectedCount, ?string $firstDesc): void
    {
        $descriptionNormalizer = new DescriptionNormalizer();
        $array = $descriptionNormalizer->normalize($attribute);

        $this->assertIsArray($array);
        $this->assertCount($expectedCount, $array);
        if ($firstDesc) {
            $firstResult = reset($array);
            $this->assertSame($firstDesc, $firstResult);
        }
    }

    public function testNormalizeThrowsException(): void
    {
        /** @var AttributeTranslation|MockObject $wrongTranslation */
        $wrongTranslation = $this->createMock(AttributeTranslationInterface::class);

        $attribute = $this->createMock(AttributeInterface::class);
        $attribute->method('getTranslations')->willReturn([$wrongTranslation]);

        $this->expectException(\LogicException::class);

        $descriptionNormalizer = new DescriptionNormalizer();
        $descriptionNormalizer->normalize($attribute);
    }

    /**
     * @dataProvider provideDataForSupportsNormalization
     */
    public function testSupportsNormalization(object $object, bool $expectedResult): void
    {
        $descriptionNormalizer = new DescriptionNormalizer();
        $result = $descriptionNormalizer->supportsNormalization($object);
        $this->assertSame($expectedResult, $result);
    }

    public function provideDataForSupportsNormalization(): array
    {
        return [
            'correct object' => [$this->createMock(AttributeInterface::class), true],
            'wrong object'   => [$this->createMock(ProductInterface::class), false],
        ];
    }

    public function provideDataForNormalize(): array
    {
        /** @var AttributeTranslation|MockObject $englishTranslation */
        $englishTranslation = $this->createMock(AttributeTranslation::class);
        $englishTranslation->method('getDescription')->willReturn('english desc');
        $englishTranslation->method('getLocale')->willReturn('en_US');
        /** @var AttributeTranslation|MockObject $frenchTranslation */
        $frenchTranslation = $this->createMock(AttributeTranslation::class);
        $frenchTranslation->method('getDescription')->willReturn('french desc');
        $frenchTranslation->method('getLocale')->willReturn('fr');

        $attribute1 = $this->createMock(AttributeInterface::class);
        $attribute1->method('getTranslations')->willReturn([$englishTranslation, $frenchTranslation]);

        $attribute2 = $this->createMock(AttributeInterface::class);
        $attribute2->method('getTranslations')->willReturn([$frenchTranslation]);

        $attribute3 = $this->createMock(AttributeInterface::class);
        $attribute3->method('getTranslations')->willReturn([]);

        return [
            'attribute with 2 translations' => [$attribute1, 2, $englishTranslation->getDescription()],
            'attribute with 1 translation'  => [$attribute2, 1, $frenchTranslation->getDescription()],
            'attribute with 0 translations' => [$attribute3, 0, null],
        ];
    }
}