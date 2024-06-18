<?php
/**
 * Copyright (c) 2019, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

declare(strict_types=1);

namespace PcmtCoreBundle\Normalizer\Standard;

use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use PcmtCoreBundle\Entity\ConcatenatedProperty;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class ConcatenatedTypeNormalizer implements NormalizerInterface
{
    /**
     * {@inheritdoc}
     */
    public function normalize($attribute, $format = null, array $context = []): array
    {
        /** @var AttributeInterface $attribute */
        $property = new ConcatenatedProperty();
        $property->updateFromAttribute($attribute);

        $output = [];

        $attributeCodes = $property->getAttributeCodes();
        if ($attributeCodes) {
            for ($i = 1; $i <= count($attributeCodes); $i++) {
                $prefix = 'attribute' . (string) $i;
                $output[$prefix] = $attributeCodes[$i - 1];
            }
        }

        $separators = $property->getSeparators();
        if ($separators) {
            for ($i = 1; $i <= count($separators); $i++) {
                $prefix = 'separator' . (string) $i;
                $output[$prefix] = $separators[$i - 1];
            }
        }

        return $output;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null): bool
    {
        return $data instanceof AttributeInterface;
    }
}