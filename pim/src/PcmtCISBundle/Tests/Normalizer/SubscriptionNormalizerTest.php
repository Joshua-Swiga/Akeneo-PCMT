<?php
/*
 * Copyright (c) 2020, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

declare(strict_types=1);

namespace PcmtCISBundle\Tests\Normalizer;

use Akeneo\Pim\Structure\Component\Model\Attribute;
use PcmtCISBundle\Normalizer\SubscriptionNormalizer;
use PcmtCISBundle\Tests\TestDataBuilder\SubscriptionBuilder;
use PHPUnit\Framework\TestCase;

class SubscriptionNormalizerTest extends TestCase
{
    public function testNormalize(): void
    {
        $subscription = (new SubscriptionBuilder())->build();

        $normalizer = new SubscriptionNormalizer();
        $array = $normalizer->normalize($subscription);

        $this->assertIsArray($array);
        $this->assertSame($subscription->getGTIN(), $array['gtin']);
    }

    /**
     * @dataProvider dataSupportsNormalization
     */
    public function testSupportsNormalization(object $object, bool $expectedResult): void
    {
        $normalizer = new SubscriptionNormalizer();
        $result = $normalizer->supportsNormalization($object);
        $this->assertSame($expectedResult, $result);
    }

    public function dataSupportsNormalization(): array
    {
        return [
            [(new SubscriptionBuilder())->build(), true],
            [new Attribute(), false],
        ];
    }
}