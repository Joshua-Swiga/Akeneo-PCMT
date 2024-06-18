<?php
/**
 * Copyright (c) 2020, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

declare(strict_types=1);

namespace PcmtCoreBundle\ArrayConverter\FlatToStandard;

use Akeneo\Pim\Enrichment\Component\Product\Connector\ArrayConverter\FlatToStandard\Product as AkeneoProduct;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Pim\Structure\Component\Repository\AttributeRepositoryInterface;
use Akeneo\Tool\Component\Connector\ArrayConverter\ArrayConverterInterface;
use PcmtCoreBundle\Extension\ConcatenatedAttribute\Structure\Component\AttributeType\PcmtAtributeTypes;

class Product implements ArrayConverterInterface
{
    /** @var AkeneoProduct */
    private $pimProductConverter;

    /** @var AttributeRepositoryInterface */
    private $attributeRepository;

    public function __construct(
        AkeneoProduct $pimProductConverter,
        AttributeRepositoryInterface $attributeRepository
    ) {
        $this->pimProductConverter = $pimProductConverter;
        $this->attributeRepository = $attributeRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function convert(array $item, array $options = []): array
    {
        $item = $this->removeConcatenated($item);

        return $this->pimProductConverter->convert($item, $options);
    }

    protected function removeConcatenated(array $item): array
    {
        $attributes = $this->attributeRepository->findBy(['type' => PcmtAtributeTypes::CONCATENATED_FIELDS]);

        foreach ($attributes as $attribute) {
            /** @var AttributeInterface $attribute */
            if (isset($item[$attribute->getCode()])) {
                unset($item[$attribute->getCode()]);
            }
        }

        return $item;
    }
}