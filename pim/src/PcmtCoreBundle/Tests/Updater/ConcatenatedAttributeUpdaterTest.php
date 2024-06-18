<?php
/**
 * Copyright (c) 2020, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */
declare(strict_types=1);

namespace PcmtCoreBundle\Tests\Updater;

use Monolog\Logger;
use PcmtCoreBundle\Entity\Attribute;
use PcmtCoreBundle\Extension\ConcatenatedAttribute\Structure\Component\AttributeType\PcmtAtributeTypes;
use PcmtCoreBundle\Updater\ConcatenatedAttributeUpdater;
use PHPUnit\Framework\MockObject\MockObject as Mock;
use PHPUnit\Framework\TestCase;

class ConcatenatedAttributeUpdaterTest extends TestCase
{
    /** @var Attribute|Mock */
    private $attributeMock;

    /** @var Logger|Mock */
    private $loggerMock;

    protected function setUp(): void
    {
        $this->attributeMock = $this->createMock(Attribute::class);
        $this->loggerMock = $this->createMock(Logger::class);

        $this->attributeMock->expects($this->any())
            ->method('getId')
            ->willReturn(1);
    }

    /**
     * @dataProvider dataUpdate
     */
    public function testUpdate(string $separator, string $attribute1, string $attribute2): void
    {
        $data = [
            'separator'  => $separator,
            'attribute1' => $attribute1,
            'attribute2' => $attribute2,
        ];
        $attribute = new Attribute();
        $attribute->setType(PcmtAtributeTypes::CONCATENATED_FIELDS);

        $this->loggerMock->expects($this->never())->method('error');

        $concatenatedAttributeUpdater = new ConcatenatedAttributeUpdater($this->loggerMock);
        $concatenatedAttributeUpdater->update($attribute, $data);

        $properties = $attribute->getProperties();
        $this->assertSame($separator, $properties['separators']);
        $this->assertSame($attribute1. ',' .$attribute2, $properties['attributes']);
    }

    public function dataUpdate(): array
    {
        return [
            [
                'separator'  => '.',
                'attribute1' => 'Weight',
                'attribute2' => 'Height',
            ],
            [
                'separator'  => '&',
                'attribute1' => 'Wght',
                'attribute2' => 'Hght',
            ],
        ];
    }

    public function testShouldReturnNonChangedAttributeWhenInvalidParameters(): void
    {
        $data = ['xxx' => 'yyy'];

        $attribute = new Attribute();
        $attribute->setType('Invalid Type');

        $this->loggerMock->expects($this->once())->method('error');

        $concatenatedAttributeUpdater = new ConcatenatedAttributeUpdater($this->loggerMock);
        $return = $concatenatedAttributeUpdater->update($attribute, $data);

        $this->assertSame($attribute, $return);
    }
}
