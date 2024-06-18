<?php
/**
 * Copyright (c) 2020, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

declare(strict_types=1);

namespace PcmtCustomDatasetBundle\EventListener;

use Akeneo\Platform\Bundle\InstallerBundle\Event\InstallerEvents;
use Doctrine\DBAL\Connection;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class APIConnectionCreator implements EventSubscriberInterface
{
    /** @var Connection */
    private $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents(): array
    {
        return [
            InstallerEvents::POST_LOAD_FIXTURES => 'createAPIConnection',
        ];
    }

    public function createAPIConnection(): void
    {
        $sql = " 
            INSERT INTO akeneo_pim.pim_api_client (random_id, redirect_uris, secret, allowed_grant_types, label) 
            VALUES ('api_connection_1', 'a:0:{}', 'api_secret', 'a:2:{i:0;s:8:\"password\";i:1;s:13:\"refresh_token\";}', 'API first connection');
        ";
        $this->connection->exec($sql);
    }
}
