<?php

declare(strict_types=1);

/**
 * Copyright (c) 2020, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

namespace PcmtDraftBundle\Controller;

use Akeneo\Pim\Enrichment\Bundle\Controller\InternalApi\ProductModelCategoryController as ProductModelCategoryControllerOriginal;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;
use Doctrine\ORM\EntityManager;
use PcmtDraftBundle\Entity\AbstractDraft;
use PcmtDraftBundle\Service\Draft\GeneralObjectFromDraftCreator;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class PcmtProductModelCategoryController extends ProductModelCategoryControllerOriginal
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

    protected function findProductModelOr404(string $id): ProductModelInterface
    {
        $repo = $this->entityManager->getRepository(AbstractDraft::class);
        $criteria = [
            'status'       => AbstractDraft::STATUS_NEW,
            'productModel' => $id,
        ];
        $draft = $repo->findOneBy($criteria);

        if (!$draft) {
            throw new NotFoundHttpException(
                sprintf('Draft for product model with id %s could not be found.', (string) $id)
            );
        }

        return $this->creator->getObjectToSave($draft);
    }
}
