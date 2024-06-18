<?php

declare(strict_types=1);

/*
 * Copyright (c) 2021, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

namespace PcmtRulesBundle\Tests\Listener;

use PcmtRulesBundle\Listener\LoggerSubscriber;
use PcmtRulesBundle\Tests\TestDataBuilder\ProductChangedEventBuilder;
use PcmtRulesBundle\Tests\TestDataBuilder\ProductModelChangedEventBuilder;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class LoggerSubscriberTest extends TestCase
{
    /** @var LoggerInterface|MockObject */
    private $loggerMock;

    protected function setUp(): void
    {
        $this->loggerMock = $this->createMock(LoggerInterface::class);
    }

    public function testProductValueUpdated(): void
    {
        $this->loggerMock->expects($this->once())->method('info');

        $s = new LoggerSubscriber($this->loggerMock);
        $s->productValueUpdated(
            (new ProductChangedEventBuilder())->build()
        );
    }

    public function testProductModelValueUpdated(): void
    {
        $this->loggerMock->expects($this->once())->method('info');

        $s = new LoggerSubscriber($this->loggerMock);
        $s->productModelValueUpdated(
            (new ProductModelChangedEventBuilder())->build()
        );
    }

    public function testGetSubscribedEvents(): void
    {
        $this->assertIsArray(LoggerSubscriber::getSubscribedEvents());
    }
}