<?php
/**
 * Copyright (c) 2019, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

declare(strict_types=1);

namespace PcmtDraftBundle\Service\Draft;

use PcmtDraftBundle\Entity\AbstractDraft;
use Symfony\Component\Translation\TranslatorInterface;

class DraftStatusTranslatorService
{
    /**
     * @var TranslatorInterface
     */
    private $translator;

    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    public function getNameTranslated(int $statusId): string
    {
        return $this->translator->trans($this->getName($statusId));
    }

    public function getName(int $statusId): string
    {
        switch ($statusId) {
            case AbstractDraft::STATUS_NEW:
                return 'pcmt_core.draft.status_new';
            case AbstractDraft::STATUS_APPROVED:
                return 'pcmt_core.draft.status_approved';
            case AbstractDraft::STATUS_REJECTED:
                return 'pcmt_core.draft.status_rejected';
            default:
                throw new \Exception('No draft status name for: ' . $statusId);
        }
    }
}