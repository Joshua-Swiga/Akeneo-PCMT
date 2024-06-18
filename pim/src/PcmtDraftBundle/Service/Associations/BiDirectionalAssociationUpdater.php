<?php

declare(strict_types=1);

/*
 * Copyright (c) 2020, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

namespace PcmtDraftBundle\Service\Associations;

use Akeneo\Pim\Enrichment\Component\Product\Model\AssociationInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\EntityWithValuesInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;
use PcmtDraftBundle\Entity\DraftInterface;
use PcmtDraftBundle\Service\Draft\GeneralObjectFromDraftCreator;

class BiDirectionalAssociationUpdater
{
    /** @var GeneralObjectFromDraftCreator */
    private $generalObjectFromDraftCreator;

    /** @var AssociationThroughDraftAdding */
    private $associationThroughDraftAdding;

    /** @var AssociationThroughDraftRemoving */
    private $associationThroughDraftRemoving;

    public function __construct(
        GeneralObjectFromDraftCreator $generalObjectFromDraftCreator,
        AssociationThroughDraftAdding $associationThroughDraftAdding,
        AssociationThroughDraftRemoving $associationThroughDraftRemoving
    ) {
        $this->generalObjectFromDraftCreator = $generalObjectFromDraftCreator;
        $this->associationThroughDraftAdding = $associationThroughDraftAdding;
        $this->associationThroughDraftRemoving = $associationThroughDraftRemoving;
    }

    public function addNewAssociations(DraftInterface $draft, array $associationData): void
    {
        $object = $this->generalObjectFromDraftCreator->getObjectToCompare($draft);

        $associations = $object->getAllAssociations();
        foreach ($associations as $association) {
            $associationType = $association->getAssociationType();
            $products = $association->getProducts();
            foreach ($products as $product) {
                /** @var ProductInterface $product */
                if (!empty($associationData[$associationType->getCode()]['products_bi_directional'])) {
                    if (in_array($product->getIdentifier(), $associationData[$associationType->getCode()]['products_bi_directional'])) {
                        $this->associationThroughDraftAdding->add($object, $product, $associationType);
                    }
                }
            }

            $productModels = $association->getProductModels();
            foreach ($productModels as $productModel) {
                /** @var ProductModelInterface $productModel */
                if (!empty($associationData[$associationType->getCode()]['product_models_bi_directional'])) {
                    if (in_array($productModel->getCode(), $associationData[$associationType->getCode()]['product_models_bi_directional'])) {
                        $this->associationThroughDraftAdding->add($object, $productModel, $associationType);
                    }
                }
            }
        }
    }

    public function removeAssociations(DraftInterface $draft): void
    {
        $objectAfterChanges = $this->generalObjectFromDraftCreator->getObjectToCompare($draft);

        $originalObject = $draft->getObject();
        $originalAssociations = $originalObject->getAllAssociations();
        foreach ($originalAssociations as $originalAssociation) {
            $associationType = $originalAssociation->getAssociationType();
            $associationAfterChanges = $objectAfterChanges->getAssociationForType($associationType);

            $originalAssociatedProducts = $originalAssociation->getProducts();
            foreach ($originalAssociatedProducts as $originalAssociatedProduct) {
                if (!$this->checkIfObjectStillExists($originalAssociatedProduct, $associationAfterChanges)) {
                    $this->associationThroughDraftRemoving->remove($originalObject, $originalAssociatedProduct, $associationType);
                }
            }

            $originalAssociatedProductModels = $originalAssociation->getProductModels();
            foreach ($originalAssociatedProductModels as $originalAssociatedProductModel) {
                if (!$this->checkIfObjectStillExists($originalAssociatedProductModel, $associationAfterChanges)) {
                    $this->associationThroughDraftRemoving->remove($originalObject, $originalAssociatedProductModel, $associationType);
                }
            }
        }
    }

    private function checkIfObjectStillExists(EntityWithValuesInterface $originalObject, AssociationInterface $associationAfterChanges): bool
    {
        $products = $associationAfterChanges->getProducts();
        foreach ($products as $product) {
            if ($product->getId() === $originalObject->getId()) {
                return true;
            }
        }

        $productModels = $associationAfterChanges->getProductModels();
        foreach ($productModels as $productModel) {
            if ($productModel->getId() === $originalObject->getId()) {
                return true;
            }
        }

        return false;
    }
}