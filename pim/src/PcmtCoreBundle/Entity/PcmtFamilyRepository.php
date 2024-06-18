<?php
/**
 * Copyright (c) 2019, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

declare(strict_types=1);

namespace PcmtCoreBundle\Entity;

use Akeneo\Pim\Structure\Bundle\Doctrine\ORM\Repository\FamilyRepository;
use Akeneo\Pim\Structure\Component\Model\FamilyInterface;
use PcmtCoreBundle\Extension\ConcatenatedAttribute\Structure\Component\AttributeType\PcmtAtributeTypes;

class PcmtFamilyRepository extends FamilyRepository
{
    public function getConcatenatedAttributes(FamilyInterface $familyId): ?array
    {
        $qb = $this->createQueryBuilder('f');
        $qb->select('a.id, a.code, a.properties')
            ->innerJoin('f.attributes', 'a')
            ->where(
                $qb->expr()->andX(
                    $qb->expr()->eq('f.id', ':familyId'),
                    $qb->expr()->eq('a.type', ':backendType')
                )
            );
        $qb->setParameters([
            ':familyId'    => $familyId,
            ':backendType' => PcmtAtributeTypes::CONCATENATED_FIELDS,
        ]);

        return $qb->getQuery()->getResult();
    }
}