<?php

declare(strict_types=1);

/*
 * Copyright (c) 2020, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

namespace PcmtCISBundle\Tests\Formatter;

use Akeneo\Tool\Component\Localization\Presenter\PresenterInterface;
use Akeneo\UserManagement\Bundle\Context\UserContext;
use PcmtCISBundle\Formatter\DateTimeProperty;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Translation\TranslatorInterface;

class DateTimePropertyTest extends TestCase
{
    /** @var TranslatorInterface|MockObject */
    private $translatorMock;

    /** @var PresenterInterface|MockObject */
    private $presenterMock;

    /** @var UserContext|MockObject */
    private $userContextMock;

    protected function setUp(): void
    {
        $this->translatorMock = $this->createMock(TranslatorInterface::class);
        $this->presenterMock = $this->createMock(PresenterInterface::class);
        $this->userContextMock = $this->createMock(UserContext::class);
    }

    public function testConvertValue(): void
    {
        $value = new \DateTime('2010-10-20 20:00:00');

        $this->translatorMock->expects($this->once())->method('getLocale')->willReturn('en-us');
        $this->userContextMock->expects($this->once())->method('getUserTimezone')->willReturn('Europe/Warsaw');

        $this->presenterMock->expects($this->once())->method('present')->willReturn($result = 'dddd');

        $property = $this->getPropertyInstance();
        $reflection = new \ReflectionClass(get_class($property));
        $method = $reflection->getMethod('convertValue');
        $method->setAccessible(true);
        $realResult = $method->invokeArgs($property, [$value]);

        $this->assertEquals($result, $realResult);
    }

    public function getPropertyInstance(): DateTimeProperty
    {
        return new DateTimeProperty(
            $this->translatorMock,
            $this->presenterMock,
            $this->userContextMock
        );
    }
}