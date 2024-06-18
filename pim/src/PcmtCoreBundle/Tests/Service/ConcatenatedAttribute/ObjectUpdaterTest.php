<?php
/**
 * Copyright (c) 2020, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

declare(strict_types=1);

namespace PcmtCoreBundle\Tests\Service\ConcatenatedAttribute;

use Akeneo\Channel\Bundle\Doctrine\Repository\ChannelRepository;
use Akeneo\Channel\Bundle\Doctrine\Repository\LocaleRepository;
use Akeneo\Channel\Component\Repository\ChannelRepositoryInterface;
use Akeneo\Channel\Component\Repository\LocaleRepositoryInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\Product;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModel;
use Akeneo\Pim\Enrichment\Component\Product\Model\WriteValueCollection;
use Akeneo\Pim\Enrichment\Component\Product\Updater\EntityWithValuesUpdater;
use Akeneo\Pim\Enrichment\Component\Product\Value\MetricValue;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use PcmtCoreBundle\Entity\Attribute;
use PcmtCoreBundle\Extension\ConcatenatedAttribute\Structure\Component\AttributeType\PcmtAtributeTypes;
use PcmtCoreBundle\Service\ConcatenatedAttribute\ObjectUpdater;
use PHPUnit\Framework\MockObject\MockObject as Mock;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\Serializer;

class ObjectUpdaterTest extends TestCase
{
    /** @var EntityWithValuesUpdater|Mock */
    private $entityWithValuesUpdater;

    /** @var NormalizerInterface |Mock */
    private $rawValuesStorageFormatNormalizer;

    /** @var ChannelRepositoryInterface|Mock */
    private $channelRepositoryMock;

    /** @var LocaleRepositoryInterface|Mock */
    private $localeRepositoryMock;

    protected function setUp(): void
    {
        $this->entityWithValuesUpdater = $this->createMock(
            EntityWithValuesUpdater::class
        );
        $this->rawValuesStorageFormatNormalizer = $this->createMock(
            Serializer::class
        );

        $this->channelRepositoryMock = $this->createMock(ChannelRepository::class);
        $this->localeRepositoryMock = $this->createMock(LocaleRepository::class);
    }

    /**
     * @dataProvider getValidProductDataToUpdate
     */
    public function testUpdate(object $mockProductEntityObject, array $parameters, array $normalized): void
    {
        $updater = $this->getTestedInstance();

        $valueMock = $this->createMock(MetricValue::class);

        $mockProductEntityObject->expects($this->exactly(2))
            ->method('hasAttribute')
            ->willReturn(true);

        $mockProductEntityObject->expects($this->exactly(2))
            ->method('getValue')
            ->willReturn($valueMock);

        $mockProductEntityObject->expects($this->any())
            ->method('getValuesForVariation')
            ->willReturn(new WriteValueCollection());

        $valueMock->expects($this->atLeastOnce())
            ->method('__toString')
            ->willReturn('attrValue');

        $this->rawValuesStorageFormatNormalizer->expects($this->any())
            ->method('normalize')
            ->willReturn($normalized);

        $this->entityWithValuesUpdater->expects($this->any())
            ->method('update')
            ->with($mockProductEntityObject, $normalized);

        $concatenatedAttribute = $parameters['concatenatedAttribute'];

        if ($concatenatedAttribute->isScopable()) {
            $this->channelRepositoryMock->expects($this->once())
                ->method('getChannelCodes')
                ->willReturn(['GFP_VAN']);
        }

        if ($concatenatedAttribute->isLocalizable()) {
            $this->localeRepositoryMock->expects($this->once())
                ->method('getActivatedLocaleCodes')
                ->willReturn(['en_US']);
        }

        $updater->update($mockProductEntityObject, $parameters);
    }

    /**
     * @dataProvider getInvalidProductDataToUpdate
     */
    public function testThrowsExceptionWhenInvalidData(object $object, array $parameters): void
    {
        $updater = $this->getTestedInstance();
        $this->expectException(\InvalidArgumentException::class);

        $updater->update($object, $parameters);
    }

    public function getValidProductDataToUpdate(): array
    {
        $memberAttribute1 = new Attribute();
        $memberAttribute1->setCode('Weight');

        $memberAttribute2 = new Attribute();
        $memberAttribute2->setCode('Height');

        $memberAttributes = [
            $memberAttribute1,
            $memberAttribute2,
        ];

        return [
            [
                $this->createMock(Product::class),
                [
                    'concatenatedAttribute'  => $this->getConcatenatedAttributeInstance($memberAttributes, false, false),
                    'memberAttributes'       => $memberAttributes,
                ],
                [
                    'concatenated_attribute' => [
                        'data' => [
                            'data'   => ['attrValue$$$attrValue'],
                            'locale' => null,
                            'scope'  => null,
                        ],
                    ],
                ],
            ],

            [
                $this->createMock(Product::class),
                [
                    'concatenatedAttribute'  => $this->getConcatenatedAttributeInstance($memberAttributes, true, true),
                    'memberAttributes'       => $memberAttributes,
                ],
                [
                    'concatenated_attribute' => [
                        'data' => [
                            'data'   => ['attrValue$$$attrValue'],
                            'locale' => 'en_US',
                            'scope'  => 'GFP_VAN',
                        ],
                    ],
                ],
            ],

            [
                $this->createMock(ProductModel::class),
                [
                    'concatenatedAttribute'  => $this->getConcatenatedAttributeInstance($memberAttributes, false, false),
                    'memberAttributes'       => $memberAttributes,
                ],
                [
                    'concatenated_attribute' => [
                        'data' => [
                            'data'   => ['attrValue$$$attrValue'],
                            'locale' => null,
                            'scope'  => null,
                        ],
                    ],
                ],
            ],
        ];
    }

    public function getInvalidProductDataToUpdate(): array
    {
        return [
            [
                new Product(),
                [
                    'invalidKeyNotconcatenatedAttribute' => new Attribute(),
                    'memberAttributes'                   => [],
                ],
            ],
            [
                new ProductModel(),
                [
                    'concatenatedAttribute' => 'not an attribute',
                    'memberAttributes'      => [],
                ],
            ],
            [
                $this->createMock(Product::class),
                [
                    'concatenatedAttibute' => new Attribute(),
                    'memberAttributes'     => [],
                ],
            ],
            [
                new Product(),
                [
                    'concatenatedAttibute'       => new Attribute(),
                    'invalidKeyMemberAttributes' => [],
                ],
            ],
        ];
    }

    private function getConcatenatedAttributeInstance(array $memberAttributes, bool $isScopable, bool $isLocalizable): AttributeInterface
    {
        $concatenatedAttribute = new Attribute();
        $concatenatedAttribute->setType(PcmtAtributeTypes::CONCATENATED_FIELDS);
        $concatenatedAttribute->setCode('concatenated_attribute');
        $concatenatedAttribute->setProperty('attributes', implode(',', $memberAttributes));
        $concatenatedAttribute->setProperty('separators', '$$$');

        $concatenatedAttribute->setScopable($isScopable);
        $concatenatedAttribute->setLocalizable($isLocalizable);

        return $concatenatedAttribute;
    }

    private function getTestedInstance(): ObjectUpdater
    {
        return new ObjectUpdater(
            $this->entityWithValuesUpdater,
            $this->rawValuesStorageFormatNormalizer,
            $this->channelRepositoryMock,
            $this->localeRepositoryMock
        );
    }
}