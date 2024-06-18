<?php

declare(strict_types=1);

/**
 * Copyright (c) 2020, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

namespace PcmtPermissionsBundle\Validator\Constraints;

class UniqueEntity extends \Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity
{
    /** @var string */
    public $service = 'PcmtPermissionsBundle\Validator\Constraints\UniqueEntityValidator';
}
