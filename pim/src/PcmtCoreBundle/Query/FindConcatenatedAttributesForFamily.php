<?php
/**
 * Copyright (c) 2019, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

declare(strict_types=1);

namespace PcmtCoreBundle\Query;

use Akeneo\Pim\Structure\Component\Model\FamilyInterface;
use Doctrine\ORM\EntityManagerInterface;
use PcmtCoreBundle\Extension\ConcatenatedAttribute\Structure\Component\AttributeType\PcmtAtributeTypes;

class FindConcatenatedAttributesForFamily
{
    /** @var EntityManagerInterface */
    private $entityManger;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManger = $entityManager;
    }

    public function execute(FamilyInterface $family): array
    {
        $sql = <<<SQL
            SELECT a.code
            FROM pim_catalog_family f
                INNER JOIN pim_catalog_family_attribute fa ON f.id = fa.family_id
                INNER JOIN pim_catalog_attribute a ON fa.attribute_id = a.id
            WHERE (f.code = :family_code AND a.code = :attribute_code)    
SQL;
        $query = $this->entityManger->getConnection()->executeQuery(
            $sql,
            [
                'family_code'    => $family->getCode(),
                'attribute_code' => PcmtAtributeTypes::CONCATENATED_FIELDS,
            ]
        );

        $results = $query->fetchAll();

        return array_map(
            function (array $result) {
                return $result['code'];
            },
            $results
        );
    }
}