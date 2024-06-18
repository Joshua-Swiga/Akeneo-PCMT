<?php
/**
 * Copyright (c) 2020, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

declare(strict_types=1);

namespace PcmtCISBundle\Tests\TestDataBuilder;

use PcmtCISBundle\Entity\Subscription;
use PcmtCoreBundle\Entity\ReferenceData\CountryCode;

class SubscriptionBuilder
{
    public const EXAMPLE_ID = 55;

    /** @var Subscription */
    private $subscription;

    public function __construct()
    {
        $this->subscription = new Subscription();

        $this->setId($this->subscription, self::EXAMPLE_ID);

        $this->subscription->setDataSourcesGLN('datasourceGLN');
        $this->subscription->setDataRecipientsGLN('datarecipientsGLN');
        $this->subscription->setGPCCategoryCode('categoryCOde');
        $this->subscription->setGTIN('78264824');
    }

    protected function setId(Subscription $subscription, int $value): void
    {
        $reflection = new \ReflectionClass(get_class($subscription));
        $property = $reflection->getProperty('id');
        $property->setAccessible(true);
        $property->setValue($subscription, $value);
    }

    public function withGTIN(string $gtin): self
    {
        $this->subscription->setGTIN($gtin);

        return $this;
    }

    public function withGPCCategoryCode(string $code): self
    {
        $this->subscription->setGPCCategoryCode($code);

        return $this;
    }

    public function withTargetMarketCountryCode(?CountryCode $code): self
    {
        $this->subscription->setTargetMarketCountryCode($code);

        return $this;
    }

    public function withDataSourcesGLN(string $dataSourcesGLN): self
    {
        $this->subscription->setDataSourcesGLN($dataSourcesGLN);

        return $this;
    }

    public function withDataRecipientsGLN(string $dataRecipientsGLN): self
    {
        $this->subscription->setDataRecipientsGLN($dataRecipientsGLN);

        return $this;
    }

    public function build(): Subscription
    {
        return $this->subscription;
    }
}