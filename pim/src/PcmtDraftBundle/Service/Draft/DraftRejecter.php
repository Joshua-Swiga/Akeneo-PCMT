<?php
/**
 * Copyright (c) 2020, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

declare(strict_types=1);

namespace PcmtDraftBundle\Service\Draft;

use Akeneo\UserManagement\Component\Model\UserInterface;
use Doctrine\ORM\EntityManagerInterface;
use PcmtDraftBundle\Entity\AbstractProductDraft;
use PcmtDraftBundle\Entity\DraftInterface;
use PcmtDraftBundle\Exception\DraftViolationException;
use PcmtSharedBundle\Service\Checker\CategoryPermissionsCheckerInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationList;

class DraftRejecter
{
    /** @var EntityManagerInterface */
    private $entityManager;

    /** @var TokenStorageInterface */
    private $tokenStorage;

    /** @var CategoryPermissionsCheckerInterface */
    private $categoryPermissionsChecker;

    public function __construct(
        EntityManagerInterface $entityManager,
        TokenStorageInterface $tokenStorage,
        CategoryPermissionsCheckerInterface $categoryPermissionsChecker
    ) {
        $this->entityManager = $entityManager;
        $this->tokenStorage = $tokenStorage;
        $this->categoryPermissionsChecker = $categoryPermissionsChecker;
    }

    public function reject(DraftInterface $draft): void
    {
        if ($draft instanceof AbstractProductDraft) {
            $entity = $draft->getProduct();
        } else {
            $entity = $draft->getProductModel();
        }

        if ($entity) {
            /** @var UserInterface $user */
            $user = $this->tokenStorage->getToken()->getUser();

            $violations = new ConstraintViolationList();

            if (!$this->categoryPermissionsChecker->hasAccessToProduct(CategoryPermissionsCheckerInterface::OWN_LEVEL, $entity, $user)) {
                $violations->add(
                    new ConstraintViolation(
                        'No permission to reject the draft: no "own" access to any of the categories of the product.',
                        '',
                        [],
                        '',
                        '',
                        ''
                    )
                );
            }

            if (0 !== $violations->count()) {
                throw new DraftViolationException($violations, $entity);
            }
        }

        $draft->reject();

        $this->entityManager->persist($draft);
        $this->entityManager->flush();
    }
}