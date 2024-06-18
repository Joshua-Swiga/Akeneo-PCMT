<?php

declare(strict_types=1);

/*
 * Copyright (c) 2020, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

namespace PcmtRulesBundle\Tests\TestDataBuilder;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;

class QueryBuilderBuilder
{
    /** @var QueryBuilder */
    private $queryBuilder;

    public function __construct(EntityManagerInterface $em)
    {
        $this->queryBuilder = new QueryBuilder($em);
        $this->queryBuilder->expr();
    }

    public function build(): QueryBuilder
    {
        return $this->queryBuilder;
    }
}