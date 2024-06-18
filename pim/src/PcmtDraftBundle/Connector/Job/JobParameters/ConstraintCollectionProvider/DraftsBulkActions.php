<?php
/**
 * Copyright (c) 2020, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

declare(strict_types=1);

namespace PcmtDraftBundle\Connector\Job\JobParameters\ConstraintCollectionProvider;

use Akeneo\Tool\Component\Batch\Job\JobInterface;
use Akeneo\Tool\Component\Batch\Job\JobParameters\ConstraintCollectionProviderInterface;
use PcmtDraftBundle\MassActions\DraftsBulkActionOperation;
use Symfony\Component\Validator\Constraints\Collection;
use Symfony\Component\Validator\Constraints\NotNull;
use Symfony\Component\Validator\Constraints\Type;

class DraftsBulkActions implements ConstraintCollectionProviderInterface
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
    public function getConstraintCollection()
    {
        return new Collection(
            [
                'fields' => [
                    DraftsBulkActionOperation::KEY_EXCLUDED              => new NotNull(),
                    DraftsBulkActionOperation::KEY_SELECTED              => new NotNull(),
                    DraftsBulkActionOperation::KEY_ALL_SELECTED          => new Type('bool'),
                    DraftsBulkActionOperation::KEY_USER_TO_NOTIFY        => new Type('string'),
                    DraftsBulkActionOperation::KEY_IS_USER_AUTHENTICATED => new Type('bool'),
                ],
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    public function supports(JobInterface $job)
    {
        return in_array($job->getName(), $this->supportedJobNames);
    }
}
