<?php
/**
 * Copyright (c) 2020, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

declare(strict_types=1);

namespace PcmtCoreBundle\Tests\Normalizer\Standard;

use PcmtCoreBundle\Normalizer\Standard\CountryCodeNormalizer;
use PcmtCoreBundle\Tests\TestDataBuilder\CountryCodeBuilder;
use PHPUnit\Framework\TestCase;

class CountryCodeNormalizerTest extends TestCase
{
    /** @var CountryCodeNormalizer */
    private $normalizer;

    protected function setUp(): void
    {
        $this->normalizer = new CountryCodeNormalizer();
    }

    public function testNormalize(): void
    {
        $countryCode = (new CountryCodeBuilder())
            ->withCode('POLAND')
            ->withName('POLAND')
            ->build();

        $result = $this->normalizer->normalize($countryCode);

        $this->assertEquals(
            [
                'code'   => 'POLAND',
                'labels' => [
                    'en_US' => 'POLAND',
                ],
            ],
            $result
        );
    }

    public function testSupportsNormalization(): void
    {
        $countryCode = (new CountryCodeBuilder())->build();

        $this->assertTrue($this->normalizer->supportsNormalization($countryCode));
    }
}