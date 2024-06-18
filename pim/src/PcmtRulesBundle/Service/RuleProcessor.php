<?php

declare(strict_types=1);

/*
 * Copyright (c) 2020, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 *
 * This a rule processor for F2F mapping ("Family copy by attribute")
 */

namespace PcmtRulesBundle\Service;

use Akeneo\Pim\Enrichment\Component\Product\Builder\ProductBuilderInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\EntityWithValuesInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;
use Akeneo\Pim\Enrichment\Component\Product\Query\Filter\Operators;
use Akeneo\Pim\Enrichment\Component\Product\Query\ProductQueryBuilderFactoryInterface;
use Akeneo\Pim\Structure\Component\Model\FamilyInterface;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use Akeneo\Tool\Component\StorageUtils\Saver\SaverInterface;
use PcmtRulesBundle\Value\AttributeMappingCollection;
use PcmtSharedBundle\Connector\Job\InvalidItems\SimpleInvalidItem;
use Ramsey\Uuid\Uuid;

class RuleProcessor
{
    public const MAX_DESTINATION_PRODUCTS = 100000;

    /** @var ProductQueryBuilderFactoryInterface */
    private $pqbFactory;

    /** @var SaverInterface */
    private $productSaver;

    /** @var SaverInterface */
    private $productModelSaver;

    /** @var ProductBuilderInterface */
    private $productBuilder;

    /** @var ProductInterface[] */
    private $productsToSave = [];

    /** @var int[] */
    private $destinationProductsId = [];

    /** @var ProductModelInterface[] */
    private $productModelsToSave = [];

    /** @var int[] */
    private $destinationProductModelsId = [];

    /** @var RuleProcessorCopier */
    private $ruleProcessorCopier;

    /** @var AttributeMappingGenerator */
    private $attributeMappingGenerator;

    public function __construct(
        ProductQueryBuilderFactoryInterface $pqbFactory,
        SaverInterface $productSaver,
        SaverInterface $productModelSaver,
        ProductBuilderInterface $productBuilder,
        RuleProcessorCopier $ruleProcessorCopier,
        AttributeMappingGenerator $attributeMappingGenerator
    ) {
        $this->pqbFactory = $pqbFactory;
        $this->productSaver = $productSaver;
        $this->productModelSaver = $productModelSaver;
        $this->productBuilder = $productBuilder;
        $this->ruleProcessorCopier = $ruleProcessorCopier;
        $this->attributeMappingGenerator = $attributeMappingGenerator;
    }

