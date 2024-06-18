<?php
/**
 * Copyright (c) 2019, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

declare(strict_types=1);

namespace PcmtCoreBundle\Connector\Job;

use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;
use Akeneo\Pim\Enrichment\Component\Product\Repository\ProductModelRepositoryInterface;
use Akeneo\Pim\Enrichment\Component\Product\Repository\ProductRepositoryInterface;
use Akeneo\Pim\Structure\Component\Repository\AttributeRepositoryInterface;
use Akeneo\Pim\Structure\Component\Repository\FamilyRepositoryInterface;
use Akeneo\Pim\Structure\Component\Repository\FamilyVariantRepositoryInterface;
use Akeneo\Tool\Component\Batch\Job\ExitStatus;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use Akeneo\Tool\Component\Connector\Step\TaskletInterface;
use Akeneo\Tool\Component\StorageUtils\Cache\EntityManagerClearerInterface;
use Akeneo\Tool\Component\StorageUtils\Saver\BulkSaverInterface;
use Akeneo\Tool\Component\StorageUtils\Updater\ObjectUpdaterInterface;

class ComputeConcatenatedAttributeValuesOfProductsTasklet implements TaskletInterface
{
    /**
     * @var int
     */
    private $batchSize = 100;

    /** @var StepExecution */
    private $stepExecution;

    /** @var AttributeRepositoryInterface */
    protected $attributeRepository;

    /** @var ObjectUpdaterInterface */
    protected $concatenatedValuesUpdater;

    /** @var BulkSaverInterface */
    protected $productBulkSaver;

    /** @var BulkSaverInterface */
    protected $productModelBulkSaver;

    /** @var ProductRepositoryInterface */
    protected $productRepository;

    /** @var FamilyRepositoryInterface */
    protected $familyRepository;

    /** @var FamilyVariantRepositoryInterface */
    protected $familyVariantRepository;

    /** @var ProductModelRepositoryInterface */
    protected $productModelRepository;

    /** @var EntityManagerClearerInterface */
    protected $cacheClearer;

    public function __construct(
        AttributeRepositoryInterface $attributeRepository,
        ObjectUpdaterInterface $concatenatedValuesUpdater,
        BulkSaverInterface $productBulkSaver,
        BulkSaverInterface $productModelBulkSaver,
        ProductRepositoryInterface $productRepository,
        FamilyRepositoryInterface $familyRepository,
        FamilyVariantRepositoryInterface $familyVariantRepository,
        ProductModelRepositoryInterface $productModelRepository,
        EntityManagerClearerInterface $cacheClearer
    ) {
        $this->attributeRepository = $attributeRepository;
        $this->concatenatedValuesUpdater = $concatenatedValuesUpdater;
        $this->productBulkSaver = $productBulkSaver;
        $this->productModelBulkSaver = $productModelBulkSaver;
        $this->productRepository = $productRepository;
        $this->familyRepository = $familyRepository;
        $this->familyVariantRepository = $familyVariantRepository;
        $this->productModelRepository = $productModelRepository;
        $this->cacheClearer = $cacheClearer;
    }

    public function execute(): void
    {
        $this->updateConcatenatedAttributes();
    }

    private function updateConcatenatedAttributes(): void
    {
        $productsToUpdate = $this->getProductsToUpdate();
        $productModelsToUpdate = $this->getProductModelsToUpdate();
        $entitiesToUpdate = array_merge($productModelsToUpdate, $productsToUpdate);
        $this->updateConcatenatedAttributesForEntities($entitiesToUpdate);
    }

    public function setStepExecution(StepExecution $stepExecution): void
    {
        $this->stepExecution = $stepExecution;
    }

    private function getProductsToUpdate(): array
    {
        $jobParameters = $this->stepExecution->getJobParameters();
        $family = $this->familyRepository->findOneByIdentifier(
            $jobParameters->get('family_code')
        );

        return $this->productRepository->findBy(['family' => $family]);
    }

    private function getProductModelsToUpdate(): array
    {
        $jobParameters = $this->stepExecution->getJobParameters();
        $family = $this->familyRepository->findOneByIdentifier(
            $jobParameters->get('family_code')
        );
        $familyVariants = $this->familyVariantRepository->findBy(['family' => $family]);

        return $this->productModelRepository->findBy(['familyVariant' => $familyVariants]);
    }

    private function getConcatenatedAttributes(): array
    {
        $jobParameters = $this->stepExecution->getJobParameters();
        $concatenatedAttributes = $jobParameters->get('concatenatedAttributesToUpdate');

        $attributes = [];
        foreach ($concatenatedAttributes as $concatenatedAttribute) {
            if (null !== $attribute = $this->attributeRepository->findOneByIdentifier($concatenatedAttribute)) {
                $attributes[] = $attribute;
            }
        }

        return $attributes;
    }

    private function updateConcatenatedAttributesForEntities(array $entitiesToUpdate): void
    {
        $concatenatedAttributes = $this->getConcatenatedAttributes();

        $saveBatch = [
            'products'      => [],
            'productModels' => [],
        ];

        foreach ($entitiesToUpdate as $entity) {
            foreach ($concatenatedAttributes as $concatenatedAttribute) {
                $memberAttributes = $this->attributeRepository->findBy(
                    [
                        'code' => explode(',', $concatenatedAttribute->getProperty('attributes')),
                    ]
                );
                $this->concatenatedValuesUpdater->update(
                    $entity,
                    [
                        'concatenatedAttribute' => $concatenatedAttribute,
                        'memberAttributes'      => $memberAttributes,
                    ]
                );

                if (count($saveBatch) >= $this->batchSize) {
                    $this->saveBatch($saveBatch);
                    $saveBatch = [];
                }
                $saveBatch = $this->assignEntityToSaveBatch($entity, $saveBatch);
            }
        }

        $this->saveBatch($saveBatch);
        $this->stepExecution->addSummaryInfo('completed', ExitStatus::COMPLETED);
    }

    private function saveBatch(array $batch): void
    {
        $productModelsBatch = !empty($batch['productModels']) ? $batch['productModels'] : null;
        $productsBatch = !empty($batch['products']) ? $batch['products'] : null;

        if ($productModelsBatch) {
            $this->productModelBulkSaver->saveAll($productModelsBatch);
        }
        if ($productsBatch) {
            $this->productBulkSaver->saveAll($productsBatch);
        }

        $this->cacheClearer->clear();
    }

    private function assignEntityToSaveBatch(object $entity, array $saveBatch): array
    {
        if ($entity instanceof ProductModelInterface) {
            $saveBatch['productModels'][] = $entity;

            return $saveBatch;
        }
        if ($entity instanceof ProductInterface) {
            $saveBatch['products'][] = $entity;

            return $saveBatch;
        }

        throw new \InvalidArgumentException('Unknown entity class: ' . get_class($entity));
    }
}