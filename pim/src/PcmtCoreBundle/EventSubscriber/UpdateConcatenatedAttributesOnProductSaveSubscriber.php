<?php
/**
 * Copyright (c) 2019, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

declare(strict_types=1);

namespace PcmtCoreBundle\EventSubscriber;

use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;
use Akeneo\Pim\Structure\Component\Repository\AttributeRepositoryInterface;
use Akeneo\Tool\Component\StorageUtils\StorageEvents;
use Akeneo\Tool\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use PcmtCoreBundle\Extension\ConcatenatedAttribute\Structure\Component\Value\ConcatenatedAttributeValue;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\EventDispatcher\GenericEvent;

class UpdateConcatenatedAttributesOnProductSaveSubscriber implements EventSubscriberInterface
{
    /** @var AttributeRepositoryInterface */
    private $attributeRepository;

    /** @var ObjectUpdaterInterface */
    private $concatenatedAttributesUpdater;

    public function __construct(
        AttributeRepositoryInterface $attributeRepository,
        ObjectUpdaterInterface $concatenatedAttributesUpdater
    ) {
        $this->attributeRepository = $attributeRepository;
        $this->concatenatedAttributesUpdater = $concatenatedAttributesUpdater;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            StorageEvents::PRE_SAVE => 'updateConcatenatedAttributesValues',
        ];
    }

    public function updateConcatenatedAttributesValues(GenericEvent $event): void
    {
        $subject = $event->getSubject();

        if (!($subject instanceof ProductInterface || $subject instanceof ProductModelInterface)) {
            return;
        }

        foreach ($subject->getValues() as $attributeValue) {
            if (ConcatenatedAttributeValue::class === get_class($attributeValue)) {
                $concatenatedAttribute = $this->attributeRepository->findOneBy(
                    [
                        'code' => $attributeValue->getAttributeCode(),
                    ]
                );
                $memberAttributes = $this->attributeRepository->findBy(
                    [
                        'code' => explode(',', $concatenatedAttribute->getProperty('attributes')),
                    ]
                );
                $this->concatenatedAttributesUpdater->update(
                    $subject,
                    [
                        'concatenatedAttribute' => $concatenatedAttribute,
                        'memberAttributes'      => $memberAttributes,
                    ]
                );
            }
        }
    }
}