<?php

declare(strict_types=1);

/*
 * Copyright (c) 2020, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

namespace PcmtCISBundle\Tests\Saver;

use Akeneo\Pim\Structure\Component\Model\Attribute;
use Doctrine\Common\Persistence\ObjectManager;
use PcmtCISBundle\Saver\SubscriptionSaver;
use PcmtCISBundle\Tests\TestDataBuilder\SubscriptionBuilder;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class SubscriptionSaverTest extends TestCase
{
    /** @var ObjectManager|MockObject */
    private $objectManagerMock;

    /** @var EventDispatcherInterface|MockObject */
    private $eventDispatcherMock;

    protected function setUp(): void
    {
        $this->objectManagerMock = $this->createMock(ObjectManager::class);
        $this->eventDispatcherMock = $this->createMock(EventDispatcherInterface::class);
    }

    public function testSave(): void
    {
        $subscription = (new SubscriptionBuilder())->build();

        $this->objectManagerMock->expects($this->once())->method('persist')->with($subscription);
        $this->objectManagerMock->expects($this->once())->method('flush');
        $this->eventDispatcherMock->expects($this->exactly(2))->method('dispatch');

        $saver = $this->getSubscriptionSaverInstance();
        $saver->save($subscription);
    }

    public function testSaveWrongObject(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $object = new Attribute();
        $saver = $this->getSubscriptionSaverInstance();
        $saver->save($object);
    }

    private function getSubscriptionSaverInstance(): SubscriptionSaver
    {
        return new SubscriptionSaver($this->objectManagerMock, $this->eventDispatcherMock);
    }
}