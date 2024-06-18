<?php
/**
 * Copyright (c) 2019, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

declare(strict_types=1);

namespace PcmtDraftBundle\Service\Draft;

use PcmtDraftBundle\Entity\AbstractDraft;

class DraftStatusListService
{
    /** @var DraftStatusTranslatorService */
    private $statusTranslatorService;

    public function __construct(DraftStatusTranslatorService $statusTranslatorService)
    {
        $this->statusTranslatorService = $statusTranslatorService;
    }

    public function getAll(): array
    {
        return [
            AbstractDraft::STATUS_NEW,
            AbstractDraft::STATUS_APPROVED,
            AbstractDraft::STATUS_REJECTED,
        ];
    }

    public function getTranslated(): array
    {
        return array_map(
            function (int $statusId) {
                return [
                    'id'   => $statusId,
                    'name' => $this->statusTranslatorService->getNameTranslated($statusId),
                ];
            },
            $this->getAll()
        );
    }
}