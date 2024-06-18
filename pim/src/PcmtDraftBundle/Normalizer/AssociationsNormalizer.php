<?php

declare(strict_types=1);

/**
 * The class could not extend base class because that one has has private functions
 *
 * Copyright (c) 2020, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

namespace PcmtDraftBundle\Normalizer;

use Akeneo\Pim\Enrichment\Component\Product\Model\EntityWithAssociationsInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\EntityWithFamilyVariantInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * Original normalizer copyrights:
 *
 * @author    Julien Janvier <julien.janvier@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AssociationsNormalizer implements NormalizerInterface
{
    /**
     * {@inheritdoc}
     *
     * @param EntityWithAssociationsInterface $associationAwareEntity
     */
    public function normalize($associationAwareEntity, $format = null, array $context = [])
    {
        $ancestorProducts = $this->getAncestorProducts($associationAwareEntity);

        return $this->normalizeAssociations($ancestorProducts);
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null)
    {
        return $data instanceof EntityWithAssociationsInterface && 'standard' === $format;
    }

    private function getAncestorProducts(EntityWithAssociationsInterface $entityWithFamilyVariant): array
    {
        $parent = null;
        if ($entityWithFamilyVariant instanceof EntityWithFamilyVariantInterface) {
            $parent = $entityWithFamilyVariant->getParent();
        }
        if (null === $parent) {
            return [$entityWithFamilyVariant];
        }

        return array_merge($this->getAncestorProducts($parent), [$entityWithFamilyVariant]);
    }

    private function normalizeAssociations(array $associationAwareEntities): array
    {
        $data = [];

        foreach ($associationAwareEntities as $associationAwareEntity) {
            foreach ($associationAwareEntity->getAssociations() as $association) {
                $code = $association->getAssociationType()->getCode();

                $data[$code]['groups'] = $data[$code]['groups'] ?? [];
                foreach ($association->getGroups() as $group) {
                    $data[$code]['groups'][] = $group->getCode();
                }

                $data[$code]['products'] = $data[$code]['products'] ?? [];
                if ($associationAwareEntity instanceof ProductModelInterface) {
                    foreach ($association->getProducts() as $product) {
                        $data[$code]['products'][] = $product->getReference();
                    }
                } else {
                    foreach ($association->getProducts() as $product) {
                        $data[$code]['products'][] = $product->getIdentifier();
                    }
                }

                $data[$code]['product_models'] = $data[$code]['product_models'] ?? [];
                foreach ($association->getProductModels() as $productModel) {
                    $data[$code]['product_models'][] = $productModel->getCode();
                }
            }
        }

        $data = array_map(function ($association) {
            $association['products'] = array_unique($association['products']);

            return $association;
        }, $data);

        ksort($data);

        return $data;
    }
}
