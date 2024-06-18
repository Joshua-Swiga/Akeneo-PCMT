<?php

/**
 * Copyright (c) 2020, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

declare(strict_types=1);

namespace PcmtDraftBundle\Service\Draft;

use Akeneo\Pim\Enrichment\Component\Product\Builder\ProductBuilderInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\EntityWithFamilyVariantInterface;
use Akeneo\Pim\Enrichment\Component\Product\Updater\ProductUpdater;
use Akeneo\Pim\Structure\Component\Model\VariantAttributeSetInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class BaseProductCreatorForDraft implements BaseEntityCreatorInterface
{
    /** @var ProductUpdater */
    private $productUpdater;

    /** @var NormalizerInterface */
    private $standardNormalizer;

    /** @var ProductBuilderInterface */
    private $productBuilder;

    public function __construct(
        ProductUpdater $productUpdater,
        NormalizerInterface $standardNormalizer,
        ProductBuilderInterface $productBuilder
    ) {
        $this->productUpdater = $productUpdater;
        $this->standardNormalizer = $standardNormalizer;
        $this->productBuilder = $productBuilder;
    }

    /**
     * {@inheritdoc}
     */
    public function create(EntityWithFamilyVariantInterface $product)
    {
        $newProduct = $this->productBuilder->createProduct($product->getIdentifier(), $product->getFamily()->getCode());

        $values = $this->standardNormalizer->normalize($product->getValues(), 'standard');

        $data = [];
        if ($product->hasAttribute('GTIN')) {
            $data['values']['GTIN'] = $values['GTIN'];
        }

        if ($product->getParent()) {
            $newProduct->setParent($product->getParent());
            $newProduct->setFamilyVariant($product->getFamilyVariant());

            foreach ($product->getFamilyVariant()->getVariantAttributeSets() as $set) {
                /** @var VariantAttributeSetInterface $set */
                if (!$attributeSet || $set->getLevel() > $attributeSet->getLevel()) {
                    $attributeSet = $set;
                }
            }

            $axesAttributes = $attributeSet->getAxes();
            foreach ($axesAttributes as $attribute) {
                $code = $attribute->getCode();
                $data['values'][$code] = $values[$code];
            }
        }
        if ($data) {
            $this->productUpdater->update($newProduct, $data);
        }

        return $newProduct;
    }
}