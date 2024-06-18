<?php

declare(strict_types=1);

/**
 * Copyright (c) 2020, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

namespace PcmtDraftBundle\Controller;

use Akeneo\Pim\Enrichment\Bundle\Controller\InternalApi\ProductCategoryController as ProductCategoryControllerOriginal;
use Doctrine\ORM\EntityManager;
use PcmtDraftBundle\Entity\AbstractDraft;
use PcmtDraftBundle\Service\Draft\GeneralObjectFromDraftCreator;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class PcmtProductCategoryController extends ProductCategoryControllerOriginal
{
    /** @var EntityManager */
    private $entityManager;

    /** @var GeneralObjectFromDraftCreator */
    private $creator;

    public function setEntityManager(EntityManager $entityManager): void
    {
        $this->entityManager = $entityManager;
    }

    public function setCreator(GeneralObjectFromDraftCreator $creator): void
    {
        $this->creator = $creator;
    }

    /**
     * {@inheritdoc}
     */
    protected function findProductOr404($id)
    {
        $repo = $this->entityManager->getRepository(AbstractDraft::class);
        $criteria = [
            'status'  => AbstractDraft::STATUS_NEW,
            'product' => $id,
        ];
        $draft = $repo->findOneBy($criteria);

        if (!$draft) {
            throw new NotFoundHttpException(
                sprintf('Draft for product with id %s could not be found.', (string) $id)
            );
        }

        return $this->creator->getObjectToSave($draft);
    }
}
