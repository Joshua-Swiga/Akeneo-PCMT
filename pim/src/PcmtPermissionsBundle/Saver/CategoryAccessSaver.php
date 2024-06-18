<?php

declare(strict_types=1);

/**
 * Copyright (c) 2020, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

namespace PcmtPermissionsBundle\Saver;

use Akeneo\Tool\Component\StorageUtils\Saver\BulkSaverInterface;
use Akeneo\Tool\Component\StorageUtils\Saver\SaverInterface;
use Doctrine\ORM\EntityManagerInterface;
use PcmtPermissionsBundle\Entity\CategoryAccess;

class CategoryAccessSaver implements BulkSaverInterface, SaverInterface
{
    /** @var EntityManagerInterface */
    protected $entityManager;

    public function __construct(EntityManagerInterface $em)
    {
        $this->entityManager = $em;
    }

    /**
     * {@inheritdoc}
     */
    public function saveAll(array $objects, array $options = []): void
    {
        foreach ($objects as $object) {
            $this->entityManager->persist($object);
        }
        $this->entityManager->flush();
    }

    /**
     * {@inheritdoc}
     */
    public function save($object, array $options = []): void
    {
        /** @var CategoryAccess $object */
        $this->entityManager->persist($object);
        $this->entityManager->flush();
    }
}
