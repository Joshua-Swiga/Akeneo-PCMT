<?php
/**
 * Copyright (c) 2019, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

declare(strict_types=1);

namespace PcmtCoreBundle\Connector\Job\JobParameters\DefaultValueProvider;

use Akeneo\Tool\Component\Batch\Job\JobInterface;
use Akeneo\Tool\Component\Batch\Job\JobParameters\DefaultValuesProviderInterface;

class ProductE2openExport implements DefaultValuesProviderInterface
{
    /** @var string[] */
    private $supportedJobNames = [];

    /** @var DefaultValuesProviderInterface */
    private $baseDefaultValueProvider;

    public function __construct(
        DefaultValuesProviderInterface $baseDefaultValueProvider,
        array $supportedJobNames
    ) {
        $this->baseDefaultValueProvider = $baseDefaultValueProvider;
        $this->supportedJobNames = $supportedJobNames;
    }

    /**
     * {@inheritdoc}
     */
    public function getDefaultValues(): array
    {
        $parameters = $this->baseDefaultValueProvider->getDefaultValues();
        $parameters['filters']['data'] = [];

        return $parameters;
    }

    /**
     * {@inheritdoc}
     */
    public function supports(JobInterface $job)
    {
        return in_array($job->getName(), $this->supportedJobNames);
    }
}
