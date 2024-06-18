<?php
/**
 * Copyright (c) 2019, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

declare(strict_types=1);

namespace PcmtCoreBundle\Tests\Updater;

use Akeneo\Pim\Structure\Component\Updater\AttributeUpdater as BaseAttributeUpdater;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidObjectException;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidPropertyTypeException;
use PcmtCoreBundle\Entity\Attribute;
use PcmtCoreBundle\Updater\AttributeUpdater;
use PcmtCoreBundle\Updater\ConcatenatedAttributeUpdater;
use PcmtCoreBundle\Updater\TranslatableUpdater;
use PcmtDraftBundle\Entity\DraftInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class AttributeUpdaterTest extends TestCase
{
    /** @var TranslatableUpdater|MockObject */
    protected $translatableUpdaterMock;

    /** @var Attribute */
    protected $attribute;

    /** @var ConcatenatedAttributeUpdater|MockObject */
    protected $concatenatedAttributeCreatorMock;

    /** @var MockObject|BaseAttributeUpdater */
    private $baseAttributeUpdaterMock;

    protected function setUp(): void
    {
        $this->baseAttributeUpdaterMock = $this->createMock(BaseAttributeUpdater::class);
        $this->translatableUpdaterMock = $this->createMock(TranslatableUpdater::class);
        $this->concatenatedAttributeCreatorMock = $this->createMock(ConcatenatedAttributeUpdater::class);
        $this->attribute = new Attribute();

        parent::setUp();
    }

    private function getAttributeUpdaterInstance(): AttributeUpdater
    {
        return new AttributeUpdater(
            $this->baseAttributeUpdaterMock,
            $this->translatableUpdaterMock,
            $this->concatenatedAttributeCreatorMock
        );
    }

    /**
     * @dataProvider dataUpdate
     */
    public function testUpdate(array $data, int $externalFields): void
    {
        $this->baseAttributeUpdaterMock->expects($this->exactly($externalFields))->method('update');
        if (isset($data['descriptions'])) {
            $this->translatableUpdaterMock->expects($this->once())->method('updateDescription');
        } else {
            $this->translatableUpdaterMock->expects($this->never())->method('updateDescription');
        }
        if (isset($data['concatenated'])) {
            $this->concatenatedAttributeCreatorMock->expects($this->once())->method('update');
        } else {
            $this->concatenatedAttributeCreatorMock->expects($this->never())->method('update');
        }
        $updater = $this->getAttributeUpdaterInstance();
        $updater->update($this->attribute, $data, []);
    }

    public function dataUpdate(): array
    {
        return [
            'single description' => [
                [
                    'descriptions' => [
                        'en_US' => 'alo',
                    ],
                ],
                'externalFields' => 0,
            ],
            'multi description' => [
                [
                    'descriptions' => [
                        'en_US' => 'alo',
                        'de'    => 'lol',
                    ],
                ],
                'externalFields' => 0,
            ],
            'multi description with other data' => [
                [
                    'descriptions' => [
                        'en_US' => 'alo',
                        'de'    => 'lol',
                    ],
                    'concatenated' => [
                        'xxx',
                    ],
                    'code'           => 'test',
                    'externalField2' => 'xxx',
                ],
                'externalFields' => 2,
            ],
            'empty array' => [
                [
                    'descriptions' => [],
                ],
                'externalFields' => 0,
            ],
        ];
    }

    public function testUpdateFunctionShouldThrowExceptionWhenWrongAttributeClassType(): void
    {
        $attributeUpdater = $this->getAttributeUpdaterInstance();
        $this->expectException(InvalidObjectException::class);
        $attributeUpdater->update($this->createMock(DraftInterface::class), []);
    }

    /**
     * @dataProvider dataUpdateInvalidPropertyType
     */
    public function testUpdateInvalidData(array $data): void
    {
        $attributeUpdater = $this->getAttributeUpdaterInstance();
        $this->expectException(InvalidPropertyTypeException::class);
        $attributeUpdater->update($this->attribute, $data);
    }

    public function dataUpdateInvalidPropertyType(): array
    {
        return [
            'not an array' => [
                ['descriptions' => 'en_US'],
            ],
            'not an array - concatenated' => [
                ['concatenated' => 'xxx'],
            ],
            'not a scalar' => [
                [
                    'descriptions' => [
                        [],
                    ],
                ],
            ],
            'not a scalar - concatenated' => [
                [
                    'concatenated' => [
                        [],
                    ],
                ],
            ],
            'one is not a scalar' => [
                [
                    'descriptions' => [
                        'en_US' => 'alo',
                        'de'    => [],
                    ],
                ],
            ],
        ];
    }
}