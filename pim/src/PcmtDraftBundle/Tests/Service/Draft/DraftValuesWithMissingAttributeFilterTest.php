<?php
/**
 * Copyright (c) 2020, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

declare(strict_types=1);

namespace PcmtDraftBundle\Tests\Service\Draft;

use Akeneo\Pim\Structure\Component\Repository\AttributeRepositoryInterface;
use PcmtDraftBundle\Service\Draft\DraftValuesWithMissingAttributeFilter;
use PcmtDraftBundle\Tests\TestDataBuilder\ProductBuilder;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class DraftValuesWithMissingAttributeFilterTest extends TestCase
{
    /** @var DraftValuesWithMissingAttributeFilter */
    private $filter;

    /** @var AttributeRepositoryInterface|MockObject */
    private $attributeRepositoryMock;

    protected function setUp(): void
    {
        $this->attributeRepositoryMock = $this->createMock(AttributeRepositoryInterface::class);

        $this->filter = new DraftValuesWithMissingAttributeFilter($this->attributeRepositoryMock);
    }

    public function dataFilter(): array
    {
        return [
            'default_case'                   => [
                'inputValues'    => [
                    'sku'              => [
                        [
                            'data'   => 'PE_BUCKET_OTHER',
                            'scope'  => null,
                            'locale' => null,
                        ],
                    ],
                    'MD_HUB_DUNS'      => [
                        [
                            'data'   => null,
                            'scope'  => null,
                            'locale' => null,
                        ],
                    ],
                    'MD_HUB_BUCKETID'  => [
                        [
                            'data'   => 'BUCKET_OTHER',
                            'scope'  => null,
                            'locale' => null,
                        ],
                    ],
                    'MD_HUB_SITE_TYPE' => [
                        [
                            'data'   => 'Forecast',
                            'scope'  => null,
                            'locale' => null,
                        ],
                    ],
                ],
                'attributes'     => [
                    'sku'              => 'pim_catalog_identifier',
                    'MD_HUB_DUNS'      => 'pim_catalog_text',
                    'MD_HUB_BUCKETID'  => 'pim_catalog_text',
                    'MD_HUB_SITE_TYPE' => 'pim_catalog_text',
                ],
                'expectedValues' => [
                    'sku'              => [
                        [
                            'data'   => 'PE_BUCKET_OTHER',
                            'scope'  => null,
                            'locale' => null,
                        ],
                    ],
                    'MD_HUB_DUNS'      => [
                        [
                            'data'   => null,
                            'scope'  => null,
                            'locale' => null,
                        ],
                    ],
                    'MD_HUB_BUCKETID'  => [
                        [
                            'data'   => 'BUCKET_OTHER',
                            'scope'  => null,
                            'locale' => null,
                        ],
                    ],
                    'MD_HUB_SITE_TYPE' => [
                        [
                            'data'   => 'Forecast',
                            'scope'  => null,
                            'locale' => null,
                        ],
                    ],
                ],
            ],
            'when_one_attribute_is_missing'   => [
                'inputValues'    => [
                    'sku'              => [
                        [
                            'data'   => 'PE_BUCKET_OTHER',
                            'scope'  => null,
                            'locale' => null,
                        ],
                    ],
                    'MD_HUB_DUNS'      => [
                        [
                            'data'   => null,
                            'scope'  => null,
                            'locale' => null,
                        ],
                    ],
                    'MD_HUB_BUCKETID'  => [
                        [
                            'data'   => 'BUCKET_OTHER',
                            'scope'  => null,
                            'locale' => null,
                        ],
                    ],
                    'MD_HUB_SITE_TYPE' => [
                        [
                            'data'   => 'Forecast',
                            'scope'  => null,
                            'locale' => null,
                        ],
                    ],
                ],
                'attributes'     => [
                    'sku'              => 'pim_catalog_identifier',
                    'MD_HUB_BUCKETID'  => 'pim_catalog_text',
                    'MD_HUB_SITE_TYPE' => 'pim_catalog_text',
                ],
                'expectedValues' => [
                    'sku'              => [
                        [
                            'data'   => 'PE_BUCKET_OTHER',
                            'scope'  => null,
                            'locale' => null,
                        ],
                    ],
                    'MD_HUB_BUCKETID'  => [
                        [
                            'data'   => 'BUCKET_OTHER',
                            'scope'  => null,
                            'locale' => null,
                        ],
                    ],
                    'MD_HUB_SITE_TYPE' => [
                        [
                            'data'   => 'Forecast',
                            'scope'  => null,
                            'locale' => null,
                        ],
                    ],
                ],
            ],
            'when_two_attributes_are_missing' => [
                'inputValues'    => [
                    'sku'              => [
                        [
                            'data'   => 'PE_BUCKET_OTHER',
                            'scope'  => null,
                            'locale' => null,
                        ],
                    ],
                    'MD_HUB_DUNS'      => [
                        [
                            'data'   => null,
                            'scope'  => null,
                            'locale' => null,
                        ],
                    ],
                    'MD_HUB_BUCKETID'  => [
                        [
                            'data'   => 'BUCKET_OTHER',
                            'scope'  => null,
                            'locale' => null,
                        ],
                    ],
                    'MD_HUB_SITE_TYPE' => [
                        [
                            'data'   => 'Forecast',
                            'scope'  => null,
                            'locale' => null,
                        ],
                    ],
                ],
                'attributes'     => [
                    'sku'             => 'pim_catalog_identifier',
                    'MD_HUB_BUCKETID' => 'pim_catalog_text',
                ],
                'expectedValues' => [
                    'sku'             => [
                        [
                            'data'   => 'PE_BUCKET_OTHER',
                            'scope'  => null,
                            'locale' => null,
                        ],
                    ],
                    'MD_HUB_BUCKETID' => [
                        [
                            'data'   => 'BUCKET_OTHER',
                            'scope'  => null,
                            'locale' => null,
                        ],
                    ],
                ],
            ],
        ];
    }

    /**
     * @dataProvider dataFilter
     */
    public function testFilter(array $inputValues, array $attributes, array $expectedValues): void
    {
        $this->attributeRepositoryMock
            ->method('getAttributeTypeByCodes')
            ->willReturn($attributes);

        $result = $this->filter->filter((new ProductBuilder())->build(), $inputValues);

        $this->assertEquals($expectedValues, $result);
    }
}