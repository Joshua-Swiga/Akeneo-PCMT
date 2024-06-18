<?php

declare(strict_types=1);

/*
 * Copyright (c) 2020, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

namespace PcmtCISBundle\Tests\Updater;

use Akeneo\Pim\Structure\Component\Model\Attribute;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidObjectException;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidPropertyException;
use Doctrine\ORM\EntityManagerInterface;
use PcmtCISBundle\Tests\TestDataBuilder\SubscriptionBuilder;
use PcmtCISBundle\Updater\SubscriptionUpdater;
use PcmtCoreBundle\Entity\ReferenceData\CountryCode;
use PcmtCoreBundle\Repository\GS1CodesRepository;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class SubscriptionUpdaterTest extends TestCase
{
    /** @var EntityManagerInterface|MockObject */
    private $entityManagerMock;

    /** @var GS1CodesRepository|MockObject */
    private $referenceDataRepositoryMock;

    protected function setUp(): void
    {
        $this->referenceDataRepositoryMock = $this->createMock(GS1CodesRepository::class);
        $this->entityManagerMock = $this->createMock(EntityManagerInterface::class);
        $this->entityManagerMock->method('getRepository')->willReturn($this->referenceDataRepositoryMock);
    }

    /**
     * @dataProvider dataUpdate
     */
    public function testUpdate(
        string $dataRecipientsGLN,
        string $dataSourcesGLN,
        string $gtin,
        string $GPCCategoryCode,
        string $countryCode
    ): void {
        $countryCodeObject = new CountryCode();
        $countryCodeObject->setCode($countryCode);

        $this->referenceDataRepositoryMock
            ->expects($this->exactly(1))
            ->method('findOneByIdentifier')
            ->withConsecutive([$countryCode])
            ->willReturnOnConsecutiveCalls($countryCodeObject);

        $subscription = (new SubscriptionBuilder())->build();
        $data = [
            'data_recipients_g_l_n'      => $dataRecipientsGLN,
            'data_sources_g_l_n'         => $dataSourcesGLN,
            'g_t_i_n'                    => $gtin,
            'g_p_c_category_code'        => $GPCCategoryCode,
            'target_market_country_code' => $countryCode,
        ];
        $updater = $this->getSubscriptionUpdaterInstance();
        $updater->update($subscription, $data);

        $this->assertSame($dataRecipientsGLN, $subscription->getDataRecipientsGLN());
        $this->assertSame($gtin, $subscription->getGTIN());
        $this->assertSame($countryCode, $subscription->getTargetMarketCountryCode()->getCode());
    }

    /**
     * @dataProvider dataUpdate
     */
    public function testUpdateWrongTargetMarketCountryCode(
        string $dataRecipientsGLN,
        string $dataSourcesGLN,
        string $gtin,
        string $GPCCategoryCode,
        string $countryCode
    ): void {
        $this->expectException(InvalidPropertyException::class);
        $this->referenceDataRepositoryMock
            ->expects($this->exactly(1))
            ->method('findOneByIdentifier')
            ->withConsecutive([$countryCode])
            ->willReturnOnConsecutiveCalls(null);

        $subscription = (new SubscriptionBuilder())->build();
        $data = [
            'data_recipients_g_l_n'      => $dataRecipientsGLN,
            'data_sources_g_l_n'         => $dataSourcesGLN,
            'g_t_i_n'                    => $gtin,
            'g_p_c_category_code'        => $GPCCategoryCode,
            'target_market_country_code' => $countryCode,
        ];
        $updater = $this->getSubscriptionUpdaterInstance();
        $updater->update($subscription, $data);
    }

    public function dataUpdate(): array
    {
        return [
            [
                'gln1',
                'gln2',
                'gtin222',
                'gpccategorycode22',
                'countrycode234',
            ],
        ];
    }

    public function testUpdateWrongObject(): void
    {
        $this->expectException(InvalidObjectException::class);
        $object = new Attribute();
        $data = [];
        $updater = $this->getSubscriptionUpdaterInstance();
        $updater->update($object, $data);
    }

    private function getSubscriptionUpdaterInstance(): SubscriptionUpdater
    {
        return new SubscriptionUpdater($this->entityManagerMock);
    }
}