<?php
/**
 * Copyright (c) 2020, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

declare(strict_types=1);

namespace PcmtDraftBundle\Repository;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\ParameterType;
use Doctrine\ORM\EntityRepository;
use PcmtSharedBundle\Service\CategoryWithPermissionsRepositoryInterface;
use PcmtSharedBundle\Service\Checker\CategoryPermissionsCheckerInterface;

class DraftRepository extends EntityRepository implements DraftRepositoryInterface
{
    /** @var CategoryWithPermissionsRepositoryInterface */
    private $categoryWithPermissionsRepository;

    private function getStatement(): string
    {
        // we need both - drafts without categories and with categories that a user has access to
        return '
            (select d.id from pcmt_catalog_product_draft d
            inner join pcmt_catalog_product_draft_category c on d.id = c.draft_id
            WHERE c.category_id IN(:categoryIds) AND d.status = :statusId AND (d.product_id is not null OR d.product_model_id is not null))

            UNION

            (select d.id from pcmt_catalog_product_draft d
            left join pcmt_catalog_product_draft_category c on d.id = c.draft_id
            where c.draft_id is null AND d.status = :statusId2 AND (d.product_id is not null OR d.product_model_id is not null))
        ';
    }

    public function findWithPermissionAndStatus(int $statusId, int $offset = 0, ?int $limit = null): array
    {
        $categoryIds = $this->categoryWithPermissionsRepository->getCategoryIds(CategoryPermissionsCheckerInterface::EDIT_LEVEL);
        if (null === $categoryIds) {
            return $this->findBy(
                [
                    'status' => $statusId,
                ],
                [
                    'id' => 'DESC',
                ],
                $limit,
                $offset
            );
        }

        $query = $this->getStatement() . ' ORDER BY id DESC';
        if ($limit) {
            $query .= ' LIMIT '. $limit;
        }
        if ($offset) {
            $query .= ' OFFSET '. $offset;
        }
        $values = [
            'categoryIds' => $categoryIds,
            'statusId'    => $statusId,
            'statusId2'   => $statusId,
        ];
        $types = [
            'categoryIds' => Connection::PARAM_INT_ARRAY,
            'statusId'    => ParameterType::INTEGER,
            'statusId2'   => ParameterType::INTEGER,
        ];

        $statement = $this->_em->getConnection()->executeQuery($query, $values, $types);

        $ids = [];
        while ($id = $statement->fetchColumn(0)) {
            $ids[] = $id;
        }

        return $this->findBy(
            [
                'id' => $ids,
            ],
            [
                'id' => 'DESC',
            ]
        );
    }

    public function countWithPermissionAndStatus(int $statusId): int
    {
        $categoryIds = $this->categoryWithPermissionsRepository->getCategoryIds(CategoryPermissionsCheckerInterface::EDIT_LEVEL);
        if (null === $categoryIds) {
            return $this->count(
                [
                    'status' => $statusId,
                ]
            );
        }

        $query = $this->getStatement();
        $values = [
            'categoryIds' => $categoryIds,
            'statusId'    => $statusId,
            'statusId2'   => $statusId,
        ];
        $types = [
            'categoryIds' => Connection::PARAM_INT_ARRAY,
            'statusId'    => ParameterType::INTEGER,
            'statusId2'   => ParameterType::INTEGER,
        ];

        $statement = $this->_em->getConnection()->executeQuery($query, $values, $types);

        $count = $statement->rowCount();

        return (int) $count;
    }

    public function setCategoryWithPermissionsRepository(CategoryWithPermissionsRepositoryInterface $categoryWithPermissionsRepository): void
    {
        $this->categoryWithPermissionsRepository = $categoryWithPermissionsRepository;
    }
}