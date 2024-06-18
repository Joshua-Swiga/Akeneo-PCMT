<?php

declare(strict_types=1);

/*
 * Copyright (c) 2021, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

namespace PcmtRulesBundle\Listener;

use PcmtRulesBundle\Event\ProductChangedEvent;
use PcmtRulesBundle\Event\ProductModelChangedEvent;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class LoggerSubscriber implements EventSubscriberInterface
{
    /** @var LoggerInterface */
    protected $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            ProductChangedEvent::NAME       => 'productValueUpdated',
            ProductModelChangedEvent::NAME  => 'productModelValueUpdated',
        ];
    }

    public function productValueUpdated(ProductChangedEvent $event): void
    {
        $this->logger->info(sprintf(
            'Product: %s, attribute: %s, locale: %s, scope: %s, previousValue: %s, newValue: %s',
            $event->getProduct()->getIdentifier(),
            $event->getAttribute()->getCode(),
            $event->getLocaleCode(),
            $event->getScopeCode(),
            $event->getPreviousValue(),
            $event->getNewValue()
        ));
    }

    public function productModelValueUpdated(ProductModelChangedEvent $event): void
    {
        $this->logger->info(sprintf(
            'Product model: %s, attribute: %s, locale: %s, scope: %s, previousValue: %s, newValue: %s',
            $event->getProductModel()->getCode(),
            $event->getAttribute()->getCode(),
            $event->getLocaleCode(),
            $event->getScopeCode(),
            $event->getPreviousValue(),
            $event->getNewValue()
        ));
    }
}
