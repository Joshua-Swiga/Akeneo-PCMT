<?php
/**
 * Copyright (c) 2019, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

declare(strict_types=1);

namespace PcmtCoreBundle\Repository;

use Doctrine\ORM\QueryBuilder;
use Pim\Bundle\CustomEntityBundle\Entity\Repository\CustomEntityRepository;

class GS1CodesRepository extends CustomEntityRepository
{
    /**
     * @param array $options
     *                       Override method
     */
    protected function selectFields(QueryBuilder $qb, array $options): void
    {
        $labelProperty = $this->getReferenceDataLabelProperty();
        $identifierField = isset($options['type']) && 'code' === $options['type'] ? 'code' : 'id';

        $qb
            ->select(
                sprintf('%s.%s AS id', $this->getAlias(), $identifierField)
            )
            ->addSelect(
                sprintf(
                    'CASE WHEN %s.%s IS NULL OR %s.%s = \'\' THEN CONCAT(\'[\', %s.code, \']\') ELSE CONCAT(%s.name, \' (\', %s.%s,  \') \') END AS text',
                    $this->getAlias(),
                    $labelProperty,
                    $this->getAlias(),
                    $labelProperty,
                    $this->getAlias(),
                    $this->getAlias(),
                    $this->getAlias(),
                    $identifierField
                )
            );
    }
}