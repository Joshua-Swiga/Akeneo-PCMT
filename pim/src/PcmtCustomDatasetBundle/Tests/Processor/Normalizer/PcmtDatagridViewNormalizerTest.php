<?php
/**
 * Copyright (c) 2020, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

declare(strict_types=1);

namespace PcmtCustomDatasetBundle\Tests\Processor\Normalizer;

use Akeneo\Pim\Enrichment\Component\Product\Model\EntityWithAssociationsInterface;
use Oro\Bundle\PimDataGridBundle\Entity\DatagridView;
use PcmtCoreBundle\ArrayConverter\StandardToFlat\AttributeConverter;
use PcmtCustomDatasetBundle\Processor\Normalizer\PcmtDatagridViewNormalizer;
use PcmtCustomDatasetBundle\Tests\TestDataBuilder\DatagridViewBuilder;
use PcmtCustomDatasetBundle\Tests\TestDataBuilder\UserBuilder;
use PHPUnit\Framework\TestCase;

class PcmtDatagridViewNormalizerTest extends TestCase
{
    /**
     * @dataProvider dataNormalize
     */
    public function testNormalize(DatagridView $datagrid, array $expectedNormalization): void
    {
        $normalizer = new PcmtDatagridViewNormalizer();
        $result = $normalizer->normalize($datagrid);
        foreach ($expectedNormalization as $key => $value) {
            $this->assertSame($value, $result[$key]);
        }
    }

    public function dataNormalize(): array
    {
        return [
            'full filled data' => [
                'datagrid with owner' => (new DatagridViewBuilder())->build(),
                'expected results'    => [
                    'owner'          => DatagridViewBuilder::EXAMPLE_OWNER_USERNAME,
                    'label'          => DatagridViewBuilder::EXAMPLE_LABEL,
                    'type'           => DatagridViewBuilder::EXAMPLE_TYPE,
                    'datagrid_alias' => DatagridViewBuilder::EXAMPLE_ALIAS,
                    'columns'        => DatagridViewBuilder::EXAMPLE_COLUMNS,
                    'filters'        => DatagridViewBuilder::EXAMPLE_FILTERS,
                ],
            ],
            'username is empty' => [
                'datagrid with owner' => (new DatagridViewBuilder())
                    ->withOwner(
                        (new UserBuilder())
                            ->withUsername('')
                            ->build()
                    )
                    ->build(),
                'expected results'    => [
                    'owner' => '',
                ],
            ],
            'username is null' => [
                'datagrid with owner' => (new DatagridViewBuilder())
                    ->withOwner(
                        (new UserBuilder())
                            ->withUsername(null)
                            ->build()
                    )
                    ->build(),
                'expected results'    => [
                    'owner' => '',
                ],
            ],
        ];
    }

    /**
     * @dataProvider dataSupportsNormalization
     */
    public function testSupportsNormalization(object $object, string $format, bool $expectedResult): void
    {
        $normalizer = new PcmtDatagridViewNormalizer();
        $result = $normalizer->supportsNormalization($object, $format);
        $this->assertSame($expectedResult, $result);
    }

    public function dataSupportsNormalization(): array
    {
        return [
            [
                $this->createMock(DatagridView::class),
                'internal_api',
                true,
            ],
            [
                $this->createMock(DatagridView::class),
                'standard',
                false,
            ],
            [
                $this->createMock(EntityWithAssociationsInterface::class),
                'internal_api',
                false,
            ],
            [
                $this->createMock(AttributeConverter::class),
                'internal_api',
                false,
            ],
        ];
    }
}