<?php
/**
 * Copyright (c) 2020, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

declare(strict_types=1);

namespace PcmtDraftBundle\Service\Draft;

use Akeneo\Pim\Enrichment\Component\Product\Comparator\Filter\FilterInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\EntityWithValuesInterface;
use Akeneo\Pim\Structure\Component\Repository\AttributeRepositoryInterface;

class DraftValuesWithMissingAttributeFilter implements FilterInterface
{
    /** @var AttributeRepositoryInterface */
    private $attributeRepository;

    public function __construct(AttributeRepositoryInterface $attributeRepository)
    {
        $this->attributeRepository = $attributeRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function filter(EntityWithValuesInterface $entity, array $newValues): array
    {
        $attributesCodes = $this->attributeRepository->getAttributeTypeByCodes(
            array_keys($newValues)
        );

        $result = [];

        foreach ($newValues as $code => $value) {
            if (!isset($attributesCodes[$code])) {
                continue;
            }

            $result[$code] = $value;
        }

        return $result;
    }
}