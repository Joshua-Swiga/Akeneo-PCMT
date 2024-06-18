<?php
/**
 * Copyright (c) 2020, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

declare(strict_types=1);

namespace PcmtDraftBundle\Exception;

use RuntimeException;

class DraftRejectFailedException extends RuntimeException
{
    public static function createWithDefaultMessage(): self
    {
        return new self('pcmt.entity.draft.error.cannot_reject_wrong_status');
    }
}