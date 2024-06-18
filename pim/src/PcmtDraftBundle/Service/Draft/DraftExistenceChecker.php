<?php

declare(strict_types=1);

/**
 * Copyright (c) 2020, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

namespace PcmtDraftBundle\Service\Draft;

use PcmtDraftBundle\Entity\AbstractDraft;
use PcmtDraftBundle\Entity\ExistingObjectDraftInterface;
use PcmtDraftBundle\Entity\ProductDraftInterface;
use PcmtDraftBundle\Entity\ProductModelDraftInterface;
use PcmtDraftBundle\Repository\DraftRepository;

class DraftExistenceChecker
{
    /** @var DraftRepository */
    private $repository;

    public function __construct(DraftRepository $repository)
    {
        $this->repository = $repository;
    }

    public function checkIfDraftForObjectAlreadyExists(ExistingObjectDraftInterface $draft): bool
    {
        $criteria = [
            'status'  => AbstractDraft::STATUS_NEW,
        ];
        if ($draft instanceof ProductModelDraftInterface) {
            $criteria['productModel'] = $draft->getObject();
        } elseif ($draft instanceof ProductDraftInterface) {
            $criteria['product'] = $draft->getObject();
        } else {
            throw new \Exception('Class '. get_class($draft).' can not be processed.');
        }

        return $this->repository->count($criteria) > 0;
    }
}