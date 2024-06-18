<?php
/**
 * Copyright (c) 2020, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

declare(strict_types=1);

namespace PcmtRulesBundle\Connector\Job\ConstraintCollectionProvider;

use Akeneo\Tool\Component\Batch\Job\JobParameters\ConstraintCollectionProviderInterface;
use PcmtRulesBundle\Constraints\CorrectAttributeMappingConstraint;
use PcmtRulesBundle\Constraints\CorrectKeyAttributeConstraint;
use PcmtRulesBundle\Constraints\DifferentFamilyConstraint;
use PcmtSharedBundle\Connector\Job\JobParameters\SupportedJobsTrait;
use Symfony\Component\Validator\Constraints\Collection;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Type;

class FamilyToFamilyConstraintCollectionProvider implements ConstraintCollectionProviderInterface
{
    use SupportedJobsTrait;

    public function __construct(array $supportedJobNames)
    {
        $this->supportedJobNames = $supportedJobNames;
    }

    public function getConstraintCollection(): Collection
    {
        return new Collection(
            [
                'fields' => [
                    'sourceFamily'      => [
                        new NotBlank(),
                        new Type('string'),
                        new DifferentFamilyConstraint(),
                    ],
                    'destinationFamily' => [
                        new NotBlank(),
                        new Type('string'),
                        new DifferentFamilyConstraint(),
                    ],
                    'keyAttribute'      => [
                        new Type('array'),
                        new CorrectKeyAttributeConstraint(),
                    ],
                    'user_to_notify'    => [
                        new Type('string'),
                    ],
                    'attributeMapping' => [
                        new Type('array'),
                        new CorrectAttributeMappingConstraint(),
                    ],
                ],
            ]
        );
    }
}
