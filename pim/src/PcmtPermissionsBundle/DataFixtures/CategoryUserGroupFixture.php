<?php

declare(strict_types=1);

/**
 * Copyright (c) 2020, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

namespace PcmtPermissionsBundle\DataFixtures;

use Akeneo\UserManagement\Component\Model\Group;
use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use PcmtPermissionsBundle\Entity\CategoryAccess;
use PcmtPermissionsBundle\Entity\CategoryWithAccess;
use PcmtSharedBundle\Service\Checker\CategoryPermissionsCheckerInterface;

class CategoryUserGroupFixture implements FixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        $this->clean($manager);

        $userGroupRepository = $manager->getRepository(Group::class);
        $userGroup = $userGroupRepository->findOneBy(['name' => 'Clothes manager']);

        $categoryWithAccessRepository = $manager->getRepository(CategoryWithAccess::class);
        $category = $categoryWithAccessRepository->findOneBy(['code'=>'MASTER_DATA']);

        $categoryAccess = new CategoryAccess($category, $userGroup, CategoryPermissionsCheckerInterface::OWN_LEVEL);
        $manager->persist($categoryAccess);

        $categoryAccess = new CategoryAccess($category, $userGroup, CategoryPermissionsCheckerInterface::EDIT_LEVEL);
        $manager->persist($categoryAccess);

        $categoryAccess = new CategoryAccess($category, $userGroup, CategoryPermissionsCheckerInterface::VIEW_LEVEL);
        $manager->persist($categoryAccess);

        $manager->flush();
    }

    public function clean(ObjectManager $manager): void
    {
        $categoryWithAccessRepository = $manager->getRepository(CategoryWithAccess::class);
        $category = $categoryWithAccessRepository->findOneBy(['code'=>'MASTER_DATA']);

        $categoryAccessRepository = $manager->getRepository(CategoryAccess::class);

        $categoryAccesses = $categoryAccessRepository->findBy(['category' => $category]);
        foreach ($categoryAccesses as $categoryAccess) {
            $manager->remove($categoryAccess);
        }
        $manager->flush();
    }
}