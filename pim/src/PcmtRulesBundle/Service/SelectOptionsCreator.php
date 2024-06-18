<?php

declare(strict_types=1);

/*
 * Copyright (c) 2020, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

namespace PcmtRulesBundle\Service;

use Akeneo\Channel\Bundle\Doctrine\Repository\LocaleRepository;
use Akeneo\Pim\Enrichment\Component\Product\Model\EntityWithValuesInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;
use Akeneo\Pim\Enrichment\Component\Product\Query\Filter\Operators;
use Akeneo\Pim\Enrichment\Component\Product\Query\ProductQueryBuilderFactoryInterface;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Pim\Structure\Component\Model\AttributeOption;
use Akeneo\Pim\Structure\Component\Model\AttributeOptionValue;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use Akeneo\Tool\Component\StorageUtils\Factory\SimpleFactoryInterface;
use Akeneo\Tool\Component\StorageUtils\Saver\SaverInterface;

class SelectOptionsCreator
{
    /** @var SaverInterface */
    protected $optionSaver;

    /** @var ProductQueryBuilderFactoryInterface */
    private $pqbFactory;

    /** @var SimpleFactoryInterface */
    protected $optionFactory;

    /** @var LocaleRepository */
    private $localeRepository;

    /** @var string[] */
    private $localeCodes = [];

    /** @var string */
    private $usedCodes = [];

    /** @var AttributeInterface */
    private $destinationAttribute;

    /** @var AttributeInterface */
    private $attributeValue;

    /** @var StepExecution */
    private $stepExecution;

    /** @var string */
    private $attributeCodeForCode;

    public function __construct(
        ProductQueryBuilderFactoryInterface $pqbFactory,
        LocaleRepository $localeRepository,
        SimpleFactoryInterface $optionFactory,
        SaverInterface $optionSaver
    ) {
        $this->pqbFactory = $pqbFactory;
        $this->localeRepository = $localeRepository;
        $this->optionFactory = $optionFactory;
        $this->optionSaver = $optionSaver;
    }

    public function create(
        StepExecution $stepExecution,
        string $sourceFamilyCode,
        string $attributeCodeForCode,
        AttributeInterface $destinationAttribute,
        AttributeInterface $attributeForValue
    ): void {
        $this->stepExecution = $stepExecution;
        $this->attributeCodeForCode = $attributeCodeForCode;
        $this->destinationAttribute = $destinationAttribute;
        $this->attributeValue = $attributeForValue;

        $this->localeCodes = $this->localeRepository->getActivatedLocaleCodes();

        // finding source products / product models
        $pqb = $this->pqbFactory->create([
            'default_locale' => null,
            'default_scope'  => null,
            'limit'          => 10000,
        ]);

        $pqb->addFilter('family', Operators::IN_LIST, [$sourceFamilyCode]);
        $entityCursor = $pqb->execute();

        // creating new options
        foreach ($entityCursor as $entity) {
            if ($entity instanceof ProductModelInterface) {
                $this->processProductModel($entity);
            } else {
                $this->processProduct($entity);
            }
        }
    }

    private function processProductModel(ProductModelInterface $productModel): void
    {
        $this->stepExecution->incrementSummaryInfo('source_product_models_found', 1);
        $this->processEntity($productModel);
        foreach ($productModel->getProductModels() as $pm) {
            $this->processProductModel($pm);
        }
        foreach ($productModel->getProducts() as $p) {
            $this->processProduct($p);
        }
    }

    private function processProduct(ProductInterface $product): void
    {
        $this->stepExecution->incrementSummaryInfo('source_products_found', 1);
        $this->processEntity($product);
    }

    private function processEntity(EntityWithValuesInterface $entity): void
    {
        // check if attributes for code and value are on this variation level
        $codes = $entity->getValuesForVariation()->getAttributeCodes();
        if (!in_array($this->attributeCodeForCode, $codes) || !in_array($this->attributeValue->getCode(), $codes)) {
            return;
        }

        $code = $entity->getValue($this->attributeCodeForCode);
        if (!$code || !$code->getData()) {
            return;
        }
        $code = $code->getData();
        if (!empty($this->usedCodes[$code])) {
            return;
        }

        /** @var AttributeOption $option */
        $option = $this->optionFactory->create();
        $option->setAttribute($this->destinationAttribute);
        $option->setCode($code);

        foreach ($this->localeCodes as $localeCode) {
            $value = $entity->getValue(
                $this->attributeValue->getCode(),
                $this->attributeValue->isLocalizable() ? $localeCode : null
            );

            if ($value && $value->getData()) {
                $attributeOptionValue = new AttributeOptionValue();
                $attributeOptionValue->setLocale($localeCode);
                $attributeOptionValue->setValue($value->getData());
                $option->addOptionValue($attributeOptionValue);
            }
        }

        if (0 === $option->getOptionValues()->count()) {
            // if there is no value defined, ignore this option.
            return;
        }

        $this->optionSaver->save($option);
        $this->usedCodes[$code] = $code;
        $this->stepExecution->incrementSummaryInfo('options_added', 1);
    }
}