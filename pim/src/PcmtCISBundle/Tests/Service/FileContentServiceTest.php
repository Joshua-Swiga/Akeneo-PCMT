<?php

declare(strict_types=1);

/*
 * Copyright (c) 2020, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

namespace PcmtCISBundle\Tests\Service;

use PcmtCISBundle\Entity\Subscription;
use PcmtCISBundle\Service\FileContentService;
use PcmtCISBundle\Tests\TestDataBuilder\CountryCodeBuilder;
use PcmtCISBundle\Tests\TestDataBuilder\SubscriptionBuilder;
use PHPUnit\Framework\TestCase;

class FileContentServiceTest extends TestCase
{
    public function testGetHeader(): void
    {
        $header = "documentCommand.type\tdataRecipient\tgtin\tgpcCategoryCode\ttargetMarketCountryCode\tdataSource";

        $service = $this->getFileContentServiceInstance();
        $this->assertEquals($header, $service->getHeader());
    }

    private function getCountryCode(Subscription $subscription): string
    {
        return $subscription->getTargetMarketCountryCode() ? $subscription->getTargetMarketCountryCode()->getCode() : '';
    }

    public function testGetSubscriptionContent(): void
    {
        $countryCode = (new CountryCodeBuilder())->withCode('008')->build();
        $subscription = (new SubscriptionBuilder())->withTargetMarketCountryCode($countryCode)->build();
        $command = 'XXXX';

        $content = "{$command}\t{$subscription->getDataRecipientsGLN()}\t{$subscription->getGTIN()}\t{$subscription->getGPCCategoryCode()}\t{$this->getCountryCode($subscription)}\t{$subscription->getDataSourcesGLN()}";

        $service = $this->getFileContentServiceInstance();
        $this->assertEquals($content, $service->getSubscriptionContent($subscription, $command));
    }

    public function getFileContentServiceInstance(): FileContentService
    {
        return new FileContentService();
    }
}