<?php
/**
 * Copyright (c) 2020, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

declare(strict_types=1);

namespace PcmtCoreBundle\Normalizer\Standard;

use PcmtCoreBundle\Entity\ReferenceData\CountryCode;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class CountryCodeNormalizer implements NormalizerInterface
{
    /**
     * {@inheritdoc}
     */
    public function normalize($object, $format = null, array $context = [])
    {
        /** @var CountryCode $object */
        return [
            'code'   => $object->getCode(),
            'labels' => [
                'en_US' => $object->getName(),
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null)
    {
        return $data instanceof CountryCode;
    }
}