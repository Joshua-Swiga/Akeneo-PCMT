<?php
/**
 * Copyright (c) 2020, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

declare(strict_types=1);

namespace PcmtCustomDatasetBundle\EventListener;

use Doctrine\DBAL\Connection;
use PcmtCustomDatasetBundle\Event\InstallerEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class TableAttributeFixing implements EventSubscriberInterface
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
            InstallerEvents::POST_DATA_IMPORTED => 'fixTableAttribute',
        ];
    }

    public function fixTableAttribute(): void
    {
        $sql = " 
            UPDATE akeneo_pim.pim_catalog_attribute_option set
                type = 'text'
            WHERE attribute_id IN(
                SELECT id from akeneo_pim.pim_catalog_attribute where attribute_type='flagbit_catalog_table'
            );
            UPDATE akeneo_pim.pim_catalog_attribute_option set
                type = 'number'
            WHERE code = 'quantityOfNextLowerLevelTradeItem' and attribute_id IN(
                SELECT id from akeneo_pim.pim_catalog_attribute where attribute_type='flagbit_catalog_table'
            );
            UPDATE akeneo_pim.pim_catalog_attribute_option set
                type = 'select',
                type_config = '{\"options\": {\"CASE\": \"Case\", \"PALLET\": \"Pallet\", \"MIXED_MODULE\": \"Mixed Module\", \"TRANSPORT_LOAD\": \"Transport Load\", \"DISPLAY_SHIPPER\": \"Display Shipper\", \"BASE_UNIT_OR_EACH\": \"Base Unit or Each\", \"PACK_OR_INNER_PACK\": \"Pack or Inner Pack\"}}'
            WHERE code = 'tradeItemUnitDescriptorCode' and attribute_id IN(
                SELECT id from akeneo_pim.pim_catalog_attribute where attribute_type='flagbit_catalog_table'
            );
        ";
        $this->connection->exec($sql);
    }
}
