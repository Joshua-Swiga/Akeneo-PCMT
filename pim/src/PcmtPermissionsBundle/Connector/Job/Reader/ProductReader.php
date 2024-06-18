<?php
/**
 * Copyright (c) 2020, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

declare(strict_types=1);

namespace PcmtPermissionsBundle\Connector\Job\Reader;

use Akeneo\Channel\Component\Model\ChannelInterface;
use Akeneo\Pim\Enrichment\Component\Product\Connector\Reader\Database\ProductReader as AkeneoProductReader;
use Akeneo\Pim\Enrichment\Component\Product\Query\Filter\Operators;
use Akeneo\UserManagement\Component\Repository\UserRepositoryInterface;
use PcmtPermissionsBundle\Service\CategoryWithPermissionsRepository;
use PcmtSharedBundle\Service\Checker\CategoryPermissionsCheckerInterface;

class ProductReader extends AkeneoProductReader
{
    /** @var CategoryWithPermissionsRepository */
    private $categoryWithPermissionsRepository;

    /** @var UserRepositoryInterface */
    private $userRepository;

    public function setCategoryWithPermissionsRepository(
        CategoryWithPermissionsRepository $categoryWithPermissionsRepository
    ): void {
        $this->categoryWithPermissionsRepository = $categoryWithPermissionsRepository;
    }

    public function setUserRepository(UserRepositoryInterface $userRepository): void
    {
        $this->userRepository = $userRepository;
    }

    public function initialize(): void
    {
        parent::initialize();

        $this->stepExecution->incrementSummaryInfo('read', 0);
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
