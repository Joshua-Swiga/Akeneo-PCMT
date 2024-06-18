<?php
/**
 * Copyright (c) 2020, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

declare(strict_types=1);

namespace PcmtDraftBundle\Connector\Job\InvalidItems;

use Akeneo\Tool\Component\Batch\Item\InvalidItemInterface;

class DraftInvalidItem implements InvalidItemInterface
{
    /** @var string[] */
    protected $invalidData = [];

    /** @var int */
    protected $draftId;

    public function __construct(int $draftId, array $invalidData)
    {
        $this->draftId = $draftId;
        $this->invalidData = $invalidData;
    }

    /**
     * {@inheritdoc}
     */
    public function getInvalidData()
    {
        return $this->invalidData;
    }

    public function getDraftId(): int
    {
        return $this->draftId;
    }
}