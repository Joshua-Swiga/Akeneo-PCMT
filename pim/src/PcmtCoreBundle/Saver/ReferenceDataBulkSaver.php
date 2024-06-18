<?php
/**
 * Copyright (c) 2019, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

declare(strict_types=1);

namespace PcmtCoreBundle\Saver;

use Akeneo\Pim\Enrichment\Component\Product\Model\ReferenceDataInterface;
use Akeneo\Tool\Component\StorageUtils\Saver\BulkSaverInterface;
use Akeneo\Tool\Component\StorageUtils\Saver\SaverInterface;
use Akeneo\Tool\Component\StorageUtils\StorageEvents;
use Doctrine\Common\Util\ClassUtils;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\GenericEvent;

class ReferenceDataBulkSaver implements BulkSaverInterface, SaverInterface
{
    /** @var EntityManagerInterface */
    protected $em;

    /** @var EventDispatcherInterface */
    protected $eventDispatcher;

    public function __construct(
        EntityManagerInterface $em,
        EventDispatcherInterface $eventDispatcher
    ) {
        $this->em = $em;
        $this->eventDispatcher = $eventDispatcher;
    }

    public function saveAll(array $objects, array $options = []): void
    {
        foreach ($objects as $object) {
            $this->validateReferenceData($object);
            $this->eventDispatcher->dispatch(StorageEvents::PRE_SAVE, new GenericEvent($object, $options));
            $this->em->persist($object);
        }
        $this->em->flush();

        foreach ($objects as $object) {
            $this->eventDispatcher->dispatch(StorageEvents::POST_SAVE, new GenericEvent($object, $options));
        }

        $this->eventDispatcher->dispatch(StorageEvents::POST_SAVE_ALL, new GenericEvent($objects, $options));
    }

    /**
     * {@inheritdoc}
     */
    public function save($object, array $options = []): void
    {
        $this->validateReferenceData($object);
        $this->eventDispatcher->dispatch(StorageEvents::PRE_SAVE, new GenericEvent($object, $options));

        $this->em->persist($object);
        $this->em->flush();
        $this->em->clear();

        $this->eventDispatcher->dispatch(StorageEvents::POST_SAVE, new GenericEvent($object, $options));
    }

    private function validateReferenceData(object $referenceData): void
    {
        if (!$referenceData instanceof ReferenceDataInterface) {
            throw new \InvalidArgumentException(
                sprintf(
                    'Expects a "Akeneo\Pim\Enrichment\Component\Product\Model\ReferenceDataInterface", "%s" provided.',
                    ClassUtils::getClass($referenceData)
                )
            );
        }
    }
}