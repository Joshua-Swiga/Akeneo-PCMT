<?php

/**
 * Copyright (c) 2020, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

declare(strict_types=1);

namespace PcmtDraftBundle\Connector\Job\Provider;

use PcmtDraftBundle\Entity\AbstractDraft;
use PcmtDraftBundle\Repository\DraftRepositoryInterface;

class DraftsProvider
{
    /** @var DraftRepositoryInterface */
    private $draftRepository;

    public function __construct(DraftRepositoryInterface $draftRepository)
    {
        $this->draftRepository = $draftRepository;
    }

    public function prepare(bool $allSelected, array $excluded, array $selected): array
    {
        if ($allSelected) {
            $drafts = $this->draftRepository->findWithPermissionAndStatus(AbstractDraft::STATUS_NEW);

            foreach ($drafts as $index => $draft) {
                if (in_array($draft->getId(), $excluded)) {
                    unset($drafts[$index]);
                }
            }

            return $drafts;
        }

        return $this->draftRepository->findBy(
            [
                'status' => AbstractDraft::STATUS_NEW,
                'id'     => $selected,
            ]
        );
    }
}