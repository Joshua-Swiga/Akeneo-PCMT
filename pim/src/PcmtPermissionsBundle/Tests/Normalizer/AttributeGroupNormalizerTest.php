<?php
/**
 * Copyright (c) 2020, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

declare(strict_types=1);

namespace PcmtPermissionsBundle\Tests\Normalizer;

use PcmtPermissionsBundle\Entity\AttributeGroupAccess;
use PcmtPermissionsBundle\Normalizer\AttributeGroupNormalizer;
use PcmtPermissionsBundle\Tests\TestDataBuilder\AttributeGroupAccessBuilder;
use PcmtPermissionsBundle\Tests\TestDataBuilder\AttributeGroupWithAccessBuilder;
use PcmtPermissionsBundle\Tests\TestDataBuilder\UserGroupBuilder;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class AttributeGroupNormalizerTest extends TestCase
{
    /** @var AttributeGroupNormalizer */
    private $attributeGroupNormalizer;

    /** @var NormalizerInterface|MockObject */
    private $akeneoAttributeGroupNormalizerMock;

    protected function setUp(): void
    {
        $this->akeneoAttributeGroupNormalizerMock = $this->createMock(NormalizerInterface::class);

        $this->attributeGroupNormalizer = new AttributeGroupNormalizer(
            $this->akeneoAttributeGroupNormalizerMock
        );

        $this->akeneoAttributeGroupNormalizerMock
            ->method('normalize')
            ->willReturn(
                [
                    'code'                  => 'PRODUCT',
                    'sort_order'            => 0,
                    'attribute'             => [
                        'price_reference',
                        'PRODUCT_DESCRIPTION',
                        'STRENGTH',
                        'ACTIVE_INGREDIENT',
                        'DOSAGE_FORM',
                        'ROUTE_OF_ADMINISTRATION',
                        'FREQUENCY',
                    ],
                    'labels'                => [
                        'en_US' => '_PRODUCT',
                        'fr_FR' => null,
                    ],
                    'attributes_sort_order' => [
                        'price_reference'         => 3,
                        'PRODUCT_DESCRIPTION'     => 1,
                        'STRENGTH'                => 8,
                        'ACTIVE_INGREDIENT'       => 9,
                        'DOSAGE_FORM'             => 5,
                        'ROUTE_OF_ADMINISTRATION' => 6,
                        'FREQUENCY'               => 4,
                    ],
                    'meta'                  => [
                        'id' => 3,
                    ],
                ]
            );
    }

    public function testNormalize(): void
    {
        $attributeGroup = (new AttributeGroupWithAccessBuilder())
            ->addAccess(
                (new AttributeGroupAccessBuilder())->withId(1)->withUserGroup((new UserGroupBuilder())->build())->withLevel(
                    AttributeGroupAccess::VIEW_LEVEL
                )->build()
            )
            ->addAccess(
                (new AttributeGroupAccessBuilder())->withId(2)->withUserGroup((new UserGroupBuilder())->build())->withLevel(
                    AttributeGroupAccess::EDIT_LEVEL
                )->build()
            )
            ->addAccess(
                (new AttributeGroupAccessBuilder())->withId(3)->withUserGroup((new UserGroupBuilder())->buildWithAnotherId())->withLevel(
                    AttributeGroupAccess::OWN_LEVEL
                )->build()
            )
            ->build();

        $result = $this->attributeGroupNormalizer->normalize($attributeGroup);

        $this->assertArrayHasKey('permission[allowed_to_view]', $result);
        $this->assertEquals(UserGroupBuilder::DEFAULT_ID, $result['permission[allowed_to_view]']);
        $this->assertArrayHasKey('permission[allowed_to_edit]', $result);
        $this->assertEquals(UserGroupBuilder::DEFAULT_ID, $result['permission[allowed_to_edit]']);
        $this->assertArrayHasKey('permission[allowed_to_own]', $result);
        $this->assertEquals(UserGroupBuilder::ANOTHER_ID, $result['permission[allowed_to_own]']);
    }

    public function testSupportsNormalization(): void
    {
        $attributeGroup = (new AttributeGroupWithAccessBuilder())
            ->build();

        $this->assertTrue($this->attributeGroupNormalizer->supportsNormalization($attributeGroup));
    }
}
