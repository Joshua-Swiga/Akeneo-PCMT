<?php

declare(strict_types=1);

/**
 * Copyright (c) 2020, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

namespace PcmtPermissionsBundle\Entity;

use Akeneo\Pim\Enrichment\Component\Category\Model\CategoryInterface;
use Akeneo\UserManagement\Component\Model\Group;
use Doctrine\Common\Collections\Collection;

interface CategoryWithAccessInterface extends CategoryInterface
{
    public function removeAccess(CategoryAccess $access): void;

    public function addAccess(CategoryAccess $access): void;

    public function getAccessesOfLevel(string $level): array;

    public function getViewAccess(): array;

    public function getEditAccess(): array;

    public function getOwnAccess(): array;

    public function setViewAccess(array $userGroups): void;

    public function setEditAccess(array $userGroups): void;

    public function setOwnAccess(array $userGroups): void;

    public function checkIfAccessExists(Group $userGroup, string $level): bool;

    public function getAccesses(): Collection;

    public function clearAccesses(): void;
}