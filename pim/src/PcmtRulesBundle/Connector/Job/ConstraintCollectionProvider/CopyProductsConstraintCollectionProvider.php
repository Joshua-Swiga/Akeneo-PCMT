<?php
/**
 * Copyright (c) 2020, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

declare(strict_types=1);

namespace PcmtRulesBundle\Connector\Job\ConstraintCollectionProvider;

use Akeneo\Tool\Component\Batch\Job\JobParameters\ConstraintCollectionProviderInterface;
use PcmtRulesBundle\Connector\Job\Step\CopyProductsRuleStep;
use PcmtRulesBundle\Constraints\CorrectAttributeMappingConstraint;
use PcmtRulesBundle\Constraints\DifferentFamilyConstraint;
use PcmtRulesBundle\Constraints\FamilyHasNoVariantsConstraint;
use PcmtRulesBundle\Constraints\FamilyHasVariantsConstraint;
use PcmtSharedBundle\Connector\Job\JobParameters\SupportedJobsTrait;
use Symfony\Component\Validator\Constraints\Collection;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Type;

class CopyProductsConstraintCollectionProvider implements ConstraintCollectionProviderInterface
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
                    CopyProductsRuleStep::PARAM_SOURCE_FAMILY      => [
                        new NotBlank(),
                        new Type('string'),
                        new DifferentFamilyConstraint(),
                        new FamilyHasNoVariantsConstraint(),
                    ],
                    CopyProductsRuleStep::PARAM_DESTINATION_FAMILY => [
                        new NotBlank(),
                        new Type('string'),
                        new DifferentFamilyConstraint(),
                        new FamilyHasVariantsConstraint(),
                    ],
                    'user_to_notify'    => [
                        new Type('string'),
                    ],
                    CopyProductsRuleStep::PARAM_ATTRIBUTE_MAPPING => [
                        new Type('array'),
                        new CorrectAttributeMappingConstraint(),
                    ],
                ],
            ]
        );
    }
}
