<?php
/*
 * Copyright (c) 2020, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

declare(strict_types=1);

/**
 * Copyright (c) 2020, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

namespace PcmtCISBundle\Validator;

use PcmtCISBundle\Constraint\UniqueValuesConstraint;
use PcmtCISBundle\Entity\Subscription;
use PcmtCISBundle\Repository\SubscriptionRepository;
use Symfony\Component\Form\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class UniqueValuesConstraintValidator extends ConstraintValidator
{
    /** @var SubscriptionRepository */
    private $subscriptionRepository;

    public function __construct(SubscriptionRepository $subscriptionRepository)
    {
        $this->subscriptionRepository = $subscriptionRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function validate($entity, Constraint $constraint): void
    {
        if (!$constraint instanceof UniqueValuesConstraint) {
            throw new UnexpectedTypeException($constraint, UniqueValuesConstraint::class);
        }

        /** @var Subscription $entity */
        $criteria = [
            'dataRecipientsGLN'       => $entity->getDataRecipientsGLN(),
            'dataSourcesGLN'          => $entity->getDataSourcesGLN(),
            'GTIN'                    => $entity->getGTIN(),
            'GPCCategoryCode'         => $entity->getGPCCategoryCode(),
            'targetMarketCountryCode' => $entity->getTargetMarketCountryCode(),
        ];

        $count = $this->subscriptionRepository->count($criteria);
        if (0 === $count) {
            return;
        }

        $this->context->buildViolation($constraint->message)
            ->addViolation();
    }
}
