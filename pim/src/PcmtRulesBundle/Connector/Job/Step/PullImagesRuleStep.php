<?php
/**
 * Copyright (c) 2021, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

declare(strict_types=1);

namespace PcmtRulesBundle\Connector\Job\Step;

use Akeneo\Pim\Enrichment\Component\Product\Model\EntityWithValuesInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;
use Akeneo\Pim\Enrichment\Component\Product\Query\Filter\Operators;
use Akeneo\Pim\Enrichment\Component\Product\Query\ProductQueryBuilderFactoryInterface;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use Akeneo\Tool\Component\Batch\Step\AbstractStep;
use PcmtRulesBundle\Service\AttributesLevelValidator;
use PcmtRulesBundle\Service\PullImageService;
use PcmtRulesBundle\Service\UpdateImageService;
use PcmtSharedBundle\Connector\Job\InvalidItems\SimpleInvalidItem;

class PullImagesRuleStep extends AbstractStep
{
    public const PARAM_FAMILY = 'family';

    public const PARAM_SOURCE_ATTRIBUTE = 'sourceAttribute';

    public const PARAM_DESTINATION_ATTRIBUTE = 'destinationAttribute';

    /** @var AttributesLevelValidator */
    private $attributesLevelValidator;

    /** @var PullImageService */
    private $pullImageService;

    /** @var UpdateImageService */
    private $productUpdateImageService;

    /** @var UpdateImageService */
    private $productModelUpdateImageService;

    /** @var ProductQueryBuilderFactoryInterface */
    private $pqbFactory;

    /** @var StepExecution */
    private $stepExecution;

    public function setPqbFactory(ProductQueryBuilderFactoryInterface $pqbFactory): void
    {
        $this->pqbFactory = $pqbFactory;
    }

    public function setPullImageService(PullImageService $pullImageService): void
    {
        $this->pullImageService = $pullImageService;
    }

    public function setProductUpdateImageService(UpdateImageService $productUpdateImageService): void
    {
        $this->productUpdateImageService = $productUpdateImageService;
    }

    public function setProductModelUpdateImageService(UpdateImageService $productModelUpdateImageService): void
    {
        $this->productModelUpdateImageService = $productModelUpdateImageService;
    }

    public function setAttributesLevelValidator(AttributesLevelValidator $attributesLevelValidator): void
    {
        $this->attributesLevelValidator = $attributesLevelValidator;
    }

    protected function doExecute(StepExecution $stepExecution): void
    {
        $this->stepExecution = $stepExecution;

        $jobParameters = $stepExecution->getJobParameters();

        $text = [];
        foreach ($jobParameters->all() as $key => $value) {
            $text[] = $key.' : '. $value;
        }
        $stepExecution->addSummaryInfo('parameters', implode(', ', $text));

        $family = $jobParameters->get(self::PARAM_FAMILY);

        // finding source products / product models
        $pqb = $this->pqbFactory->create([
            'default_locale' => null,
            'default_scope'  => null,
            'limit'          => 10000,
        ]);

        $pqb->addFilter('family', Operators::IN_LIST, [$family]);
        $entityCursor = $pqb->execute();

        $this->pullImageService->setStepExecution($stepExecution);
        $this->pullImageService->setSourceAttributeCode($jobParameters->get(self::PARAM_SOURCE_ATTRIBUTE));
        $this->pullImageService->setDestinationAttributeCode($jobParameters->get(self::PARAM_DESTINATION_ATTRIBUTE));

        $this->productUpdateImageService->setDestinationAttributeCode($jobParameters->get(self::PARAM_DESTINATION_ATTRIBUTE));
        $this->productModelUpdateImageService->setDestinationAttributeCode($jobParameters->get(self::PARAM_DESTINATION_ATTRIBUTE));

        $this->stepExecution->addSummaryInfo('entities_found', 0);
        $this->stepExecution->addSummaryInfo('entities_processed', 0);
        $this->stepExecution->addSummaryInfo('source_urls_found', 0);
        $this->stepExecution->addSummaryInfo('files_downloaded', 0);
        $this->stepExecution->addSummaryInfo('entities_updated', 0);

        foreach ($entityCursor as $entity) {
            $this->processEntity($entity);
        }
    }

    private function processEntity(EntityWithValuesInterface $entity): void
    {
        if ($entity instanceof ProductModelInterface) {
            foreach ($entity->getProductModels() as $subProductModel) {
                $this->processEntity($subProductModel);
            }
            foreach ($entity->getProducts() as $subProduct) {
                $this->processEntity($subProduct);
            }
        }

        $this->stepExecution->incrementSummaryInfo('entities_found', 1);

        $jobParameters = $this->stepExecution->getJobParameters();
        $attributeCodes = [
            $jobParameters->get(self::PARAM_SOURCE_ATTRIBUTE),
            $jobParameters->get(self::PARAM_DESTINATION_ATTRIBUTE),
        ];

        if (!$this->attributesLevelValidator->validate($entity, $attributeCodes)) {
            return;
        }
        try {
            $this->stepExecution->incrementSummaryInfo('entities_processed', 1);
            $file = $this->pullImageService->processEntity($entity);
        } catch (\Throwable $e) {
            $msg = sprintf(
                'Problem with pulling image from url for entity %s. Error: %s',
                $entity->getLabel(),
                $e->getMessage()
            );
            $invalidItem = new SimpleInvalidItem(
                [
                    'entity'      => $entity->getId(),
                ]
            );
            $this->stepExecution->addWarning($msg, [], $invalidItem);
        }
        if (!empty($file)) {
            try {
                $this->stepExecution->incrementSummaryInfo('files_downloaded', 1);
                if ($entity instanceof ProductInterface) {
                    $this->productUpdateImageService->processEntity($entity, $file);
                } else {
                    $this->productModelUpdateImageService->processEntity($entity, $file);
                }
                $this->stepExecution->incrementSummaryInfo('entities_updated', 1);
            } catch (\Throwable $e) {
                $msg = sprintf(
                    'Problem with updating image entity %s. Error: %s',
                    $entity->getLabel(),
                    $e->getMessage()
                );
                $invalidItem = new SimpleInvalidItem(
                    [
                        'entity'      => $entity->getId(),
                    ]
                );
                $this->stepExecution->addWarning($msg, [], $invalidItem);
            }
        }
    }
}