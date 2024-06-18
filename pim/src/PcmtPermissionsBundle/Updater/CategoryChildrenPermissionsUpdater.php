<?php
/**
 * Copyright (c) 2020, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

declare(strict_types=1);

namespace PcmtPermissionsBundle\Updater;

use Akeneo\Tool\Component\StorageUtils\Saver\SaverInterface;
use Doctrine\Common\Collections\ArrayCollection;
use PcmtPermissionsBundle\Entity\CategoryAccess;
use PcmtPermissionsBundle\Entity\CategoryWithAccess;

class CategoryChildrenPermissionsUpdater
{
    /** @var ArrayCollection */
    private $accesses;

    /** @var SaverInterface */
    private $categorySaver;

    public function __construct(SaverInterface $categorySaver)
    {
        $this->categorySaver = $categorySaver;
    }

    public function update(CategoryWithAccess $category): void
    {
        $this->accesses = $category->getAccesses();
        $children = $category->getChildren();
        foreach ($children as $child) {
            $this->updateCategory($child);
        }
    }

    private function updateCategory(CategoryWithAccess $category): void
    {
        if ($this->areAccessesDifferent($category->getAccesses())) {
            $category->clearAccesses();
            foreach ($this->accesses as $access) {
                $newAccess = new CategoryAccess($category, $access->getUserGroup(), $access->getLevel());
                $category->addAccess($newAccess);
            }
            $this->categorySaver->save($category);
        }
        $children = $category->getChildren();
        foreach ($children as $child) {
            $this->updateCategory($child);
        }
    }

    private function areAccessesDifferent(iterable $accesses): bool
    {
        if ($this->getHashForAccesses($accesses) !== $this->getHashForAccesses($this->accesses)) {
            return true;
        }

        return false;
    }

    private function getHashForAccesses(iterable $accesses): string
    {
        $accessesNormalized = [];
        foreach ($accesses as $access) {
            /** @var CategoryAccess $access */
            $accessesNormalized[] = $access->getUserGroup()->getId() . '-' . $access->getLevel();
        }
        sort($accessesNormalized);

        return md5(json_encode($accessesNormalized));
    }
}