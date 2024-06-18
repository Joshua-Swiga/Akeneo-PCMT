<?php
/**
 * Copyright (c) 2019, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

declare(strict_types=1);

namespace PcmtCoreBundle\Connector\Job\JobParameters\ConstraintCollectionProvider;

use Akeneo\Tool\Component\Batch\Job\JobInterface;
use Akeneo\Tool\Component\Batch\Job\JobParameters\ConstraintCollectionProviderInterface;
use Symfony\Component\Validator\Constraints\Collection;

class ComputeConcatenatedAttributeValuesOfProducts implements ConstraintCollectionProviderInterface
{
    public function getConstraintCollection(): Collection
    {
        return new Collection(
            [
                'fields' => [
                    'concatenatedAttributesToUpdate' => null,
                    'family_code'                    => null,
                ],
            ]
        );
    }

    public function supports(JobInterface $job): bool
    {
        return 'compute_concatenated_attributes_values' === $job->getName();
    }
}