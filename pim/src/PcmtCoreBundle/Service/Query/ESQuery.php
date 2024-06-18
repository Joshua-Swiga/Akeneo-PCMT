<?php
/**
 * Copyright (c) 2020, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */
declare(strict_types=1);

namespace PcmtCoreBundle\Service\Query;

use Akeneo\Tool\Bundle\ElasticsearchBundle\Client;

class ESQuery
{
    /** @var Client */
    private $esClient;

    /** @var string */
    private $indexType;

    public function __construct(Client $client, string $indexType)
    {
        $this->esClient = $client;
        $this->indexType = $indexType;
    }

    public function execute(array $query): array
    {
        return $this->esClient->search($this->indexType, $query);
    }
}
