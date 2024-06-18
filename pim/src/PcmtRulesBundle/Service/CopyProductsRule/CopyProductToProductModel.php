<?php
/*
 * Copyright (c) 2021, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

declare(strict_types=1);

/*
 * Copyright (c) 2021, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

namespace PcmtRulesBundle\Service\CopyProductsRule;

use Akeneo\Pim\Enrichment\Component\Product\Builder\ProductBuilderInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\EntityWithValuesInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModel;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Pim\Structure\Component\Model\VariantAttributeSetInterface;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use Akeneo\Tool\Component\StorageUtils\Saver\SaverInterface;
use PcmtRulesBundle\Service\RuleProcessorCopier;
use PcmtRulesBundle\Value\AttributeMapping;
use PcmtRulesBundle\Value\AttributeMappingCollection;
use Ramsey\Uuid\Uuid;

class CopyProductToProductModel
{
    /** @var StepExecution */
    private $stepExecution;

    /** @var SaverInterface */
    private $productSaver;

    /** @var ProductBuilderInterface */
    private $variantProductBuilder;

    /** @var RuleProcessorCopier */
    private $ruleProcessorCopier;

    /** @var SaverInterface */
    private $productModelSaver;

    /** @var SubEntityFinder */
    private $subEntityFinder;

    /** @var AttributeMappingCollection */
    private $attributeMappingCollection;

    public function __construct(
        SaverInterface $productSaver,
        SaverInterface $productModelSaver,
        ProductBuilderInterface $variantProductBuilder,
        RuleProcessorCopier $ruleProcessorCopier,
        SubEntityFinder $subEntityFinder
    ) {
        $this->productSaver = $productSaver;
        $this->productModelSaver = $productModelSaver;
        $this->variantProductBuilder = $variantProductBuilder;
        $this->ruleProcessorCopier = $ruleProcessorCopier;
        $this->subEntityFinder = $subEntityFinder;
    }

    public function process(
        StepExecution $stepExecution,
        ProductInterface $sourceProduct,
        ProductModelInterface $destinationProductModel,
        AttributeMappingCollection $attributeMappingCollection
    ): void {
        $this->attributeMappingCollection = $attributeMappingCollection;
        $this->stepExecution = $stepExecution;
        $this->processDestinationProductModel($sourceProduct, $destinationProductModel, 1);
    }

    private function processDestinationProductModel(
        ProductInterface $sourceProduct,
        ProductModelInterface $destinationProductModel,
        int $level
    ): void {
        $variant = $destinationProductModel->getFamilyVariant();
        $set = $variant->getVariantAttributeSet($level);
        /** @var VariantAttributeSetInterface $set */
        $axisAttributes = $set->getAxes();
        foreach ($axisAttributes as $destinationAttribute) {
            /** @var AttributeInterface $destinationAttribute */
            $sourceAttribute = $this->attributeMappingCollection->getSourceAttributeForDestinationOne($destinationAttribute);
            if ($sourceAttribute) {
                $value = $sourceProduct->getValue($sourceAttribute->getCode());
            }
            if (empty($value) || !$value->getData()) {
                $this->stepExecution->incrementSummaryInfo('axis_attribute_not_exists_in_source_product', 1);

                return;
            }
        }

        $collection = clone$this->attributeMappingCollection;
        foreach ($collection as $key => $attributeMapping) {
            /** @var AttributeMapping $attributeMapping */
            if (!$set->hasAttribute($attributeMapping->getDestinationAttribute())) {
                $collection->remove($key);
            }
        }

        $subEntity = $this->subEntityFinder->findByAxisAttributes($destinationProductModel, $axisAttributes, $sourceProduct);
        if ($subEntity) {
            $this->stepExecution->incrementSummaryInfo('sub_entities_found', 1);

            $this->copy($sourceProduct, $subEntity, $collection);
            if ($subEntity instanceof ProductModelInterface) {
                $this->processDestinationProductModel($sourceProduct, $subEntity, ++$level);
            }
        } else {
            if ($level < $variant->getNumberOfLevel()) {
                $subProductModel = $this->createNewSubProductModel(
                    $sourceProduct,
                    $destinationProductModel,
                    $collection
                );
                $this->stepExecution->incrementSummaryInfo('sub_product_models_created', 1);
                $this->processDestinationProductModel($sourceProduct, $subProductModel, ++$level);
            } else {
                $this->createNewSubProduct(
                    $sourceProduct,
                    $destinationProductModel,
                    $collection
                );
                $this->stepExecution->incrementSummaryInfo('sub_products_created', 1);
            }
        }
    }

    private function copy(
        ProductInterface $sourceProduct,
        EntityWithValuesInterface $destinationEntity,
        AttributeMappingCollection $collection
    ): void {
        foreach ($collection as $key => $attributeMapping) {
            /** @var AttributeMapping $attributeMapping */
            if (!$sourceProduct->getValue($attributeMapping->getSourceAttribute()->getCode())) {
                unset($collection[$key]);
            }
        }

        $result = $this->ruleProcessorCopier->copy($sourceProduct, $destinationEntity, $collection);
        if ($result) {
            if ($destinationEntity instanceof ProductInterface) {
                $this->productSaver->save($destinationEntity);
            } else {
                $this->productModelSaver->save($destinationEntity);
            }
        }
    }

    private function createNewSubProduct(
        ProductInterface $sourceProduct,
        ProductModelInterface $productModel,
        AttributeMappingCollection $attributeMappingCollection
    ): void {
        $destinationProduct = $this->variantProductBuilder->createProduct(
            Uuid::uuid4()->toString(),
            $productModel->getFamily()->getCode()
        );

        $destinationProduct->setFamilyVariant($productModel->getFamilyVariant());
        $destinationProduct->setParent($productModel);
        $this->copy($sourceProduct, $destinationProduct, $attributeMappingCollection);
    }

    private function createNewSubProductModel(
        ProductInterface $sourceProduct,
        ProductModelInterface $productModel,
        AttributeMappingCollection $attributeMappingCollection
    ): ProductModelInterface {
        $subProductModel = new ProductModel();
        $subProductModel->setCreated(new \DateTime());
        $subProductModel->setUpdated(new \DateTime());
        $subProductModel->setCode(Uuid::uuid4()->toString());
        $subProductModel->setFamilyVariant($productModel->getFamilyVariant());
        $subProductModel->setParent($productModel);
        $this->copy($sourceProduct, $subProductModel, $attributeMappingCollection);

        return $subProductModel;
    }
}
