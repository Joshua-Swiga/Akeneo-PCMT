<?php

declare(strict_types=1);

/**
 * Copyright (c) 2020, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

namespace PcmtPermissionsBundle\Entity;

use Akeneo\Pim\Enrichment\Component\Category\Model\Category;
use Akeneo\UserManagement\Component\Model\Group;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use PcmtSharedBundle\Service\Checker\CategoryPermissionsCheckerInterface;

class CategoryWithAccess extends Category implements CategoryWithAccessInterface
{
    /** @var Collection */
    private $accesses;

    public function __construct()
    {
        parent::__construct();

        $this->accesses = new ArrayCollection();
    }

    public function removeAccess(CategoryAccess $access): void
    {
        $this->accesses->removeElement($access);
    }

    /**
     * {@inheritdoc}
     */
    public function addAccess(CategoryAccess $access): void
    {
        $this->accesses->add($access);
    }

    public function getAccessesOfLevel(string $level): array
    {
        $accesses = [];
        foreach ($this->accesses as $access) {
            /** @var CategoryAccess $access */
            if ($level === $access->getLevel()) {
                $accesses[] = $access->getUserGroup();
            }
        }

        return $accesses;
    }

    public function getViewAccess(): array
    {
        return $this->getAccessesOfLevel(CategoryPermissionsCheckerInterface::VIEW_LEVEL);
    }

    public function getEditAccess(): array
    {
        return $this->getAccessesOfLevel(CategoryPermissionsCheckerInterface::EDIT_LEVEL);
    }

    public function getOwnAccess(): array
    {
        return $this->getAccessesOfLevel(CategoryPermissionsCheckerInterface::OWN_LEVEL);
    }

    private function setAccessesByUserGroups(array $userGroups, string $level): void
    {
        foreach ($userGroups as $userGroup) {
            if (!$this->checkIfAccessExists($userGroup, $level)) {
                $categoryAccess = new CategoryAccess($this, $userGroup, $level);
                $this->accesses->add($categoryAccess);
            }
        }
    }

    public function setViewAccess(array $userGroups): void
    {
        $this->setAccessesByUserGroups($userGroups, CategoryPermissionsCheckerInterface::VIEW_LEVEL);
    }

    public function setEditAccess(array $userGroups): void
    {
        $this->setAccessesByUserGroups($userGroups, CategoryPermissionsCheckerInterface::EDIT_LEVEL);
    }

    public function setOwnAccess(array $userGroups): void
    {
        $this->setAccessesByUserGroups($userGroups, CategoryPermissionsCheckerInterface::OWN_LEVEL);
    }

    public function checkIfAccessExists(Group $userGroup, string $level): bool
    {
        foreach ($this->accesses as $access) {
            /** @var CategoryAccess $access */
            if ($access->getUserGroup()->getId() === $userGroup->getId() && $level === $access->getLevel()) {
                return true;
            }
        }

        return false;
    }

    public function getAccesses(): Collection
    {
        return $this->accesses;
    }

    public function clearAccesses(): void
    {
        foreach ($this->accesses->getKeys() as $key) {
            $this->accesses->remove($key);
        }
    }
}