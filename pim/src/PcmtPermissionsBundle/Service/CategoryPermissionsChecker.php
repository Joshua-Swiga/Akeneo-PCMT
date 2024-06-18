<?php
/**
 * Copyright (c) 2020, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

declare(strict_types=1);

namespace PcmtPermissionsBundle\Service;

use Akeneo\Tool\Component\Classification\CategoryAwareInterface;
use Akeneo\UserManagement\Component\Model\Group;
use Akeneo\UserManagement\Component\Model\UserInterface;
use PcmtPermissionsBundle\Entity\CategoryAccess;
use PcmtPermissionsBundle\Entity\CategoryWithAccessInterface;
use PcmtSharedBundle\Service\Checker\CategoryPermissionsCheckerInterface;
use Symfony\Component\DependencyInjection\Exception\ParameterNotFoundException;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class CategoryPermissionsChecker implements CategoryPermissionsCheckerInterface
{
    /** @var TokenStorageInterface */
    private $tokenStorage;

    public function __construct(TokenStorageInterface $tokenStorage)
    {
        $this->tokenStorage = $tokenStorage;
    }

    public function hasAccessToProduct(string $type, ?CategoryAwareInterface $entity, ?UserInterface $user = null): bool
    {
        /* Access to product without category is always granted */
        if (null === $entity || 0 === $entity->getCategories()->count()) {
            return true;
        }
        foreach ($entity->getCategories()->getIterator() as $category) {
            if ($this->isGranted($type, $category, $user)) {
                return true;
            }
        }

        return false;
    }

    public function isGranted(string $type, CategoryWithAccessInterface $category, ?UserInterface $user = null): bool
    {
        if (!in_array($type, CategoryPermissionsCheckerInterface::ALL_LEVELS)) {
            throw new ParameterNotFoundException($type);
        }

        /* Access to category without any group is always granted ("All" default group) */
        foreach ($this->getAccessLevels($type) as $level) {
            if (0 === count($category->getAccessesOfLevel($level))) {
                return true;
            }
        }

        $user = $user ?? $this->tokenStorage->getToken()->getUser();

        foreach ($user->getGroups() as $group) {
            /** @var Group $group */
            foreach ($category->getAccesses()->getIterator() as $access) {
                /** @var CategoryAccess $access */
                if ($group->getId() === $access->getUserGroup()->getId()) {
                    if (in_array($access->getLevel(), $this->getAccessLevels($type))) {
                        return true;
                    }
                }
            }
        }

        return false;
    }

    private function getAccessLevels(string $type): array
    {
        switch ($type) {
            case CategoryPermissionsCheckerInterface::VIEW_LEVEL:
                return [
                    CategoryPermissionsCheckerInterface::VIEW_LEVEL,
                    CategoryPermissionsCheckerInterface::EDIT_LEVEL,
                    CategoryPermissionsCheckerInterface::OWN_LEVEL,
                ];
            case CategoryPermissionsCheckerInterface::EDIT_LEVEL:
                return [
                    CategoryPermissionsCheckerInterface::EDIT_LEVEL,
                    CategoryPermissionsCheckerInterface::OWN_LEVEL,
                ];
            case CategoryPermissionsCheckerInterface::OWN_LEVEL:
                return [
                    CategoryPermissionsCheckerInterface::OWN_LEVEL,
                ];
        }
    }
}