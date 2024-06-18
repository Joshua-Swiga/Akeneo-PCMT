<?php
/**
 * Copyright (c) 2019, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

declare(strict_types=1);

namespace PcmtDraftBundle\Normalizer;

use PcmtDraftBundle\Entity\AttributeChange;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class AttributeChangeNormalizer implements NormalizerInterface
{
    /**
     * {@inheritdoc}
     */
    public function normalize($change, $format = null, array $context = []): array
    {
        $data = [];
        $data['attribute'] = $change->getAttributeName();
        $data['previousValue'] = $change->getPreviousValue();
        $data['newValue'] = $change->getNewValue();

        return $data;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null, array $context = []): bool
    {
        return $data instanceof AttributeChange;
    }
}