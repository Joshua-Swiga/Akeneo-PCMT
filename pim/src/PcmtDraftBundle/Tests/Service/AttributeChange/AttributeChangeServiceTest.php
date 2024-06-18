<?php
/**
 * Copyright (c) 2020, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

declare(strict_types=1);

namespace PcmtDraftBundle\Tests\Service\AttributeChange;

use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Pim\Structure\Component\Repository\AttributeRepositoryInterface;
use Akeneo\Tool\Component\Versioning\Model\VersionableInterface;
use PcmtDraftBundle\Entity\AttributeChange;
use PcmtDraftBundle\Service\AttributeChange\AttributeChangeService;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Serializer\Serializer;

class AttributeChangeServiceTest extends TestCase
{
    /** @var MockObject|VersionableInterface */
    private $objectNewMock;

    /** @var MockObject|VersionableInterface */
    private $objectPreviousMock;

    /** @var MockObject|Serializer */
    private $versioningSerializerMock;

    /** @var MockObject|AttributeRepositoryInterface */
    protected $attributeRepositoryMock;

    protected function setUp(): void
    {
        $this->objectNewMock = $this->createMock(VersionableInterface::class);
        $this->objectPreviousMock = $this->createMock(VersionableInterface::class);
        $this->versioningSerializerMock = $this->createMock(Serializer::class);
        $this->attributeRepositoryMock = $this->createMock(AttributeRepositoryInterface::class);
        parent::setUp();
    }

    private function getAttributeChangeServiceInstance(): AttributeChangeService
    {
        return new AttributeChangeService(
            $this->versioningSerializerMock,
            $this->attributeRepositoryMock
        );
    }

    /**
     * @dataProvider dataGetWithAttributesNames
     */
    public function testGetWithAttributesNames(array $normalizedNewObject, ?AttributeInterface $attributeInstance, string $expectedAttributeName): void
    {
        $this->versioningSerializerMock->method('normalize')->willReturn($normalizedNewObject);
        $this->attributeRepositoryMock->method('findOneByIdentifier')->willReturn($attributeInstance);

        $attributeChangeService = $this->getAttributeChangeServiceInstance();
        $changes = $attributeChangeService->get($this->createMock(ProductInterface::class), null);

        $this->assertIsArray($changes);
        $this->assertCount(count($normalizedNewObject), $changes);
        /** @var AttributeChange $change */
        $change = reset($changes);
        $this->assertInstanceOf(AttributeChange::class, $change);
        $this->assertSame($expectedAttributeName, $change->getAttributeName());
    }

    public function dataGetWithAttributesNames(): array
    {
        $attributeLabel = 'attributeLabel';
        $attributeInstanceMock = $this->createMock(AttributeInterface::class);
        $attributeInstanceMock->method('getLabel')->willReturn($attributeLabel);

        $attributeCode2 = 'attrCode2';

        return [
            'an attribute'     => [
                [
                    'attributeCode1' => 'attributeValue1',
                    'attri'          => 'val',
                ],
                $attributeInstanceMock,
                $attributeLabel,
            ],
            'not an attribute' => [
                [$attributeCode2 => 'attributeValue2'],
                null,
                $attributeCode2,
            ],
        ];
    }

    public function testGetEmpty(): void
    {
        $service = $this->getAttributeChangeServiceInstance();
        $changes = $service->get(null, null);
        $this->assertIsArray($changes);
        $this->assertEmpty($changes);
    }

    /**
     * @dataProvider dataGetPreviousObjectEmpty
     */
    public function testGetPreviousObjectEmpty(array $normalizedData): void
    {
        $this->versioningSerializerMock->method('normalize')->willReturn($normalizedData);

        $service = $this->getAttributeChangeServiceInstance();
        $changes = $service->get($this->objectNewMock, null);
        $this->assertIsArray($changes);
        $this->assertCount(count($normalizedData), $changes);
    }

    public function dataGetPreviousObjectEmpty(): array
    {
        return [
            [[]],
            [['attribute1' => 'value1']],
            [[
                'attribute1' => 'value1',
                'attribute2' => 'value2',
            ]],
        ];
    }

    public function testGetTwoSameProducts(): void
    {
        $this->versioningSerializerMock->method('normalize')->willReturn(['attribute1' => 'value1']);

        $service = $this->getAttributeChangeServiceInstance();
        $changes = $service->get($this->objectNewMock, $this->objectPreviousMock);
        $this->assertEmpty($changes);
    }

    public function testGetTwoDifferentProducts(): void
    {
        $this->versioningSerializerMock->method('normalize')
            ->will($this->onConsecutiveCalls(
                [
                    'attribute1' => 'value1',
                    'attribute2' => 'value2',
                ],
                [
                    'attribute1' => 'value3',
                    'attribute3' => 'value3',
                ]
            ));

        $service = $this->getAttributeChangeServiceInstance();
        $changes = $service->get($this->objectNewMock, $this->objectPreviousMock);
        $this->assertNotEmpty($changes);
        $this->assertCount(3, $changes);
    }
}