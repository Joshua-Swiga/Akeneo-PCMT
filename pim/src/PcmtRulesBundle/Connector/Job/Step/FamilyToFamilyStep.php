<?php
/**
 * Copyright (c) 2020, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

declare(strict_types=1);

namespace PcmtRulesBundle\Connector\Job\Step;

use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;
use Akeneo\Pim\Enrichment\Component\Product\Query\Filter\Operators;
use Akeneo\Pim\Enrichment\Component\Product\Query\ProductQueryBuilderFactoryInterface;
use Akeneo\Pim\Structure\Component\Model\FamilyInterface;
use Akeneo\Pim\Structure\Component\Repository\FamilyRepositoryInterface;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use Akeneo\Tool\Component\Batch\Step\AbstractStep;
use PcmtRulesBundle\Service\JobParametersTextCreator;
use PcmtRulesBundle\Service\RuleAttributeProvider;
use PcmtRulesBundle\Service\RuleProcessor;

class FamilyToFamilyStep extends AbstractStep
{
    /** @var RuleAttributeProvider */
    private $attributeProvider;

    /** @var RuleProcessor */
    private $ruleProductProcessor;

    /** @var ProductQueryBuilderFactoryInterface */
    private $pqbFactory;

    /** @var FamilyRepositoryInterface */
    private $familyRepository;

    public const BATCH_SIZE = 20;

    /** @var JobParametersTextCreator */
    private $jobParametersTextCreator;

    public function setAttributeProvider(RuleAttributeProvider $attributeProvider): void
    {
        $this->attributeProvider = $attributeProvider;
    }

    public function setRuleProductProcessor(RuleProcessor $ruleProductProcessor): void
    {
        $this->ruleProductProcessor = $ruleProductProcessor;
    }

    public function setPqbFactory(ProductQueryBuilderFactoryInterface $pqbFactory): void
    {
        $this->pqbFactory = $pqbFactory;
    }

    public function setFamilyRepository(FamilyRepositoryInterface $familyRepository): void
    {
        $this->familyRepository = $familyRepository;
    }

    public function setJobParametersTextCreator(JobParametersTextCreator $jobParametersTextCreator): void
    {
        $this->jobParametersTextCreator = $jobParametersTextCreator;
    }

    protected function doExecute(StepExecution $stepExecution): void
    {
        $parameters = $stepExecution->getJobParameters();

        $stepExecution->addSummaryInfo('parameters', $this->jobParametersTextCreator->create($parameters));

        /** @var FamilyInterface $sourceFamily */
        $sourceFamily = $this->familyRepository->findOneBy(['code' => $parameters->get('sourceFamily')]);
        /** @var FamilyInterface $destinationFamily */
        $destinationFamily = $this->familyRepository->findOneBy(['code' => $parameters->get('destinationFamily')]);

        $attributes = $this->attributeProvider->getAllForFamilies($sourceFamily, $destinationFamily);

        $stepExecution->addSummaryInfo('attributes_found', count($attributes));
        $stepExecution->addSummaryInfo('source_products_found', 0);
        $stepExecution->addSummaryInfo('source_product_models_found', 0);
        $stepExecution->addSummaryInfo('source_products_processed', 0);
        $stepExecution->addSummaryInfo('source_product_models_processed', 0);
        $stepExecution->addSummaryInfo('destination_products_found_and_saved', 0);
        $stepExecution->addSummaryInfo('destination_product_models_found_and_saved', 0);
        $stepExecution->addSummaryInfo('destination_products_created', 0);

        $result = true;
        $offset = 0;
        while ($result) {
            $result = $this->processBatch($stepExecution, $sourceFamily, $destinationFamily, $offset);
            $offset += self::BATCH_SIZE;
        }
    }

    private function processBatch(StepExecution $stepExecution, FamilyInterface $sourceFamily, FamilyInterface $destinationFamily, int $offset): bool
    {
        $count = 0;
        // look in ElasticSearch index
        $pqb = $this->pqbFactory->create([
            'default_locale' => null,
            'default_scope'  => null,
            'limit'          => self::BATCH_SIZE,
            'from'           => $offset,
        ]);
        $pqb->addFilter('family', Operators::IN_LIST, [$sourceFamily->getCode()]);

        $entityCursor = $pqb->execute();

        foreach ($entityCursor as $entity) {
            $count++;
            if ($entity instanceof ProductModelInterface) {
                $stepExecution->incrementSummaryInfo('source_product_models_found', 1);
                $this->processProductModel($stepExecution, $sourceFamily, $destinationFamily, $entity);
            } else {
                $stepExecution->incrementSummaryInfo('source_products_found', 1);
                $this->processProduct($stepExecution, $sourceFamily, $destinationFamily, $entity);
            }
        }

        return $count ? true : false;
    }

    private function processProductModel(StepExecution $stepExecution, FamilyInterface $sourceFamily, FamilyInterface $destinationFamily, ProductModelInterface $productModel): void
    {
        $stepExecution->incrementSummaryInfo('source_product_models_processed', 1);
        $this->ruleProductProcessor->process($stepExecution, $sourceFamily, $destinationFamily, $productModel);
        foreach ($productModel->getProductModels() as $subProductModel) {
            $this->processProductModel($stepExecution, $sourceFamily, $destinationFamily, $subProductModel);
        }
        foreach ($productModel->getProducts() as $productVariant) {
            $this->processProduct($stepExecution, $sourceFamily, $destinationFamily, $productVariant);
        }
    }

    private function processProduct(StepExecution $stepExecution, FamilyInterface $sourceFamily, FamilyInterface $destinationFamily, ProductInterface $product): void
    {
        $stepExecution->incrementSummaryInfo('source_products_processed', 1);
        $this->ruleProductProcessor->process($stepExecution, $sourceFamily, $destinationFamily, $product);
    }
}
