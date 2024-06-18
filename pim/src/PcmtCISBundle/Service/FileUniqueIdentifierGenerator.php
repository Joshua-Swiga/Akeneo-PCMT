<?php

declare(strict_types=1);

/*
 * Copyright (c) 2020, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

namespace PcmtCISBundle\Service;

use Carbon\Carbon;

class FileUniqueIdentifierGenerator
{
    public function generate(): string
    {
        return (new Carbon())->format('Ymd\THis\Z');
    }
}