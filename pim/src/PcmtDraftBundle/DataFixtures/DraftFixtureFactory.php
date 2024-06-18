<?php

declare(strict_types=1);

/**
 * Copyright (c) 2020, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

namespace PcmtDraftBundle\DataFixtures;

use Doctrine\Common\DataFixtures\FixtureInterface;
use PcmtDraftBundle\Entity\AbstractDraft;

class DraftFixtureFactory
{
    public function createDraft(int $status): FixtureInterface
    {
        if (!in_array($status, AbstractDraft::STATUSES)) {
            throw new \InvalidArgumentException('Unknown status, cannot create draft.');
        }

        switch ($status) {
            case AbstractDraft::STATUS_NEW:
                return new CategoryUserGroupFixture();
        }
    }
}