<?php
/**
 * Copyright (c) 2019, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

declare(strict_types=1);

namespace PcmtCoreBundle\Connector\Job\Reader;

use Akeneo\Channel\Component\Model\ChannelInterface;
use Akeneo\Pim\Enrichment\Component\Product\Connector\Reader\Database\ProductReader;
use Akeneo\Pim\Enrichment\Component\Product\Query\Filter\Operators;
use Akeneo\Tool\Component\StorageUtils\Cursor\CursorInterface;
use Akeneo\UserManagement\Component\Repository\UserRepositoryInterface;
use PcmtSharedBundle\Service\CategoryWithPermissionsRepositoryInterface;
use PcmtSharedBundle\Service\Checker\CategoryPermissionsCheckerInterface;

class MstSupplierExportReader extends ProductReader implements CrossJoinExportReaderInterface
{
    /** @var CursorInterface */
    protected $crossProducts;

    /** @var bool */
    private $firstCrossRead = true;

    /** @var CategoryWithPermissionsRepositoryInterface */
    private $categoryWithPermissionsRepository;

    /** @var UserRepositoryInterface */
    private $userRepository;

    public function setCategoryWithPermissionsRepository(
        CategoryWithPermissionsRepositoryInterface $categoryWithPermissionsRepository
    ): void {
        $this->categoryWithPermissionsRepository = $categoryWithPermissionsRepository;
    }

    public function setUserRepository(UserRepositoryInterface $userRepository): void
    {
        $this->userRepository = $userRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function initialize(): void
    {
        parent::initialize();
        $this->firstCrossRead = true;

        $this->stepExecution->incrementSummaryInfo('read', 0);
    }

    public function setFamilyToCrossRead(string $familyToCrossRead): void
    {
        $filters = $this->getFiltersWithFamily($familyToCrossRead);
        $this->crossProducts = $this->getProductsCursor($filters, $this->getConfiguredChannel());
    }

    /**
     * {@inheritdoc}
     */
    public function readCross()
    {
        $product = null;

        if ($this->crossProducts->valid()) {
            if (!$this->firstCrossRead) {
                $this->crossProducts->next();
            }
            $product = $this->crossProducts->current();
        }

        if (null !== $product) {
            $this->stepExecution->incrementSummaryInfo('read_cross');

            $channel = $this->getConfiguredChannel();
            if (null !== $channel) {
                $this->metricConverter->convert($product, $channel);
            }
        }

        $this->firstCrossRead = false;

        return $product;
    }

    private function getFiltersWithFamily(string $family): array
    {
        $filters = parent::getConfiguredFilters();

        foreach ($filters as $key => $filter) {
            if ('family' === $filter['field']) {
                unset($filters[$key]);
                break;
            }
        }
        $filters[] = [
            'field'    => 'family',
            'value'    => [
                0 => $family,
            ],
            'operator' => 'IN',
        ];

        return $filters;
    }

    /**
     * {@inheritdoc}
     */
    protected function getConfiguredFilters()
    {
        return $this->getFiltersWithFamily('MD_HUB');
    }

    /**
     * {@inheritdoc}
     */
    protected function getProductsCursor(array $filters, ?ChannelInterface $channel = null)
    {
        $options = null !== $channel ? ['default_scope' => $channel->getCode()] : [];

        $jobExecution = $this->stepExecution->getJobExecution();
        $user = $this->userRepository->findOneByIdentifier($jobExecution->getUser());

        $productQueryBuilder = $this->pqbFactory->create($options);
        foreach ($filters as $filter) {
            $productQueryBuilder->addFilter(
                $filter['field'],
                $filter['operator'],
                $filter['value'],
                $filter['context'] ?? []
            );
        }

        $productQueryBuilder->addFilter(
            'categories',
            Operators::IN_LIST_OR_UNCLASSIFIED,
            $this->categoryWithPermissionsRepository->getCategoryCodes(
                CategoryPermissionsCheckerInterface::VIEW_LEVEL,
                $user
            )
        );

        return $productQueryBuilder->execute();
    }
}
