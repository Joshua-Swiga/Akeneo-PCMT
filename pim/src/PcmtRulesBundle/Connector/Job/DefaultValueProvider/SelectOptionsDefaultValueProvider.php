<?php
/**
 * Copyright (c) 2020, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

declare(strict_types=1);

namespace PcmtRulesBundle\Connector\Job\DefaultValueProvider;

use Akeneo\Tool\Component\Batch\Job\JobParameters\DefaultValuesProviderInterface;
use PcmtSharedBundle\Connector\Job\JobParameters\SupportedJobsTrait;

class SelectOptionsDefaultValueProvider implements DefaultValuesProviderInterface
{
    use SupportedJobsTrait;

    public function __construct(array $supportedJobNames)
    {
        $this->supportedJobNames = $supportedJobNames;
    }

    public function getDefaultValues(): array
    {
        return [
            'destinationAttribute' => '',
            'sourceFamily'         => '',
            'attributeCode'        => '',
            'attributeValue'       => '',
            'user_to_notify'       => null,
        ];
    }
}