<?php
/**
 * Copyright (c) 2019, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

declare(strict_types=1);

namespace PcmtCoreBundle\Normalizer\Standard;

use Akeneo\Pim\Structure\Component\Normalizer\Standard\AttributeNormalizer as BaseAttributeNormalizer;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class AttributeNormalizer extends BaseAttributeNormalizer
{
    /** @var NormalizerInterface */
    private $concatenatedNormalizer;

    /** @var NormalizerInterface */
    private $descriptionNormalizer;

    public function setConcatenatedNormalizer(NormalizerInterface $concatenatedNormalizer): void
    {
        $this->concatenatedNormalizer = $concatenatedNormalizer;
    }

    public function setDescriptionNormalizer(NormalizerInterface $descriptionNormalizer): void
    {
        $this->descriptionNormalizer = $descriptionNormalizer;
    }

    /**
     * {@inheritdoc}
     */
    public function normalize($attribute, $format = null, array $context = [])
    {
        $normalizedData = parent::normalize($attribute, $format, $context);
        $normalizedData['descriptions'] = $this->descriptionNormalizer->normalize($attribute, $format, $context);
        $normalizedData['concatenated'] = $this->concatenatedNormalizer->normalize($attribute);

        return $normalizedData;
    }
}
