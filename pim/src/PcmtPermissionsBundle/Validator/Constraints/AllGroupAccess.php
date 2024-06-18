<?php

declare(strict_types=1);

/**
 * Copyright (c) 2020, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

namespace PcmtPermissionsBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

class AllGroupAccess extends Constraint
{
    /** @var string[] */
    public $fields = [];

    /** @var string */
    public $message = 'The field cannot be empty.';

    /** @var string */
    public $messageAll = 'You cannot have "All" field mixed with others.';

    /**
     * {@inheritdoc}
     */
    public function getTargets()
    {
        return self::CLASS_CONSTRAINT;
    }
}