    public function process(StepExecution $stepExecution, FamilyInterface $sourceFamily, FamilyInterface $destinationFamily, EntityWithValuesInterface $sourceProduct): void
    {
        $this->productsToSave = [];
        $this->productModelsToSave = [];

        $mappings = $this->attributeMappingGenerator->get(
            $sourceFamily,
            $destinationFamily,
            $stepExecution->getJobParameters()->get('attributeMapping')
        );

        $keyAttributeCode = $stepExecution->getJobParameters()->get('keyAttribute');

        $keyAttributeMapping = $this->attributeMappingGenerator->getKeyAttributesMapping(
            $keyAttributeCode['sourceKeyAttribute'],
            $keyAttributeCode['destinationKeyAttribute']
        );

        // add key attribute to the rest of mappings
        $mappings->add($keyAttributeMapping);

        $keyValue = $sourceProduct->getValue($keyAttributeMapping->getSourceAttribute()->getCode());
        if (!$keyValue) {
            return;
        }

        // searching through ElasticSearch index
        $pqb = $this->pqbFactory->create([
            'default_locale' => null,
            'default_scope'  => null,
            'limit'          => self::MAX_DESTINATION_PRODUCTS,
        ]);
        try {
            $pqb->addFilter($keyAttributeMapping->getDestinationAttribute()->getCode(), Operators::EQUALS, $keyValue->getData());
        } catch (\Throwable $e) {
            try {
                $pqb->addFilter($keyAttributeMapping->getDestinationAttribute()->getCode(), Operators::IN_LIST, [$keyValue->getData()]);
            } catch (\Throwable $e) {
                throw new \Exception('Unsupported attribute type used as key attribute in a rule: ' . $keyAttributeCode['destinationKeyAttribute']);
            }
        }
        $pqb->addFilter('family', Operators::IN_LIST, [$destinationFamily->getCode()]);

        $destinationProducts = $pqb->execute();
        foreach ($destinationProducts as $destinationProduct) {
            $this->copy($sourceProduct, $destinationProduct, $mappings, $stepExecution);
        }

        if (0 === count($destinationProducts) && 0 === count($destinationFamily->getFamilyVariants())) {
            $this->createNewDestinationProduct($sourceProduct, $destinationFamily, $mappings, $stepExecution);

            foreach ($this->productsToSave as $product) {
                $this->productSaver->save($product);
            }

            $stepExecution->incrementSummaryInfo('destination_products_created', 1);
        } else {
            foreach ($this->productsToSave as $id => $product) {
                $this->productSaver->save($product);
                if (empty($this->destinationProductsId[$id])) {
                    $stepExecution->incrementSummaryInfo('destination_products_found_and_saved', 1);
                    $this->destinationProductsId[$id] = $id;
                }
            }
            foreach ($this->productModelsToSave as $id => $productModel) {
                $this->productModelSaver->save($productModel);
                if (empty($this->destinationProductModelsId[$id])) {
                    $stepExecution->incrementSummaryInfo('destination_product_models_found_and_saved', 1);
                    $this->destinationProductModelsId[$id] = $id;
                }
            }
        }
    }

    private function copy(
        EntityWithValuesInterface $sourceProduct,
        EntityWithValuesInterface $destinationProduct,
        AttributeMappingCollection $attributeMappingCollection,
        StepExecution $stepExecution
    ): void {
        try {
            $result = $this->ruleProcessorCopier->copy($sourceProduct, $destinationProduct, $attributeMappingCollection);
            if ($result) {
                $this->addEntityToSave($destinationProduct);
            }

            if ($destinationProduct instanceof ProductModelInterface) {
                foreach ($destinationProduct->getProductModels() as $productModel) {
                    $this->copy($sourceProduct, $productModel, $attributeMappingCollection, $stepExecution);
                }
                foreach ($destinationProduct->getProducts() as $product) {
                    $this->copy($sourceProduct, $product, $attributeMappingCollection, $stepExecution);
                }
            }
        } catch (\Throwable $e) {
            $msg = sprintf(
                'Problem with copying data from product %s to product %s. Error: %s',
                $sourceProduct->getId(),
                $destinationProduct->getId(),
                $e->getMessage()
            );
            $invalidItem = new SimpleInvalidItem(
                [
                    'sourceProduct'      => $sourceProduct->getId(),
                    'destinationProduct' => $destinationProduct->getId(),
                ]
            );
            $stepExecution->addWarning($msg, [], $invalidItem);
        }
    }

    private function addEntityToSave(EntityWithValuesInterface $entity): void
    {
        if ($entity instanceof ProductInterface) {
            $this->productsToSave[$entity->getId()] = $entity;
        } elseif ($entity instanceof ProductModelInterface) {
            $this->productModelsToSave[$entity->getId()] = $entity;
        }
    }

    private function createNewDestinationProduct(
        EntityWithValuesInterface $sourceEntity,
        FamilyInterface $destinationFamily,
        AttributeMappingCollection $attributeMappingCollection,
        StepExecution $stepExecution
    ): void {
        $destinationProduct = $this->productBuilder->createProduct(
            Uuid::uuid4()->toString(),
            $destinationFamily->getCode()
        );

        $this->addEntityToSave($destinationProduct);

        $this->copy($sourceEntity, $destinationProduct, $attributeMappingCollection, $stepExecution);
    }
}
