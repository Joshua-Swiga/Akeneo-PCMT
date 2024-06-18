<?php
/**
 * Copyright (c) 2020, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

declare(strict_types=1);

namespace PcmtDraftBundle\Connector\Job\JobParameters\DefaultValueProvider;

use Akeneo\Tool\Component\Batch\Job\JobInterface;
use Akeneo\Tool\Component\Batch\Job\JobParameters\DefaultValuesProviderInterface;
use PcmtDraftBundle\MassActions\DraftsBulkActionOperation;

class DraftsBulkActions implements DefaultValuesProviderInterface
{
    /** @var string[] */
    protected $supportedJobNames = [];

    /**
     * @param string[] $supportedJobNames
     */
    public function __construct(array $supportedJobNames)
    {
        $this->supportedJobNames = $supportedJobNames;
    }

    /**
     * {@inheritdoc}
     */
    public function getDefaultValues(): array
    {
        return [
            DraftsBulkActionOperation::KEY_EXCLUDED              => [],
            DraftsBulkActionOperation::KEY_SELECTED              => [],
            DraftsBulkActionOperation::KEY_ALL_SELECTED          => false,
            DraftsBulkActionOperation::KEY_USER_TO_NOTIFY        => null,
            DraftsBulkActionOperation::KEY_IS_USER_AUTHENTICATED => false,
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function supports(JobInterface $job): bool
    {
        return in_array($job->getName(), $this->supportedJobNames);
    }
}
