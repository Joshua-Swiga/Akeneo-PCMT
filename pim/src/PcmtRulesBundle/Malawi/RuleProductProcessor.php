<?php
/*
 * Copyright (c) 2020, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

declare(strict_types=1);

/*
 * Copyright (c) 2020, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

namespace PcmtRulesBundle\Malawi;

use Akeneo\Pim\Enrichment\Component\Product\Builder\ProductBuilderInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\EntityWithValuesInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModel;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;
use Akeneo\Pim\Enrichment\Component\Product\Updater\ProductModelUpdater;
use Akeneo\Tool\Component\StorageUtils\Saver\SaverInterface;
use PcmtRulesBundle\Service\RuleAttributeProvider;
use PcmtRulesBundle\Service\RuleProcessorCopier;
use PcmtRulesBundle\Value\AttributeMapping;
use PcmtRulesBundle\Value\AttributeMappingCollection;
use Ramsey\Uuid\Uuid;

class RuleProductProcessor
{
    /** @var RuleAttributeProvider */
    private $ruleAttributeProvider;

    /** @var SaverInterface */
    private $productSaver;

    /** @var ProductBuilderInterface */
    private $variantProductBuilder;

    /** @var RuleProcessorCopier */
    private $ruleProcessorCopier;

    /** @var SaverInterface */
    private $productModelSaver;

    /** @var ProductBuilderInterface */
    private $productModelUpdater;

    public function __construct(
        RuleAttributeProvider $ruleAttributeProvider,
        SaverInterface $productSaver,
        SaverInterface $productModelSaver,
        ProductBuilderInterface $variantProductBuilder,
        ProductModelUpdater $productModelUpdater,
        RuleProcessorCopier $ruleProcessorCopier
    ) {
        $this->ruleAttributeProvider = $ruleAttributeProvider;
        $this->productSaver = $productSaver;
        $this->productModelSaver = $productModelSaver;
        $this->variantProductBuilder = $variantProductBuilder;
        $this->productModelUpdater = $productModelUpdater;
        $this->ruleProcessorCopier = $ruleProcessorCopier;
    }

    public function process(
        array $rule,
        ProductInterface $sourceProduct
    ): void {
        $keyValue = $sourceProduct->getValue($rule['keyAttribute']->getCode());
        if (!$keyValue) {
            return;
        }

        $attributeMapping = $this->getAttributeMapping($rule);
        $associations = $sourceProduct->getAssociations();
        foreach ($associations as $association) {
            $models = $association->getProductModels();
            foreach ($models as $model) {
                /** @var ProductModelInterface $model */
                echo 'Found product model association: ' . $model->getCode() . "\n";

                $this->processDestinationProductModel($attributeMapping, $sourceProduct, $model);
            }
        }
    }

    private function processDestinationProductModel(
        AttributeMappingCollection $attributeMapping,
        ProductInterface $sourceProduct,
        ProductModelInterface $destinationProductModel
    ): void {
        $sourceKeyAttributeValue = $sourceProduct->getValue(RuleProcessStep::KEY_ATTRIBUTE_NAME_FIRST_AXIS);
        echo 'key attribute value: ' . $sourceKeyAttributeValue->getData() . "\n";
        $subProductModels = $destinationProductModel->getProductModels();
        foreach ($subProductModels as $subProductModel) {
            /** @var ProductModelInterface $subProductModel */
            $destinationKeyAttributeValue = $subProductModel->getValue(RuleProcessStep::KEY_ATTRIBUTE_NAME_FIRST_AXIS);

            echo '- found sub product model: ' . $destinationKeyAttributeValue->getData() . "\n";

            if ($sourceKeyAttributeValue->getData() === $destinationKeyAttributeValue->getData()) {
                echo "Matching sub product model exists, copying data.\n";
                $this->copy($attributeMapping, $sourceProduct, $subProductModel);
                $this->processDestinationSubProductModel($attributeMapping, $sourceProduct, $subProductModel);

                return;
            }
        }

        echo "Sub product model does not exist, creating.\n";
        $subProductModel = $this->createNewDestinationProductModel(
            $attributeMapping,
            $sourceProduct,
            $destinationProductModel
        );
        echo 'new dest product model has id: ' . $subProductModel->getId() . "\n";
        $this->processDestinationSubProductModel($attributeMapping, $sourceProduct, $subProductModel);
    }

    private function processDestinationSubProductModel(
        AttributeMappingCollection $attributeMapping,
        ProductInterface $sourceProduct,
        ProductModelInterface $destinationSubProductModel
    ): void {
        $sourceKeyAttributeValue = $sourceProduct->getValue(RuleProcessStep::KEY_ATTRIBUTE_NAME_SECOND_AXIS_SOURCE);
        $destinationProducts = $destinationSubProductModel->getProducts();
        foreach ($destinationProducts as $product) {
            /** @var ProductInterface $product */
            $value = $product->getValue(RuleProcessStep::KEY_ATTRIBUTE_NAME_SECOND_AXIS_DESTINATION);
            echo '- found variant: ' . $value->getData() . "\n";

            if ($sourceKeyAttributeValue->getData() === $value->getData()) {
                echo "Matching variant exists, copying data.\n";
                $this->copy($attributeMapping, $sourceProduct, $product);

                return;
            }
        }

        echo "Variant not exists, creating.\n";
        $this->createNewDestinationProduct($attributeMapping, $sourceProduct, $destinationSubProductModel);
    }

    private function copy(
        AttributeMappingCollection $attributes,
        ProductInterface $sourceProduct,
        EntityWithValuesInterface $destinationProduct
    ): void {
        try {
            $result = $this->ruleProcessorCopier->copy($sourceProduct, $destinationProduct, $attributes);
            if ($result) {
                if ($destinationProduct instanceof ProductInterface) {
                    $this->productSaver->save($destinationProduct);
                } else {
                    $this->productModelSaver->saveAll([$destinationProduct]);
                }
            }
        } catch (\Throwable $e) {
            echo sprintf(
                "- error while copying %s: %s - full: %s\n",
                $sourceProduct->getLabel(),
                $e->getMessage(),
                $e->__toString()
            );
        }
    }

    /*
     * Retrieve the common attributes between the source and destination
     * families designated in the rule parameter.  Return as a simple mapping.
     */
    private function getAttributeMapping(array $rule): AttributeMappingCollection
    {
        $attributes = $this->ruleAttributeProvider->getAllForFamilies(
            $rule['sourceFamily'],
            $rule['destinationFamily']
        );

        return $this->attributeArrayToMappingCollection($attributes);
    }

    /*
     * Turn an array of Attributes, into an AttributeMappingCollection.
     * The mapping is that of source -> destination, so this mapping
     * for Malawi is simply the same attribute - copies from and to the
     * same attribute in different families.
     */
    private function attributeArrayToMappingCollection(array $attributes): AttributeMappingCollection
    {
        $asMappingCollection = new AttributeMappingCollection();
        foreach ($attributes as $att) {
            $asMapping = new AttributeMapping($att, $att);
            $asMappingCollection->add($asMapping);
        }

        return $asMappingCollection;
    }

    private function createNewDestinationProduct(
        AttributeMappingCollection $attributeMapping,
        ProductInterface $sourceProduct,
        ProductModelInterface $productModel
    ): void {
        echo "Creating new dest product...\n";
        $sourceGtin = $sourceProduct->getValue(RuleProcessStep::KEY_ATTRIBUTE_NAME_SECOND_AXIS_SOURCE);
        echo sprintf("... Source GTIN: %s\n", $sourceGtin);

        $destinationProduct = $this->variantProductBuilder->createProduct(
            Uuid::uuid4()->toString(),
            $productModel->getFamily()->getCode()
        );

        // add additional mapping to KNOW_GTIN in dest
        $sourceKeyAtt = $this->ruleAttributeProvider->getAttributeByCode(RuleProcessStep::KEY_ATTRIBUTE_NAME_SECOND_AXIS_SOURCE);
        $destKeyAtt = $this->ruleAttributeProvider->getAttributeByCode(RuleProcessStep::KEY_ATTRIBUTE_NAME_SECOND_AXIS_DESTINATION);
        $attributeMapping->add(new AttributeMapping($sourceKeyAtt, $destKeyAtt));

        $variants = $productModel->getFamily()->getFamilyVariants();
        $variant = $variants->first();
        $destinationProduct->setFamilyVariant($variant);
        $destinationProduct->setParent($productModel);
        $this->copy($attributeMapping, $sourceProduct, $destinationProduct);
    }

    private function createNewDestinationProductModel(
        AttributeMappingCollection $attributeMapping,
        ProductInterface $sourceProduct,
        ProductModelInterface $productModel
    ): ProductModelInterface {
        $subProductModel = new ProductModel();
        $subProductModel->setCreated(new \DateTime());
        $subProductModel->setUpdated(new \DateTime());

        $data = [
            'code' => Uuid::uuid4()->toString(),
        ];

        $this->productModelUpdater->update($subProductModel, $data);

        $variants = $productModel->getFamily()->getFamilyVariants();
        $variant = $variants->first();
        $subProductModel->setFamilyVariant($variant);
        $subProductModel->setParent($productModel);
        $this->copy($attributeMapping, $sourceProduct, $subProductModel);
        $productModel->addProductModel($subProductModel);

        return $subProductModel;
    }
}
