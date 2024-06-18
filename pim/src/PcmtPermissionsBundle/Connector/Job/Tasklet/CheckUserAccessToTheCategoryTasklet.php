<?php
/**
 * Copyright (c) 2020, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

declare(strict_types=1);

namespace PcmtPermissionsBundle\Connector\Job\Tasklet;

use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;
use Akeneo\Pim\Enrichment\Component\Product\Repository\ProductModelRepositoryInterface;
use Akeneo\Pim\Enrichment\Component\Product\Repository\ProductRepositoryInterface;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use Akeneo\Tool\Component\Classification\CategoryAwareInterface;
use Akeneo\Tool\Component\Connector\Step\TaskletInterface;
use PcmtPermissionsBundle\Connector\Job\InvalidItem\CategoryAwareInvalidItem;
use PcmtSharedBundle\Service\Checker\CategoryPermissionsCheckerInterface;

class CheckUserAccessToTheCategoryTasklet implements TaskletInterface
{
    /** @var StepExecution */
    private $stepExecution;

    /** @var ProductRepositoryInterface */
    private $productRepository;

    /** @var ProductModelRepositoryInterface */
    private $productModelRepository;

    /** @var CategoryPermissionsCheckerInterface */
    private $categoryPermissionsChecker;

    public function __construct(
        ProductRepositoryInterface $productRepository,
        ProductModelRepositoryInterface $productModelRepository,
        CategoryPermissionsCheckerInterface $categoryPermissionsChecker
    ) {
        $this->productRepository = $productRepository;
        $this->productModelRepository = $productModelRepository;
        $this->categoryPermissionsChecker = $categoryPermissionsChecker;
    }

    public function setStepExecution(StepExecution $stepExecution): void
    {
        $this->stepExecution = $stepExecution;
    }

    /**
     * Execute the tasklet
     */
    public function execute(): void
    {
        if (null === $this->stepExecution) {
            throw new \InvalidArgumentException(
                sprintf('In order to execute "%s" you need to set a step execution.', static::class)
            );
        }

        $this->stepExecution->addSummaryInfo('products_and_product_models_without_access', 0);

        $filters = $this->stepExecution->getJobParameters()->get('filters');
        $idFilter = array_filter(
            $filters,
            function (array $filter) {
                return 'id' === $filter['field'];
            }
        )[0];

        $ids = [
            'products'       => [],
            'product_models' => [],
        ];

        foreach ($idFilter['value'] as $entityId) {
            if (false !== mb_strpos($entityId, 'product_model')) {
                $ids['product_models'][] = (int) explode('_', $entityId)[2];
            } else {
                $ids['products'][] = (int) explode('_', $entityId)[1];
            }
        }

        $products = $this->productRepository->findBy(['id' => $ids['products']]);
        $productModels = $this->productModelRepository->findBy(['id' => $ids['product_models']]);

        $ids = $this->checkPermissions($products);
        $ids = array_merge($ids, $this->checkPermissions($productModels));

        $filters = $this->stepExecution->getJobParameters()->get('filters');

        $filters = array_map(
            function (array $filter) use ($ids) {
                if ('id' === $filter['field']) {
                    $filter['value'] = $ids;
                }

                return $filter;
            },
            $filters
        );

        $this->stepExecution->getJobParameters()->set('filters', $filters);
    }

    private function checkPermissions(array $categoryAwareEntities): array
    {
        $ids = [];

        foreach ($categoryAwareEntities as $categoryAwareEntity) {
            /** @var CategoryAwareInterface $categoryAwareEntity */
            if (!$this->categoryPermissionsChecker->hasAccessToProduct(
                CategoryPermissionsCheckerInterface::OWN_LEVEL,
                $categoryAwareEntity
            )) {
                $this->stepExecution->incrementSummaryInfo(
                    'products_and_product_models_without_access'
                );

                $this->stepExecution->incrementReadCount();
                $this->stepExecution->addWarning(
                    'User does not have access to any of the categories of the entity',
                    [],
                    new CategoryAwareInvalidItem($categoryAwareEntity)
                );
                continue;
            }

            if ($categoryAwareEntity instanceof ProductInterface) {
                $ids[] = 'product_'.$categoryAwareEntity->getId();
            } elseif ($categoryAwareEntity instanceof ProductModelInterface) {
                $ids[] = 'product_model_'.$categoryAwareEntity->getId();
            }
        }

        return $ids;
    }
}
