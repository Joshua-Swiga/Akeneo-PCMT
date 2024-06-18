<?php

declare(strict_types=1);

/*
 * Copyright (c) 2020, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

namespace PcmtCISBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Oro\Bundle\PimDataGridBundle\Doctrine\ORM\Repository\DatagridRepositoryInterface;

class SubscriptionRepository extends EntityRepository implements DatagridRepositoryInterface
{
    /**
     * {@inheritdoc}
     */
    public function createDatagridQueryBuilder()
    {
        $qb = $this->createQueryBuilder('subscription');
        $aliases = $qb->getRootAliases();
        $rootAlias = reset($aliases);

        $qb->addOrderBy(sprintf('%s.id', $rootAlias), 'ASC');

        return $qb;
    }
}