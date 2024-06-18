<?php

/**
 * Copyright (c) 2020, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

declare(strict_types=1);

namespace PcmtDraftBundle\Service\Draft;

use Akeneo\Pim\Enrichment\Component\Product\Model\EntityWithFamilyVariantInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModel;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;
use Akeneo\Pim\Enrichment\Component\Product\Updater\ProductModelUpdater;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class BaseProductModelCreatorForDraft implements BaseEntityCreatorInterface
{
    /** @var ProductModelUpdater */
    private $productModelUpdater;

    /** @var NormalizerInterface */
    private $standardNormalizer;

    public function __construct(ProductModelUpdater $productModelUpdater, NormalizerInterface $standardNormalizer)
    {
        $this->productModelUpdater = $productModelUpdater;
        $this->standardNormalizer = $standardNormalizer;
    }

    /**
     * {@inheritdoc}
     */
    public function create(EntityWithFamilyVariantInterface $processedProductModel)
    {
        $data = [
            'code'           => $processedProductModel->getCode(),
            'family_variant' => $processedProductModel->getFamilyVariant()->getCode(),
        ];

        if (!$processedProductModel->isRoot()) {
            $data = $this->fillRequiredDataForAxis($processedProductModel, $data);
        }

        $newProductModel = new ProductModel();

        $this->productModelUpdater->update($newProductModel, $data);

        return $newProductModel;
    }

    private function fillRequiredDataForAxis(ProductModelInterface $productModel, array $data): array
    {
        $values = $this->standardNormalizer->normalize($productModel->getValues(), 'standard');
        $data['parent'] = $productModel->getParent()->getCode();

        $attributeSet = $productModel->getFamilyVariant()->getVariantAttributeSet(1);
        $axesAttributes = $attributeSet->getAxes();
        foreach ($axesAttributes as $attribute) {
            $code = $attribute->getCode();
            $data['values'][$code] = $values[$code];
        }

        return $data;
    }
}