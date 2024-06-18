<?php

declare(strict_types=1);

/*
 * Copyright (c) 2020, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

namespace PcmtCISBundle\Service;

use PcmtCISBundle\Entity\Subscription;

class FileContentService
{
    public function getHeader(): string
    {
        $header = [
            'documentCommand.type',
            'dataRecipient',
            'gtin',
            'gpcCategoryCode',
            'targetMarketCountryCode',
            'dataSource',
        ];

        return implode("\t", $header);
    }

    public function getSubscriptionContent(Subscription $subscription, string $command): string
    {
        $row = [
            $command,
            $subscription->getDataRecipientsGLN(),
            $subscription->getGTIN(),
            $subscription->getGPCCategoryCode(),
            $subscription->getTargetMarketCountryCode() ? $subscription->getTargetMarketCountryCode()->getCode() : '',
            $subscription->getDataSourcesGLN(),
        ];

        return implode("\t", $row);
    }
}