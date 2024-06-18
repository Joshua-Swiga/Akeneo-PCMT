<?php
/**
 * Copyright (c) 2020, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

declare(strict_types=1);

namespace PcmtDraftBundle\Exception;

use RuntimeException;

class DraftSavingFailedException extends RuntimeException
{
    public static function draftAlreadyApproved(): self
    {
        return new self('pcmt.entity.draft.error.already_approved');
    }

    public static function draftAlreadyRejected(): self
    {
        return new self('pcmt.entity.draft.error.already_rejected');
    }

    public static function noCorrespondingObject(): self
    {
        return new self('pcmt.entity.draft.error.no_corresponding_object');
    }

    public static function draftHasBeenEditedInTheMeantime(): self
    {
        return new self('pcmt.entity.draft.error.draft_already_edited');
    }
}