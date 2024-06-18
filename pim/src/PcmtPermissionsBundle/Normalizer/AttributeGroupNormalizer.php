<?php
/**
 * Copyright (c) 2020, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

declare(strict_types=1);

namespace PcmtPermissionsBundle\Normalizer;

use PcmtPermissionsBundle\Entity\AttributeGroupAccess;
use PcmtPermissionsBundle\Entity\AttributeGroupWithAccess;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class AttributeGroupNormalizer implements NormalizerInterface
{
    /** @var NormalizerInterface */
    private $internalNormalizer;

    public function __construct(NormalizerInterface $internalNormalizer)
    {
        $this->internalNormalizer = $internalNormalizer;
    }

    /**
     * {@inheritdoc}
     */
    public function normalize($object, $format = null, array $context = [])
    {
        /** @var AttributeGroupWithAccess $object */
        $data = $this->internalNormalizer->normalize($object, 'internal_api');

        $data['permission[allowed_to_own]'] = $this->getAccesses(
            $object,
            AttributeGroupAccess::OWN_LEVEL
        );
        $data['permission[allowed_to_edit]'] = $this->getAccesses(
            $object,
            AttributeGroupAccess::EDIT_LEVEL
        );
        $data['permission[allowed_to_view]'] = $this->getAccesses(
            $object,
            AttributeGroupAccess::VIEW_LEVEL
        );

        return $data;
    }

    private function getAccesses(AttributeGroupWithAccess $attributeGroup, string $level): string
    {
        $accesses = $attributeGroup->getAccesses()->filter(
            function (AttributeGroupAccess $access) use ($level) {
                return $level === $access->getLevel();
            }
        );

        return implode(
            ',',
            $accesses->map(
                function (AttributeGroupAccess $access) {
                    return $access->getUserGroup()->getId();
                }
            )->toArray()
        );
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null)
    {
        return $data instanceof AttributeGroupWithAccess;
    }
}
