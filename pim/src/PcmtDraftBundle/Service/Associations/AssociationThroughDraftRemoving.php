<?php

declare(strict_types=1);

/*
 * Copyright (c) 2020, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

namespace PcmtDraftBundle\Service\Associations;

use Akeneo\Pim\Enrichment\Component\Product\Model\EntityWithAssociationsInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;
use Akeneo\Pim\Structure\Component\Model\AssociationTypeInterface;
use Akeneo\Tool\Component\StorageUtils\Saver\SaverInterface;
use PcmtDraftBundle\Entity\AbstractDraft;
use PcmtDraftBundle\Entity\ExistingObjectDraftInterface;
use PcmtDraftBundle\Repository\DraftRepository;
use PcmtDraftBundle\Service\Draft\GeneralObjectFromDraftCreator;

class AssociationThroughDraftRemoving
{
    /** @var SaverInterface */
    private $productSaver;

    /** @var SaverInterface */
    private $productModelSaver;

    /** @var GeneralObjectFromDraftCreator */
    private $generalObjectFromDraftCreator;

    /** @var DraftRepository */
    private $draftRepository;

    public function __construct(
        SaverInterface $productSaver,
        SaverInterface $productModelSaver,
        GeneralObjectFromDraftCreator $generalObjectFromDraftCreator,
        DraftRepository $draftRepository
    ) {
        $this->productSaver = $productSaver;
        $this->productModelSaver = $productModelSaver;
        $this->generalObjectFromDraftCreator = $generalObjectFromDraftCreator;
        $this->draftRepository = $draftRepository;
    }

    public function remove(
        EntityWithAssociationsInterface $objectToBeRemovedFromAssociation,
        EntityWithAssociationsInterface $objectToBeChanged,
        AssociationTypeInterface $associationType
    ): void {
        if ($objectToBeRemovedFromAssociation->getId() === $objectToBeChanged->getId()) {
            return;
        }
        $criteria = [
            'status'  => AbstractDraft::STATUS_NEW,
        ];
        if ($objectToBeChanged instanceof ProductInterface) {
            $criteria['product'] = $objectToBeChanged;
        } elseif ($objectToBeChanged instanceof ProductModelInterface) {
            $criteria['productModel'] = $objectToBeChanged;
        }

        /** @var ExistingObjectDraftInterface $draft */
        $draft = $this->draftRepository->findOneBy($criteria);
        if ($draft) {
            // we do this not to overwrite any other changes already in the draft of connected product
            $objectToBeChanged = $this->generalObjectFromDraftCreator->getObjectToCompare($draft);
        }

        $association = $objectToBeChanged->getAssociationForType($associationType);
        if (!$association) {
            return;
        }

        if ($objectToBeRemovedFromAssociation instanceof ProductInterface) {
            $association->removeProduct($objectToBeRemovedFromAssociation);
        } else {
            $association->removeProductModel($objectToBeRemovedFromAssociation);
        }

        if ($objectToBeChanged instanceof ProductInterface) {
            $this->productSaver->save($objectToBeChanged);
        } else {
            $this->productModelSaver->save($objectToBeChanged);
        }
    }
}