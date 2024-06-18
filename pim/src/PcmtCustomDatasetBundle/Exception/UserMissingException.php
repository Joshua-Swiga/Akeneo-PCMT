<?php
/**
 * Copyright (c) 2020, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

declare(strict_types=1);

namespace PcmtCustomDatasetBundle\Exception;

use Akeneo\Tool\Component\StorageUtils\Exception\PropertyException;

class UserMissingException extends PropertyException
{
    public function __construct(string $user)
    {
        parent::__construct();
        $this->message =
            sprintf(
                'There is no user "%s" in the database.',
                $user
            );
    }
}