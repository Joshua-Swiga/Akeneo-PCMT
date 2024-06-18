<?php
/**
 * Copyright (c) 2020, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

declare(strict_types=1);

namespace PcmtDraftBundle\Normalizer;

class ProductViolationWithLabelNormalizer extends ProductViolationNormalizer
{
    /**
     * {@inheritdoc}
     */
    public function normalize($violation, $format = null, array $context = []): array
    {
        $return = parent::normalize($violation, $format, $context);
        if (isset($return['attribute']) && !in_array($return['attribute'], ['family'])) {
            $attribute = $this->attributeRepository->findOneByIdentifier($return['attribute']);

            $return['attribute'] = $attribute->getLabel();
            $return['attributeCode'] = $attribute->getCode();
        }

        return $return;
    }
}
