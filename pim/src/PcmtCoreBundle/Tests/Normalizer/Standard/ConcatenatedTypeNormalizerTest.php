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
use PcmtCoreBundle\Entity\ConcatenatedProperty;
use PcmtCoreBundle\Normalizer\Standard\ConcatenatedTypeNormalizer;
use PcmtCoreBundle\Tests\TestDataBuilder\AttributeBuilder;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ConcatenatedTypeNormalizerTest extends TestCase
{
    /** @var ConcatenatedTypeNormalizer */
    private $concatenatedNormalizer;

    /** @var AttributeInterface|MockObject */
    private $attribute;

    protected function setUp(): void
    {
        $this->attribute = (new AttributeBuilder())->buildConcatenated();
        parent::setUp();
    }

    /**
     * @dataProvider provideData
     */
    public function testNormalize(string $attributes, string $separators, array $result): void
    {
        $this->attribute->setProperty('attributes', $attributes);
        $this->attribute->setProperty('separators', $separators);

        $this->concatenatedNormalizer = new ConcatenatedTypeNormalizer();
        $array = $this->concatenatedNormalizer->normalize($this->attribute);

        $this->assertIsArray($array);
        $this->assertSame($result, $array);
    }

    public function provideData(): array
    {
        return [
            ['aaaa,bbb', '*', [
                'attribute1' => 'aaaa',
                'attribute2' => 'bbb',
                'separator1' => '*',
            ]],
            ['aa,bbb', '|', [
                'attribute1' => 'aa',
                'attribute2' => 'bbb',
                'separator1' => '|',
            ]],
            ['aa,bbb', ConcatenatedProperty::SPECIAL_CODE_FOR_DELIMITER, [
                'attribute1' => 'aa',
                'attribute2' => 'bbb',
                'separator1' => ConcatenatedProperty::DELIMITER,
            ]],
            ['aa,bbb,cc', 'x,y', [
                'attribute1' => 'aa',
                'attribute2' => 'bbb',
                'attribute3' => 'cc',
                'separator1' => 'x',
                'separator2' => 'y',
            ]],
        ];
    }

    /**
     * @dataProvider provideDataForSupportsNormalization
     */
    public function testSupportsNormalization(object $object, bool $expectedResult): void
    {
        $descriptionNormalizer = new ConcatenatedTypeNormalizer();
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
}