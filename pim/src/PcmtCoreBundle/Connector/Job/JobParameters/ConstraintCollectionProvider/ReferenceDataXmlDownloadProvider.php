<?php
/**
 * Copyright (c) 2020, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

declare(strict_types=1);

namespace PcmtCoreBundle\Connector\Job\JobParameters\ConstraintCollectionProvider;

use Akeneo\Tool\Component\Batch\Job\JobParameters\ConstraintCollectionProviderInterface;
use PcmtSharedBundle\Connector\Job\JobParameters\SupportedJobsTrait;
use Symfony\Component\Validator\Constraints\Collection;

class ReferenceDataXmlDownloadProvider implements ConstraintCollectionProviderInterface
{
    use SupportedJobsTrait;

    public function __construct(
        array $supportedJobNames
    ) {
        $this->supportedJobNames = $supportedJobNames;
    }

    public function getConstraintCollection(): Collection
    {
        return new Collection([
            'fields' => [
                'dirPath'  => [],
                'filePath' => [],
            ],
        ]);
    }
}