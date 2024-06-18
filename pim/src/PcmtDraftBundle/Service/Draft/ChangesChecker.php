<?php

declare(strict_types=1);

/*
 * Copyright (c) 2020, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

namespace PcmtDraftBundle\Service\Draft;

use Doctrine\ORM\EntityManagerInterface;
use PcmtDraftBundle\Entity\DraftInterface;
use PcmtDraftBundle\Service\AttributeChange\AttributeChangeService;

class ChangesChecker
{
    /** @var EntityManagerInterface */
    private $entityManager;

    /** @var GeneralObjectFromDraftCreator */
    private $generalObjectFromDraftCreator;

    /** @var AttributeChangeService */
    private $attributeChangeService;

    public function __construct(
        EntityManagerInterface $entityManager,
        GeneralObjectFromDraftCreator $generalObjectFromDraftCreator,
        AttributeChangeService $attributeChangeService
    ) {
        $this->entityManager = $entityManager;
        $this->generalObjectFromDraftCreator = $generalObjectFromDraftCreator;
        $this->attributeChangeService = $attributeChangeService;
    }

    public function checkIfChanges(DraftInterface $draft): bool
    {
        $this->entityManager->refresh($draft->getObject());
        $associations = $draft->getObject()->getAssociations();
        foreach ($associations as $association) {
            $this->entityManager->refresh($association);
        }

        $newObject = $this->generalObjectFromDraftCreator->getObjectToCompare($draft);
        $changes = $this->attributeChangeService->get($newObject, $draft->getObject());

        return $changes ? true : false;
    }
}