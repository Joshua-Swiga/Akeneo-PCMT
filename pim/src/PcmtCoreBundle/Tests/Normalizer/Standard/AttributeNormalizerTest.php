<?php
/**
 * Copyright (c) 2020, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

declare(strict_types=1);

namespace PcmtDraftBundle\Tests\Normalizer;

use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use PcmtCoreBundle\Normalizer\Standard\AttributeNormalizer;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class AttributeNormalizerTest extends TestCase
{
    /** @var AttributeNormalizer */
    private $attributeNormalizer;

    /** @var AttributeInterface|MockObject */
    private $attribute;

    /** @var NormalizerInterface|MockObject */
    private $translationNormalizer;

    /** @var NormalizerInterface|MockObject */
    private $dateTimeNormalizer;

    /** @var string[] */
    private $properties = [];

    /** @var NormalizerInterface */
    private $concatenatedNormalizer;

    /** @var NormalizerInterface */
    private $descriptionNormalizer;

    protected function setUp(): void
    {
        $this->attribute = $this->createMock(AttributeInterface::class);

        $this->translationNormalizer = $this->createMock(NormalizerInterface::class);
        $this->dateTimeNormalizer = $this->createMock(NormalizerInterface::class);
        $this->properties = [];

        $this->concatenatedNormalizer = $this->createMock(NormalizerInterface::class);
        $this->descriptionNormalizer = $this->createMock(NormalizerInterface::class);

        parent::setUp();
    }

    /**
     * @param array|string|null $valueC
     * @param array|string|null $valueD
     * @dataProvider provideData
     */
    public function testNormalize($valueC, $valueD): void
    {
        $this->concatenatedNormalizer->expects($this->once())->method('normalize')->willReturn($valueC);
        $this->descriptionNormalizer->expects($this->once())->method('normalize')->willReturn($valueD);

        $this->attributeNormalizer = new AttributeNormalizer(
            $this->translationNormalizer,
            $this->dateTimeNormalizer,
            $this->properties
        );
        $this->attributeNormalizer->setConcatenatedNormalizer($this->concatenatedNormalizer);
        $this->attributeNormalizer->setDescriptionNormalizer($this->descriptionNormalizer);

        $array = $this->attributeNormalizer->normalize($this->attribute);

        $this->assertIsArray($array);
        $this->assertArrayHasKey('descriptions', $array);
        $this->assertArrayHasKey('concatenated', $array);
        $this->assertSame($valueC, $array['concatenated']);
        $this->assertSame($valueD, $array['descriptions']);
    }

    public function provideData(): array
    {
        return [
            ['aaa', 'bbb'],
            [['xxxx' => 'yyyy'], ['xcccc' => 'bbb']],
            [null, null],
        ];
    }
}